<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Adapters;

use MediaWiki\Page\PageIdentity;
use MediaWiki\Page\WikiPageFactory;
use Title;

class PageCategoriesRetriever {

	public function __construct(
		private WikiPageFactory $pageFactory
	) {
	}

	/**
	 * @return string[]
	 */
	public function getPageCategories( PageIdentity $page ): array {
		$wikiPage = $this->pageFactory->newFromTitle( $page );

		return array_map(
			fn( Title $categoryTitle ) => $categoryTitle->getText(),
			iterator_to_array( $wikiPage->getCategories() )
		);
	}

}
