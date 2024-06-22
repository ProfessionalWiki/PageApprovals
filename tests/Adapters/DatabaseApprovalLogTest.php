<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Tests\Adapters;

use MediaWikiIntegrationTestCase;
use ProfessionalWiki\PageApprovals\Adapters\DatabaseApprovalLog;
use ProfessionalWiki\PageApprovals\Application\ApprovalLog;
use ProfessionalWiki\PageApprovals\Application\ApprovalState;
use Wikimedia\Timestamp\ConvertibleTimestamp;

/**
 * @covers \ProfessionalWiki\PageApprovals\Adapters\DatabaseApprovalLog
 * @group Database
 */
class DatabaseApprovalLogTest extends MediaWikiIntegrationTestCase {

	protected function setUp(): void {
		parent::setUp();
		$this->tablesUsed[] = 'approval_log';
	}

	private function newApprovalLog(): ApprovalLog {
		return new DatabaseApprovalLog( $this->db );
	}

	public function testGetApprovalStateReturnsNullForNonexistentPage(): void {
		$log = $this->newApprovalLog();

		$this->assertNull( $log->getApprovalState( 404 ) );
	}

	public function testApprovePage(): void {
		$log = $this->newApprovalLog();
		$pageId = 1;
		$userId = 42;

		$log->approvePage( $pageId, $userId );

		$state = $log->getApprovalState( $pageId );
		$this->assertInstanceOf( ApprovalState::class, $state );
		$this->assertTrue( $state->isApproved );
		$this->assertSame( $pageId, $state->pageId );
		$this->assertSame( $userId, $state->approverId );
		$this->assertEqualsWithDelta( time(), $state->approvalTimestamp, 2 );
	}

	public function testUnapprovePage(): void {
		$log = $this->newApprovalLog();
		$pageId = 2;
		$userId = 24;

		$log->approvePage( $pageId, $userId );
		$log->unapprovePage( $pageId, $userId );

		$state = $log->getApprovalState( $pageId );
		$this->assertInstanceOf( ApprovalState::class, $state );
		$this->assertFalse( $state->isApproved );
		$this->assertSame( $pageId, $state->pageId );
		$this->assertSame( $userId, $state->approverId );
		$this->assertEqualsWithDelta( time(), $state->approvalTimestamp, 2 );
	}

	public function testUnapprovePageWithNullUserId(): void {
		$log = $this->newApprovalLog();
		$pageId = 3;

		$log->approvePage( $pageId, 42 );
		$log->unapprovePage( $pageId, null );

		$state = $log->getApprovalState( $pageId );
		$this->assertInstanceOf( ApprovalState::class, $state );
		$this->assertFalse( $state->isApproved );
		$this->assertSame( $pageId, $state->pageId );
		$this->assertNull( $state->approverId );
	}

	public function testGetApprovalStateReturnsLatestState(): void {
		$log = $this->newApprovalLog();
		$pageId = 4;

		ConvertibleTimestamp::setFakeTime( '20230101000000' );
		$log->approvePage( $pageId, 1 );

		ConvertibleTimestamp::setFakeTime( '20230102000000' );
		$log->unapprovePage( $pageId, 2 );

		ConvertibleTimestamp::setFakeTime( '20230103000000' );
		$log->approvePage( $pageId, 3 );

		ConvertibleTimestamp::setFakeTime( '20230104000000' );
		$log->approvePage( $pageId + 1, 4 );

		$state = $log->getApprovalState( $pageId );
		$this->assertInstanceOf( ApprovalState::class, $state );
		$this->assertTrue( $state->isApproved );
		$this->assertSame( $pageId, $state->pageId );
		$this->assertSame( 3, $state->approverId );
		$this->assertSame( strtotime( '20230103000000' ), $state->approvalTimestamp );
	}

}
