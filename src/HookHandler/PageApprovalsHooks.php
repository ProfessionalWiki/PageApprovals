<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\HookHandler;

use MediaWiki\MediaWikiServices;
use MediaWiki\Permissions\PermissionManager;
use OutputPage;
use Title;
use Wikimedia\Rdbms\DBError;

class PageApprovalsHooks {

	public static function onOutputPageBeforeHTML( OutputPage $out ): void {
		$user = $out->getUser();
		$title = $out->getTitle();

		if ( !$title instanceof Title || !$user->isRegistered() ) {
			return;  // Ensure that the user is logged in and title is valid
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
			) && $user->getId() !== $firstUserId;

		$isApproved = false; // This should be determined by your logic

		// @phpstan-ignore-next-line
		$messageKey = $isApproved ? "pageapprovals-status-approved" : "pageapprovals-status-not-approved";
		$message = $out->msg( $messageKey )->text();
		$out->addHTML( "<div class='page-approval-status'>{$message}</div>" );

		// @phpstan-ignore-next-line
		if ( $canApprove && !$isApproved ) {
			$pageId = $title->getArticleID();
			$approveButtonText = $out->msg( 'pageapprovals-approve-button' )->text();
			$buttonHtml = "<button id='approveButton' data-page-id='{$pageId}'>{$approveButtonText}</button>";
			$out->addHTML( $buttonHtml );
		}

		$out->addModules( 'ext.pageApprovals.modules' );
	}

}
