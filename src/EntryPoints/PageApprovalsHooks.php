<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\EntryPoints;

use DatabaseUpdater;
use OutputPage;
use ParserOutput;
use ProfessionalWiki\PageApprovals\PageApprovals;

class PageApprovalsHooks {

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

	public static function onOutputPageBeforeHTML( OutputPage $out ): void {
		// TODO: Build logic for all hardcoded Booleans
		$isUserAnApprover = true;
		$isPageApproved = false;
		// @phpstan-ignore-next-line
		$isPageApprovable = $out->getTitle()->isContentPage() && !$out->getTitle()->isSpecialPage();
		$isAnApproverForPageCategory = true;

		if ( $isPageApprovable ) {
			self::showApprovalStatus( $out, $isPageApproved );
		}

		// @phpstan-ignore-next-line
		$canApprove = $isUserAnApprover && $isPageApprovable && $isAnApproverForPageCategory;

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
