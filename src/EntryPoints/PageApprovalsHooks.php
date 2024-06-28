<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\EntryPoints;

use DatabaseUpdater;
use OutputPage;
use ParserOutput;
use TemplateParser;
use ProfessionalWiki\PageApprovals\PageApprovals;

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
		if ( !self::isApprovablePage( $out ) ) {
			return;
		}

		$templateParser = new TemplateParser( __DIR__ . '/../../templates/' );

		// TODO: Build logic for all hardcoded Booleans
		$isUserAnApprover = true;
		$isPageApproved = false;
		$isAnApproverForPageCategory = true;

		$context = [
			'isPageApproved' => $isPageApproved,
			'canApprove' => $isAnApproverForPageCategory && $isUserAnApprover,
			'approveButtonText' => $out->msg( 'pageapprovals-approve-button' )->text(),
			'unapproveButtonText' => $out->msg( 'pageapprovals-unapprove-button' )->text(),
			'approvalStatusMessage' => $out->msg(
				$isPageApproved ? "pageapprovals-status-approved" : "pageapprovals-status-not-approved"
			)->text()
		];

		$html = $templateParser->processTemplate( 'PageApprovalStatus', $context );
		$out->addHTML( $html );
		$out->addModules( 'ext.pageApprovals.resources' );
	}

}
