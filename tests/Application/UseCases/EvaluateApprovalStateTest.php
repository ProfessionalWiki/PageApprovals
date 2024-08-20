<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Tests\Application\UseCases;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\PageApprovals\Adapters\InMemoryApprovalLog;
use ProfessionalWiki\PageApprovals\Adapters\InMemoryHtmlRepository;
use ProfessionalWiki\PageApprovals\Application\ApprovalLog;
use ProfessionalWiki\PageApprovals\Application\UseCases\EvaluateApprovalState;

/**
 * @covers \ProfessionalWiki\PageApprovals\Application\UseCases\EvaluateApprovalState
 */
class EvaluateApprovalStateTest extends TestCase {

	private const PAGE_ID = 42;

	public function testPageGetsUnapprovedUponNoPriorHtml(): void {
		$approvalLog = $this->newApprovalLogWithApprovedPage();

		$action = new EvaluateApprovalState(
			htmlRepository: new InMemoryHtmlRepository(),
			approvalLog: $approvalLog
		);

		$action->evaluate( self::PAGE_ID, 'whatever' );

		$this->assertFalse( $approvalLog->getApprovalState( self::PAGE_ID )->isApproved );
	}

	private function newApprovalLogWithApprovedPage(): ApprovalLog {
		$approvalLog = new InMemoryApprovalLog();
		$approvalLog->approvePage( self::PAGE_ID, 1 );
		return $approvalLog;
	}

	public function testApprovedPageGetsUnapprovedUponMismatchingHtml(): void {
		$approvalLog = $this->newApprovalLogWithApprovedPage();

		$htmlRepo = new InMemoryHtmlRepository();
		$htmlRepo->saveApprovedHtml( self::PAGE_ID, 'one' );

		$action = new EvaluateApprovalState(
			htmlRepository: $htmlRepo,
			approvalLog: $approvalLog
		);

		$action->evaluate( self::PAGE_ID, 'two' );

		$this->assertFalse( $approvalLog->getApprovalState( self::PAGE_ID )->isApproved );
	}

	public function testPageRemainsApprovedOnMatchingHtml(): void {
		$approvalLog = $this->newApprovalLogWithApprovedPage();

		$htmlRepo = new InMemoryHtmlRepository();
		$htmlRepo->saveApprovedHtml( self::PAGE_ID, 'same' );

		$action = new EvaluateApprovalState(
			htmlRepository: $htmlRepo,
			approvalLog: $approvalLog
		);

		$action->evaluate( self::PAGE_ID, 'same' );

		$this->assertTrue( $approvalLog->getApprovalState( self::PAGE_ID )->isApproved );
	}

	public function testUnapprovedPageRemainsUnapproved(): void {
		$approvalLog = new InMemoryApprovalLog();

		$action = new EvaluateApprovalState(
			htmlRepository: new InMemoryHtmlRepository(),
			approvalLog: $approvalLog
		);

		$action->evaluate( self::PAGE_ID, 'different' );

		// By asserting null, we verify that no additional "unapproved" record ended up in the log.
		$this->assertNull( $approvalLog->getApprovalState( self::PAGE_ID ) );
	}

	public function testSmwIdsAreIgnored(): void {
		$approvalLog = $this->newApprovalLogWithApprovedPage();

		$htmlRepo = new InMemoryHtmlRepository();
		$htmlRepo->saveApprovedHtml( self::PAGE_ID, '<div id="smw-123">content</div><div id="not-smw">content</div>' );

		$action = new EvaluateApprovalState(
			htmlRepository: $htmlRepo,
			approvalLog: $approvalLog
		);

		$action->evaluate( self::PAGE_ID, '<div id="smw-456">content</div><div id="not-smw">content</div>' );

		$this->assertTrue( $approvalLog->getApprovalState( self::PAGE_ID )->isApproved );
	}

}
