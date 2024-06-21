<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\EntryPoints;

use DatabaseUpdater;
use OutputPage;
use ParserOutput;
use ProfessionalWiki\PageApprovals\PageApprovals;

class PageApprovalsHooks {

	public static function onOutputPageParserOutput( OutputPage $out, ParserOutput $parserOutput ): void {
		PageApprovals::getInstance()->newEvaluateApprovalStateAction()->evaluate(
			pageId: $out->getWikiPage()->getId(),
			currentPageHtml: $parserOutput->getRawText(),
		);
	}

	public static function onLoadExtensionSchemaUpdates( DatabaseUpdater $updater ): void {
		$updater->addExtensionTable(
			'approval_log',
			__DIR__ . '/../../sql/PageApprovals.sql'
		);
	}

}
