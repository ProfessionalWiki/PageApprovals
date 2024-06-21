<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\EntryPoints;

use MediaWiki\MediaWikiServices;
use OutputPage;
use ParserOutput;
use ProfessionalWiki\PageApprovals\PageApprovals;
use Title;
use Wikimedia\Rdbms\DBError;

class PageApprovalsHooks {

	public static function onOutputPageParserOutput( OutputPage $out, ParserOutput $parserOutput ): void {
		PageApprovals::getInstance()->newEvaluateApprovalStateAction()->evaluate(
			pageId: $out->getWikiPage()->getId(),
			currentPageHtml: $parserOutput->getRawText(),
		);
	}

	public static function onOutputPageBeforeHTML( OutputPage $out ): void {
		$user = $out->getUser();
		$title = $out->getTitle();

		if ( !$title instanceof Title || !$user->isRegistered() ) {
			return;
		}

		$permissionManager = MediaWikiServices::getInstance()->getPermissionManager();
		$revisionStore = MediaWikiServices::getInstance()->getRevisionStore();

		try {
			$firstRevision = $revisionStore->getFirstRevision( $title );
			$firstUserId = $firstRevision ? $firstRevision->getUser( 0 ) : null;
		} catch ( DBError $e ) {
			wfLogWarning( "Database error fetching first revision: " . $e->getMessage() );
			return;
		}

		$canApprove = $title->isContentPage() && !$title->isSpecialPage() && $permissionManager->userCan(
				'edit',
				$user,
				$title
			) && $user->getId() !== $firstUserId; // TODO: Placeholder for actual Logic

		$isApproved = false; // TODO: build Logic

		// @phpstan-ignore-next-line
		$messageKey = $isApproved ? "pageapprovals-status-approved" : "pageapprovals-status-not-approved";
		$message = $out->msg( $messageKey )->text();
		$out->addHTML( "<div class='page-approval-status'>{$message}</div>" );

		// @phpstan-ignore-next-line
		if ( $canApprove && !$isApproved ) {
			$approveButtonText = $out->msg( 'pageapprovals-approve-button' )->text();
			$buttonHtml = "<button id='approveButton'>{$approveButtonText}</button>";
			$out->addHTML( $buttonHtml );
		}

		$out->addModules( 'ext.pageApprovals.resources' );
	}

}
