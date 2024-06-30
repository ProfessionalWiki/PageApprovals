<?php

namespace ProfessionalWiki\PageApprovals\Tests\EntryPoints\REST;

use MediaWiki\Rest\RequestData;
use MediaWiki\Tests\Rest\Handler\HandlerTestTrait;
use MediaWiki\Tests\Unit\Permissions\MockAuthorityTrait;
use ProfessionalWiki\PageApprovals\Adapters\InMemoryApprovalLog;
use ProfessionalWiki\PageApprovals\Adapters\InMemoryHtmlRepository;
use ProfessionalWiki\PageApprovals\Adapters\PageHtmlRetriever;
use ProfessionalWiki\PageApprovals\Application\HtmlRepository;
use ProfessionalWiki\PageApprovals\EntryPoints\REST\ApprovePageApi;
use ProfessionalWiki\PageApprovals\Tests\PageApprovalsIntegrationTest;
use ProfessionalWiki\PageApprovals\Tests\TestDoubles\FailingApprovalAuthorizer;
use ProfessionalWiki\PageApprovals\Tests\TestDoubles\SucceedingApprovalAuthorizer;

/**
 * @covers \ProfessionalWiki\PageApprovals\EntryPoints\REST\ApprovePageApi
 * @group Database
 */
class ApprovePageApiTest extends PageApprovalsIntegrationTest {
	use HandlerTestTrait;
	use MockAuthorityTrait;

	private InMemoryApprovalLog $approvalLog;
	private HtmlRepository $htmlRepository;

	protected function setUp(): void {
		parent::setUp();

		$this->approvalLog = new InMemoryApprovalLog();
		$this->htmlRepository = new InMemoryHtmlRepository();
	}

	public function testApprovePageHappyPath(): void {
		$response = $this->executeHandler(
			$this->newApprovePageApi(),
			$this->createValidRequestData( $this->createPageWithCategories()->getRevisionRecord()->getId() ),
		);

		$this->assertSame( 204, $response->getStatusCode() );
	}

	private function newApprovePageApi(): ApprovePageApi {
		return new ApprovePageApi(
			new SucceedingApprovalAuthorizer(),
			$this->approvalLog,
			$this->htmlRepository,
			$this->newPageHtmlRetriever(),
			$this->getServiceContainer()->getWikiPageFactory(),
			$this->getServiceContainer()->getRevisionLookup()
		);
	}

	private function newPageHtmlRetriever(): PageHtmlRetriever {
		return new PageHtmlRetriever( $this->getServiceContainer()->getWikiPageFactory() );
	}

	private function createValidRequestData( int $revisionId ): RequestData {
		return new RequestData( [
			'method' => 'POST',
			'pathParams' => [
				'revisionId' => $revisionId
			],
			'headers' => [
				'Content-Type' => 'application/json'
			]
		] );
	}

	public function testApprovalFailsWithoutPermission(): void {
		$response = $this->executeHandler(
			$this->newApprovePageApiWithFailingAuthorizer(),
			$this->createValidRequestData( $this->createPageWithCategories()->getRevisionRecord()->getId() )
		);

		$this->assertSame( 403, $response->getStatusCode() );
	}

	private function newApprovePageApiWithFailingAuthorizer(): ApprovePageApi {
		return new ApprovePageApi(
			new FailingApprovalAuthorizer(),
			$this->approvalLog,
			$this->htmlRepository,
			$this->newPageHtmlRetriever(),
			$this->getServiceContainer()->getWikiPageFactory(),
			$this->getServiceContainer()->getRevisionLookup()
		);
	}

	public function testApprovalFailsForMissingPageId(): void {
		$response = $this->executeHandler(
			$this->newApprovePageApi(),
			$this->createValidRequestData( 404404404 )
		);

		$this->assertSame( 404, $response->getStatusCode() );
	}

	public function testPageIsApproved(): void {
		$page = $this->createPageWithCategories();

		$this->executeHandler(
			$this->newApprovePageApi(),
			$this->createValidRequestData( $page->getRevisionRecord()->getId() ),
		);

		$this->assertTrue(
			$this->approvalLog->getApprovalState( $page->getId() )->isApproved
		);
	}

	public function testAPIUserIsInApprovalState(): void {
		$page = $this->createPageWithCategories();
		$user = $this->mockRegisteredUltimateAuthority();

		$this->executeHandler(
			$this->newApprovePageApi(),
			$this->createValidRequestData( $page->getRevisionRecord()->getId() ),
			authority: $user
		);

		$this->assertSame(
			$user->getUser()->getId(),
			$this->approvalLog->getApprovalState( $page->getId() )->approverId
		);
	}

	public function testApprovedPageHtmlIsSaved(): void {
		$page = $this->createPageWithText( 'Page text to be saved' );

		$this->executeHandler(
			$this->newApprovePageApi(),
			$this->createValidRequestData( $page->getRevisionRecord()->getId() ),
		);

		$this->assertSame( <<<EOT
<p>Page text to be saved
</p>
EOT
			, $this->htmlRepository->getApprovedHtml( $page->getId() )
		);
	}

	public function testApprovalFailsWithOutdatedRevisionId(): void {
		$page = $this->createPageWithCategories();
		$oldRevisionId = $page->getRevisionRecord()->getId();

		$this->editPage( $page, 'New revision' );

		$response = $this->executeHandler(
			$this->newApprovePageApi(),
			$this->createValidRequestData( $oldRevisionId ),
		);

		$this->assertSame( 409, $response->getStatusCode() );
	}

}
