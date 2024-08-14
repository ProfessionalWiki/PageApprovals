<?php

namespace ProfessionalWiki\PageApprovals\EntryPoints\Specials;

use Html;
use MediaWiki\Linker\LinkRenderer;
use ProfessionalWiki\PageApprovals\Application\ApproverRepository;
use ProfessionalWiki\PageApprovals\Application\PendingApproval;
use ProfessionalWiki\PageApprovals\Application\PendingApprovalRetriever;
use SpecialPage;
use TitleValue;

class SpecialPendingApprovals extends SpecialPage {

	public function __construct(
		private readonly ApproverRepository $approverRepository,
		private readonly PendingApprovalRetriever $pendingApprovalRetriever,
		private readonly LinkRenderer $linkRenderer
	) {
		parent::__construct( 'PendingApprovals' );
	}

	public function execute( $subPage ): void {
		$this->setHeaders();
		$this->checkPermissions();
		$this->checkReadOnly();

		$categories = $this->approverRepository->getApproverCategories( $this->getUser()->getId() );

		if ( $categories === [] ) {
			$this->getOutput()->addWikiMsg( 'pageapprovals-no-categories' );
			return;
		}

		$pendingApprovals = $this->pendingApprovalRetriever->getPendingApprovalsForApprover( $this->getUser()->getId() );

		if ( $pendingApprovals === [] ) {
			$this->getOutput()->addWikiMsg( 'pageapprovals-no-pending-approvals' );
			return;
		}

		$this->getOutput()->addHTML( $this->createPendingApprovalsTable( $pendingApprovals ) );
	}

	/**
	 * @param array<PendingApproval> $pendingApprovals
	 */
	private function createPendingApprovalsTable( array $pendingApprovals ): string {
		return Html::rawElement(
			'table',
			[ 'class' => 'wikitable sortable' ],
			$this->createHeaderRow() . $this->createPendingApprovalRows( $pendingApprovals )
		);
	}

	private function createHeaderRow(): string {
		return <<<HTML
<tr>
	<th>{$this->msg( 'pageapprovals-pending-approvals-page' )->escaped()}</th>
	<th>{$this->msg( 'pageapprovals-pending-approvals-categories' )->escaped()}</th>
	<th class="headerSort headerSortDown">
		{$this->msg('pageapprovals-pending-approvals-last-edit-time')->escaped()}
	</th>
	<th>{$this->msg( 'pageapprovals-pending-approvals-last-edit-by' )->escaped()}</th>
</tr>
HTML;
	}

	/**
	 * @param array<PendingApproval> $pendingApprovals
	 */
	private function createPendingApprovalRows( array $pendingApprovals ): string {
		return implode(
			"\n",
			array_map(
				fn( PendingApproval $pendingApproval ) => $this->createPendingApprovalRow( $pendingApproval ),
				$pendingApprovals
			)
		);
	}

	private function createPendingApprovalRow( PendingApproval $pendingApproval ): string {
		$cells = implode(
			"\n",
			[
				Html::rawElement( 'td', [], $this->linkRenderer->makeLink( $pendingApproval->title ) ),
				Html::element( 'td', [], implode( ', ', $this->getCategoryTitlesFromDbKeys( $pendingApproval->categories ) ) ),
				Html::element(
					'td',
					[ 'data-sort-value' => $pendingApproval->lastEditTimestamp ],
					$this->getLanguage()->userTimeAndDate( $pendingApproval->lastEditTimestamp, $this->getUser() )
				),
				Html::element( 'td', [], $pendingApproval->lastEditUserName )
			]
		);

		return "<tr>$cells</tr>";
	}

	/**
	 * @param string[] $categoryDbKeys
	 * @return string[]
	 */
	private function getCategoryTitlesFromDbKeys( array $categoryDbKeys ): array {
		return array_filter( array_map(
			fn( string $dbKey ) => TitleValue::tryNew( NS_CATEGORY, $dbKey )?->getText(),
			$categoryDbKeys
		) );
	}

}
