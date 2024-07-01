<?php

namespace ProfessionalWiki\PageApprovals\Tests\EntryPoints\REST;

use MediaWiki\Rest\RequestData;
use MediaWiki\Tests\Rest\Handler\HandlerTestTrait;
use MediaWiki\Tests\Unit\Permissions\MockAuthorityTrait;
use ProfessionalWiki\PageApprovals\Adapters\InMemoryApprovalLog;
use ProfessionalWiki\PageApprovals\Application\ApprovalLog;
use ProfessionalWiki\PageApprovals\EntryPoints\REST\UnapprovePageApi;
use ProfessionalWiki\PageApprovals\Tests\PageApprovalsIntegrationTest;
use ProfessionalWiki\PageApprovals\Tests\TestDoubles\FailingApprovalAuthorizer;
use ProfessionalWiki\PageApprovals\Tests\TestDoubles\SucceedingApprovalAuthorizer;

/**
 * @covers \ProfessionalWiki\PageApprovals\EntryPoints\REST\UnapprovePageApi
 * @group Database
 */
class UnapprovePageApiTest extends PageApprovalsIntegrationTest {
	use HandlerTestTrait;
	use MockAuthorityTrait;

	private InMemoryApprovalLog $approvalLog;

	protected function setUp(): void {
		parent::setUp();

		$this->approvalLog = new InMemoryApprovalLog();
	}

	public function testUnapprovePageHappyPath(): void {
		$user = $this->getTestUser();
		$page = $this->createPageWithCategories();

		$this->approvalLog->approvePage( $page->getId(), 1 );

		$response = $this->executeHandler(
			$this->newUnapprovePageApi(),
			$this->createValidRequestData( $page->getRevisionRecord()->getId() ),
			authority: $user->getAuthority()
		);

		$state = $this->approvalLog->getApprovalState( $page->getId() );

		$this->assertSame( 200, $response->getStatusCode() );
		$this->assertSame(
			[
				'approvalTimestamp' => $state->approvalTimestamp,
				'approver' => $user->getUser()->getName(),
			],
			json_decode( $response->getBody()->getContents(), true )
		);
	}

	private function newUnapprovePageApi(): UnapprovePageApi {
		return new UnapprovePageApi(
			new SucceedingApprovalAuthorizer(),
			$this->approvalLog,
			$this->getServiceContainer()->getWikiPageFactory(),
			$this->getServiceContainer()->getRevisionLookup(),
			$this->getServiceContainer()->getUserIdentityLookup()
		);
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
			$this->newUnapprovePageApiWithFailingAuthorizer(),
			$this->createValidRequestData( $this->createPageWithCategories()->getRevisionRecord()->getId() ),
		);

		$this->assertSame( 403, $response->getStatusCode() );
	}

	private function newUnapprovePageApiWithFailingAuthorizer( ?ApprovalLog $approvalLog = null ): UnapprovePageApi {
		return new UnapprovePageApi(
			new FailingApprovalAuthorizer(),
			$approvalLog ?? new InMemoryApprovalLog(),
			$this->getServiceContainer()->getWikiPageFactory(),
			$this->getServiceContainer()->getRevisionLookup(),
			$this->getServiceContainer()->getUserIdentityLookup()
		);
	}

	public function testUnapprovalFailsForMissingPageId(): void {
		$response = $this->executeHandler(
			$this->newUnapprovePageApi(),
			$this->createValidRequestData( 404404404 )
		);

		$this->assertSame( 404, $response->getStatusCode() );
	}

	public function testPageIsUnapproved(): void {
		$page = $this->createPageWithCategories();

		$this->approvalLog->approvePage( $page->getId(), 1 );

		$this->executeHandler(
			$this->newUnapprovePageApi(),
			$this->createValidRequestData( $page->getRevisionRecord()->getId() ),
		);

		$this->assertFalse(
			$this->approvalLog->getApprovalState( $page->getId() )->isApproved
		);
	}

	public function testAPIUserIsInApprovalState(): void {
		$page = $this->createPageWithCategories();
		$user = $this->mockRegisteredUltimateAuthority();

		$this->approvalLog->approvePage( $page->getId(), 1 );

		$this->executeHandler(
			$this->newUnapprovePageApi(),
			$this->createValidRequestData( $page->getRevisionRecord()->getId() ),
			authority: $user
		);

		$this->assertSame(
			$user->getUser()->getId(),
			$this->approvalLog->getApprovalState( $page->getId() )->approverId
		);
	}

	public function testUnapprovalFailsWithOutdatedRevisionId(): void {
		$page = $this->createPageWithCategories();
		$oldRevisionId = $page->getRevisionRecord()->getId();

		$this->editPage( $page, 'New revision' );

		$response = $this->executeHandler(
			$this->newUnapprovePageApi(),
			$this->createValidRequestData( $oldRevisionId ),
		);

		$this->assertSame( 409, $response->getStatusCode() );
	}

}
