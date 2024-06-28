<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\EntryPoints;

use DatabaseUpdater;
use OutputPage;
use ParserOutput;
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

		// TODO: Build logic for all hardcoded Booleans
		$isUserAnApprover = true;
		$isPageApproved = false;
		$isAnApproverForPageCategory = true;

		self::showApprovalStatus( $out, $isPageApproved );

		// @phpstan-ignore-next-line
		$canApprove = $isUserAnApprover && $isAnApproverForPageCategory;

		if ( $canApprove ) {
			self::showApprovalButton( $out, $isPageApproved );
		}

		$out->addModules( 'ext.pageApprovals.resources' );
	}

	private static function showApprovalStatus( OutputPage $out, bool $isPageApproved ): void {
		$messageKey = $isPageApproved ? "pageapprovals-status-approved" : "pageapprovals-status-not-approved";
		$message = $out->msg( $messageKey )->text();
		$out->addHTML( "<div class='page-approval-status'>{$message}</div>" );
	}

	private static function showApprovalButton( OutputPage $out, bool $isPageApproved ): void {
		if ( $isPageApproved ) {
			$unapproveButtonText = $out->msg( 'pageapprovals-unapprove-button' )->text();
			$unapproveButtonHtml = "<button id='unapproveButton'>{$unapproveButtonText}</button>";
			$out->addHTML( $unapproveButtonHtml );
		} else {
			$approveButtonText = $out->msg( 'pageapprovals-approve-button' )->text();
			$approveButtonHtml = "<button id='approveButton'>{$approveButtonText}</button>";
			$out->addHTML( $approveButtonHtml );
		}
	}

}
