<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\EntryPoints;

use DatabaseUpdater;
use OutputPage;
use ParserOutput;
use ProfessionalWiki\PageApprovals\PageApprovals;
use Title;

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

		$pageIsApproved = PageApprovals::getInstance()->getApprovalLog()->getApprovalState( pageId: $out->getWikiPage()->getId() )?->isApproved ?? false;
		$isApproverForPage = array_intersect(
				PageApprovals::getInstance()->getApproverRepository()->getApproverCategories( $out->getUser()->getId() ),
				array_map(
					fn( Title $category ) => $category->getText(), // TODO: verify handling of different category names
					iterator_to_array( $out->getWikiPage()->getCategories() ),
				)
			) !== [];

		$out->addHTML(
			PageApprovals::getInstance()->getTemplateParser()->processTemplate(
				'PageApprovalStatus',
				[
					'isPageApproved' => $pageIsApproved,
					'canApprove' => $isApproverForPage,
					'approveButtonText' => $out->msg( 'pageapprovals-approve-button' )->text(),
					'unapproveButtonText' => $out->msg( 'pageapprovals-unapprove-button' )->text(),
					'approvalStatusMessage' => $out->msg(
						$pageIsApproved ? 'pageapprovals-status-approved' : 'pageapprovals-status-not-approved'
					)->text()
				]
			)
		);

		$out->addModules( 'ext.pageApprovals.resources' );
	}

}
