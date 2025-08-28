<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\EntryPoints;

use ALItem;
use ALRow;
use ALTree;
use MediaWiki\Installer\DatabaseUpdater;
use MediaWiki\Output\OutputPage;
use MediaWiki\Parser\ParserOutput;
use ProfessionalWiki\PageApprovals\PageApprovals;
use ProfessionalWiki\PageApprovals\Presentation\OutputPageUiPresenter;

class PageApprovalsHooks {

	public static function onOutputPageParserOutput( OutputPage $out, ParserOutput $parserOutput ): void {
		// TODO: verify called only once when embedding other pages
		// FIXME: we want the evaluation to happen only on render. This hook is also getting called when using the cache.

		if ( self::isApprovablePage( $out ) ) {
			PageApprovals::getInstance()->newEvaluateApprovalStateAction()->evaluate(
				pageId: $out->getWikiPage()->getId(),
				currentPageHtml: PageApprovals::getInstance()->getPageHtmlRetriever()->getPageHtml( $out->getWikiPage()->getId() ),
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
		( new OutputPageUiPresenter( $out ) )->presentUi(
			PageApprovals::getInstance()->newApprovalUiQuery()->getUiState( $out )
		);
	}

	public static function onAdminLinks( ALTree &$adminLinks ): void {
		$generalSection = $adminLinks->getSection( wfMessage( 'adminlinks_general' )->text() );

		if ( $generalSection === null ) {
			return;
		}

		$extensionsRow = $generalSection->getRow( 'extensions' );

		if ( $extensionsRow === null ) {
			$extensionsRow = new ALRow( 'extensions' );
			$generalSection->addRow( $extensionsRow );
		}

		$extensionsRow->addItem( ALItem::newFromSpecialPage( 'PendingApprovals' ) );
		$extensionsRow->addItem( ALItem::newFromSpecialPage( 'ManageApprovers' ) );
	}

}
