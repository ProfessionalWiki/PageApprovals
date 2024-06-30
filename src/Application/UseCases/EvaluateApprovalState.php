<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Application\UseCases;

use ProfessionalWiki\PageApprovals\Application\ApprovalLog;
use ProfessionalWiki\PageApprovals\Application\HtmlRepository;

class EvaluateApprovalState {

	public function __construct(
		private readonly HtmlRepository $htmlRepository,
		private readonly ApprovalLog $approvalLog
	) {
	}

	public function evaluate( int $pageId, string $currentPageHtml ): void {
		if ( $currentPageHtml !== $this->getApprovedHtmlForPage( $pageId )
			&& $this->approvalLog->getApprovalState( $pageId )?->isApproved === true
		) {
			$this->unapprovePage( $pageId );
		}
	}

	private function getApprovedHtmlForPage( int $pageId ): ?string {
		return $this->htmlRepository->getApprovedHtml( $pageId );
	}

	private function unapprovePage( int $pageId ): void {
		$this->approvalLog->unapprovePage( pageId: $pageId, userId: null );
	}

}
