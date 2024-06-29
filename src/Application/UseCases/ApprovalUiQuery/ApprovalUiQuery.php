<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Application\UseCases\ApprovalUiQuery;

use Iterator;
use OutputPage;
use ProfessionalWiki\PageApprovals\Application\ApprovalLog;
use ProfessionalWiki\PageApprovals\Application\ApprovalState;
use ProfessionalWiki\PageApprovals\Application\ApproverRepository;
use Title;

class ApprovalUiQuery {

	public function __construct(
		private readonly ApprovalLog $approvalLog,
		private readonly ApproverRepository $approverRepository,
	) {
	}

	public function getUiState( OutputPage $out ): UiArguments {
		$showUi = $this->isApprovablePage( $out );
		$approvalState = $this->getApprovalState( $out, $showUi );

		return new UiArguments(
			showUi: $showUi, // TODO: test
			userIsApprover: $showUi && $this->userIsApproverForPage( $out ),
			pageIsApproved: $approvalState?->isApproved ?? false, // TODO: test
			approvalTimestamp: $approvalState?->approvalTimestamp ?? 0, // TODO: test
			approverId: $approvalState?->approverId ?? null, // TODO: test
		);
	}

	private function getApprovalState( OutputPage $out, bool $showUi ): ?ApprovalState {
		if ( $showUi ) {
			return $this->approvalLog->getApprovalState( pageId: $out->getWikiPage()->getId() ); // TODO: test
		}

		return null;
	}

	private function isApprovablePage( OutputPage $out ): bool {
		return $out->isArticle() // TODO: test
			&& $out->getRevisionId() !== null // Exclude non-existing pages // TODO: test
			&& $out->isRevisionCurrent(); // TODO: test
	}

	private function userIsApproverForPage( OutputPage $out ): bool {
		$sharedCategories = array_intersect(
			$this->approverRepository->getApproverCategories( $out->getUser()->getId() ), // TODO: test
			$this->titleArrayObjectToStringArray( $out->getWikiPage()->getCategories() )
		);

		return $sharedCategories !== []; // TODO: test
	}

	/**
	 * @param Iterator<Title> $titles
	 * @return string[]
	 */
	private function titleArrayObjectToStringArray( Iterator $titles ): array {
		return array_map(
			fn( Title $category ) => $category->getText(), // TODO: verify handling of different category names
			iterator_to_array( $titles ),
		);
	}

}
