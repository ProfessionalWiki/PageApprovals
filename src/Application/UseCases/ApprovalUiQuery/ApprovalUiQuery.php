<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Application\UseCases\ApprovalUiQuery;

use MediaWiki\Output\OutputPage;
use ProfessionalWiki\PageApprovals\Application\ApprovalAuthorizer;
use ProfessionalWiki\PageApprovals\Application\ApprovalLog;
use ProfessionalWiki\PageApprovals\Application\ApprovalState;
use ProfessionalWiki\PageApprovals\Application\ApproverRepository;

class ApprovalUiQuery {

	public function __construct(
		private readonly ApprovalLog $approvalLog,
		private readonly ApprovalAuthorizer $approvalAuthorizer,
		private readonly ApproverRepository $approverRepository
	) {
	}

	public function getUiState( OutputPage $out ): UiArguments {
		$showUi = $this->isApprovablePage( $out );
		$approvalState = $this->getApprovalState( $out, $showUi );

		return new UiArguments(
			showUi: $showUi,
			userIsApprover: $showUi && $this->approvalAuthorizer->canApprove( $out->getWikiPage() ),
			pageIsApproved: $approvalState?->isApproved ?? false,
			approvalTimestamp: $approvalState?->approvalTimestamp ?? 0,
			approverId: $approvalState?->approverId ?? null,
			approverUserName: $approvalState?->approverUserName
		);
	}

	private function getApprovalState( OutputPage $out, bool $showUi ): ?ApprovalState {
		if ( $showUi ) {
			return $this->approvalLog->getApprovalState( pageId: $out->getWikiPage()->getId() );
		}
		return null;
	}

	private function isApprovablePage( OutputPage $out ): bool {
		return $this->pageHasApprovers( $out )
			&& $out->isArticle()
			&& $out->getRevisionId() !== null // Exclude non-existing pages
			&& $out->getRevisionId() === $out->getWikiPage()->getLatest();
	}

	private function pageHasApprovers( OutputPage $out ): bool {
		return array_intersect(
				$out->getCategories(),
				$this->approverRepository->getAllCategories()
			) !== [];
	}
}
