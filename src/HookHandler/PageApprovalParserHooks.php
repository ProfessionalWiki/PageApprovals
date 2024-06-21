<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\HookHandler;

use DatabaseUpdater;
use OutputPage;
use ParserOutput;
use ProfessionalWiki\PageApprovals\PageApprovals;

class PageApprovalParserHooks {

	public static function onOutputPageParserOutput( OutputPage $out, ParserOutput $parserOutput ): void {
		if ( $out->isArticle() ) {
			PageApprovals::getInstance()->newEvaluateApprovalStateAction()->evaluate(
				pageId: $out->getWikiPage()->getId(),
				currentPageHtml: $parserOutput->getRawText(),
			);
		}
	}

	public static function onLoadExtensionSchemaUpdates( DatabaseUpdater $updater ): void {
		$updater->addExtensionTable(
			'approval_log',
			__DIR__ . '/../../sql/PageApprovals.sql'
		);
	}

}
