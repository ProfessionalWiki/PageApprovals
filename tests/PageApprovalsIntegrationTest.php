<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Tests;

use Title;
use WikiPage;

class PageApprovalsIntegrationTest extends \MediaWikiIntegrationTestCase {

	protected function createPageWithText( string $text = 'Whatever wikitext' ): WikiPage {
		$page = $this->getServiceContainer()->getWikiPageFactory()->newFromTitle( $this->createUniqueTitle() );

		$this->editPage( $page, $text );

		return $page;
	}

	protected function createPageWithCategories( array $categories = [] ): WikiPage {
		$page = $this->getServiceContainer()->getWikiPageFactory()->newFromTitle( $this->createUniqueTitle() );

		$this->editPage( $page, $page->getTitle()->getText() . $this->buildCategoryWikitext( $categories ) );

		return $page;
	}

	private function createUniqueTitle(): Title {
		static $pageCounter = 0;
		return Title::newFromText( 'PATestPage' . ++$pageCounter );
	}

	private function buildCategoryWikitext( array $categories ): string {
		return implode(
			"\n",
			array_map(
				fn( $category ) => "[[Category:$category]]",
				$categories
			)
		);
	}

	protected function insertApprovalLogEntry( int $pageId, bool $isApproved, string $timestamp = null ): void {
		$this->db->insert(
			'approval_log',
			[
				'al_page_id' => $pageId,
				'al_timestamp' => $timestamp ?? $this->db->timestamp(),
				'al_is_approved' => $isApproved ? 1 : 0,
				'al_user_id' => 1
			]
		);
	}

}
