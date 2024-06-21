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

	public function testPageIsUnapprovedUponMismatchingHtml(): void {
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

}
