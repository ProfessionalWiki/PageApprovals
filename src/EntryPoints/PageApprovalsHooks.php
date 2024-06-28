<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\EntryPoints;

use DatabaseUpdater;
use OutputPage;
use ParserOutput;
use ProfessionalWiki\PageApprovals\PageApprovals;
use ProfessionalWiki\PageApprovals\Presentation\OutputPageUiPresenter;

class PageApprovalsHooks {

	public static function onOutputPageParserOutput( OutputPage $out, ParserOutput $parserOutput ): void {
		// TODO: verify called only once when embedding other pages
		// FIXME: we want the evaluation to happen only on render. This hook is also getting called when using the cache.

		if ( self::isApprovablePage( $out ) ) {
			PageApprovals::getInstance()->newEvaluateApprovalStateAction()->evaluate(
				pageId: $out->getWikiPage()->getId(),
				currentPageHtml: $parserOutput->getRawText(),
			);
		}
	}

	private static function isApprovablePage( OutputPage $out ): bool {
		return $out->isArticle()
			&& $out->getRevisionId() !== null // Exclude non-existing pages
			&& $out->isRevisionCurrent();
	}

	public static function onLoadExtensionSchemaUpdates( DatabaseUpdater $updater ): void {
		$updater->addExtensionTable(
			'approval_log',
			__DIR__ . '/../../sql/PageApprovals.sql'
		);
	}

	public static function onOutputPageBeforeHTML( OutputPage $out ): void {
		if ( !self::isApprovablePage( $out ) ) { // TODO: move to UseCase
			return;
		}

		( new OutputPageUiPresenter( $out ) )->presentUi(
			PageApprovals::getInstance()->newApprovalUiQuery()->getUiState( $out )
		);
	}

}
