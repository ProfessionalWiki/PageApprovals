<?php

namespace ProfessionalWiki\PageApprovals\EntryPoints\Specials;

use Html;
use MediaWiki\Linker\LinkRenderer;
use ProfessionalWiki\PageApprovals\Application\PendingApproval;
use ProfessionalWiki\PageApprovals\Application\PendingApprovalRetriever;
use SpecialPage;

class SpecialPendingApprovals extends SpecialPage {

	public function __construct(
		private readonly PendingApprovalRetriever $pendingApprovalRetriever,
		private readonly LinkRenderer $linkRenderer
	) {
		parent::__construct( 'PendingApprovals' );
	}

	public function execute( $subPage ): void {
		$this->setHeaders();
		$this->checkPermissions();
		$this->checkReadOnly();

		$this->getOutput()->addHTML(
			$this->createPendingApprovalsTable(
				$this->pendingApprovalRetriever->getPendingApprovalsForApprover( $this->getUser()->getId() )
			)
		);
	}

	/**
	 * @param array<PendingApproval> $pendingApprovals
	 */
	private function createPendingApprovalsTable( array $pendingApprovals ): string {
		return Html::rawElement(
			'table',
			[ 'class' => 'wikitable' ],
			$this->createHeaderRow() . implode( "\n", $this->createPendingApprovalRows( $pendingApprovals ) )
		);
	}

	private function createHeaderRow(): string {
		return $this->createTableRow(
			[
				$this->msg( 'pageapprovals-pending-approvals-page' )->plain(),
				$this->msg( 'pageapprovals-pending-approvals-categories' )->plain(),
				$this->msg( 'pageapprovals-pending-approvals-last-edit-time' )->plain(),
				$this->msg( 'pageapprovals-pending-approvals-last-edit-by' )->plain(),
			],
			'th'
		);
	}

	/**
	 * @param string[] $cells
	 */
	private function createTableRow( array $cells, string $cellType = 'td' ): string {
		$rowContent = '';
		foreach ( $cells as $cell ) {
			$rowContent .= Html::rawElement( $cellType, [], $cell );
		}
		return Html::rawElement( 'tr', [], $rowContent );
	}

	/**
	 * @param array<PendingApproval> $pendingApprovals
	 * @return string[]
	 */
	private function createPendingApprovalRows( array $pendingApprovals ): array {
		$rows = [];
		foreach ( $pendingApprovals as $pendingApproval ) {
			$rows[] = $this->createPendingApprovalRow( $pendingApproval );
		}
		return $rows;
	}

	private function createPendingApprovalRow( PendingApproval $pendingApproval ): string {
		return $this->createTableRow( [
			$this->linkRenderer->makeLink( $pendingApproval->title ),
			implode( ', ', $pendingApproval->categories ),
			$this->getLanguage()->userTimeAndDate( $pendingApproval->lastEditTimestamp, $this->getUser() ),
			$pendingApproval->lastEditUserName
		] );
	}

}
