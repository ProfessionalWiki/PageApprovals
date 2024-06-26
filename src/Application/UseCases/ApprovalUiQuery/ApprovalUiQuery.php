<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Application\UseCases\ApprovalUiQuery;

use OutputPage;
use ProfessionalWiki\PageApprovals\Application\ApprovalAuthorizer;
use ProfessionalWiki\PageApprovals\Application\ApprovalLog;
use ProfessionalWiki\PageApprovals\Application\ApprovalState;

class ApprovalUiQuery {

	public function __construct(
		private readonly ApprovalLog $approvalLog,
		private readonly ApprovalAuthorizer $approvalAuthorizer
	) {
	}

	public function getUiState( OutputPage $out ): UiArguments {
		$showUi = $this->isApprovablePage( $out );
		$approvalState = $this->getApprovalState( $out, $showUi );

		return new UiArguments(
			showUi: $showUi, // TODO: test
			userIsApprover: $showUi && $this->approvalAuthorizer->canApprove( $out->getWikiPage() ),
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

}
