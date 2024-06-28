<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Adapters;

use Title;
use WikiPage;

class PageCategoriesRetriever {

	/**
	 * @return string[]
	 */
	public function getPageCategories( WikiPage $page ): array {
		return array_map(
			fn( Title $categoryTitle ) => $categoryTitle->getText(),
			iterator_to_array( $page->getCategories() )
		);
	}

}
