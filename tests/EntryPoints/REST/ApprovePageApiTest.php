<?php

namespace ProfessionalWiki\PageApprovals\Tests\EntryPoints\REST;

use MediaWiki\MediaWikiServices;
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
			$this->createValidRequestData( $this->getIdOfExistingPage( 'Test 1' ) )
		);

		$this->assertSame( 204, $response->getStatusCode() );
	}

	private function newApprovePageApi(): ApprovePageApi {
		return new ApprovePageApi(
			new SucceedingApprovalAuthorizer(),
			$this->approvalLog,
			$this->htmlRepository,
			$this->newPageHtmlRetriever()
		);
	}

	private function newPageHtmlRetriever(): PageHtmlRetriever {
		return new PageHtmlRetriever( MediaWikiServices::getInstance()->getWikiPageFactory() );
	}

	private function createValidRequestData( int $pageId ): RequestData {
		return new RequestData( [
			'method' => 'POST',
			'pathParams' => [
				'pageId' => $pageId
			],
			'headers' => [
				'Content-Type' => 'application/json'
			]
		] );
	}

	public function testApprovalFailsWithoutPermission(): void {
		$response = $this->executeHandler(
			$this->newApprovePageApiWithFailingAuthorizer(),
			$this->createValidRequestData( $this->getIdOfExistingPage( 'Test 2' ) )
		);

		$this->assertSame( 403, $response->getStatusCode() );
	}

	private function newApprovePageApiWithFailingAuthorizer(): ApprovePageApi {
		return new ApprovePageApi(
			new FailingApprovalAuthorizer(),
			$this->approvalLog,
			$this->htmlRepository,
			$this->newPageHtmlRetriever()
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
		$pageId = $this->getIdOfExistingPage( 'Page to be approved' );

		$this->executeHandler(
			$this->newApprovePageApi(),
			$this->createValidRequestData( $pageId )
		);

		$this->assertTrue(
			$this->approvalLog->getApprovalState( $pageId )->isApproved
		);
	}

	public function testAPIUserIsInApprovalState(): void {
		$pageId = $this->getIdOfExistingPage( 'API User' );
		$user = $this->mockRegisteredUltimateAuthority();

		$this->executeHandler(
			$this->newApprovePageApi(),
			$this->createValidRequestData( $pageId ),
			authority: $user
		);

		$this->assertSame(
			$user->getUser()->getId(),
			$this->approvalLog->getApprovalState( $pageId )->approverId
		);
	}

	public function testApprovedPageHtmlIsSaved(): void {
		$pageId = $this->getIdOfExistingPage( 'Page to be saved', 'Page text to be saved' );

		$this->executeHandler(
			$this->newApprovePageApi(),
			$this->createValidRequestData( $pageId )
		);

		$this->assertSame( <<<EOT
<p>Page text to be saved
</p>
EOT
			, $this->htmlRepository->getApprovedHtml( $pageId )
		);
	}

}
