<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Adapters;

use MediaWiki\Page\WikiPageFactory;
use ParserOptions;

class PageContentRetriever {

	public function __construct(
		private WikiPageFactory $pageFactory
	) {
	}

	public function getPageContent( int $pageId ): ?string {
		$page = $this->pageFactory->newFromID( $pageId );

		if ( $page === null ) {
			return null;
		}

		$parserOutput = $page->getParserOutput( ParserOptions::newFromAnon() );

		if ( $parserOutput === false ) {
			return null;
		}

		return $parserOutput->getRawText();
	}

}
