<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Tests;

use MediaWiki\MediaWikiServices;
use Title;

class PageApprovalsIntegrationTest extends \MediaWikiIntegrationTestCase {

	protected function getIdOfExistingPage( string $titleText, string $text = 'Whatever wikitext' ): int {
		$title = Title::newFromText( $titleText );
		$this->editPage( $title, $text );
		return MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $title )->getId();
	}

}
