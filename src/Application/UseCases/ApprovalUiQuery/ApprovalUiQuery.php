<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Application\UseCases\ApprovalUiQuery;

use Iterator;
use OutputPage;
use ProfessionalWiki\PageApprovals\Application\ApprovalLog;
use ProfessionalWiki\PageApprovals\Application\ApproverRepository;
use Title;

class ApprovalUiQuery {

	public function __construct(
		private readonly ApprovalLog $approvalLog,
		private readonly ApproverRepository $approverRepository,
	) {
	}

	public function getUiState( OutputPage $out ): UiArguments {
		$approvalState = $this->approvalLog->getApprovalState( pageId: $out->getWikiPage()->getId() );

		return new UiArguments(
			showUi: true,
			userIsApprover: $this->userIsApproverForPage( $out ),
			pageIsApproved: $approvalState?->isApproved ?? false,
			approvalTimestamp: $approvalState?->approvalTimestamp ?? 0,
			approverId: $approvalState?->approverId ?? null,
		);
	}

	private function userIsApproverForPage( OutputPage $out ): bool {
		$sharedCategories = array_intersect(
			$this->approverRepository->getApproverCategories( $out->getUser()->getId() ),
			$this->titleArrayObjectToStringArray( $out->getWikiPage()->getCategories() )
		);

		return $sharedCategories !== [];
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
