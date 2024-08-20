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
		if ( !$this->htmlIsTheSame( $currentPageHtml, $this->getApprovedHtmlForPage( $pageId ) ?? '' )
			&& $this->approvalLog->getApprovalState( $pageId )?->isApproved === true
		) {
			$this->unapprovePage( $pageId );
		}
	}

	private function htmlIsTheSame( string $html1, string $html2 ): bool {
		return $this->normalizeHtml( $html1 ) === $this->normalizeHtml( $html2 );
	}

	private function normalizeHtml( string $html ): string {
		return preg_replace( '/id="smw-[^"]+"/', '', $html ) ?? '';
	}

	private function getApprovedHtmlForPage( int $pageId ): ?string {
		return $this->htmlRepository->getApprovedHtml( $pageId );
	}

	private function unapprovePage( int $pageId ): void {
		$this->approvalLog->unapprovePage( pageId: $pageId, userId: null );
	}

}
