<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Tests;

use MediaWiki\MediaWikiServices;
use Title;

class PageApprovalsIntegrationTest extends \MediaWikiIntegrationTestCase {

	protected function getIdOfExistingPage( string $titleText ): int {
		$title = Title::newFromText( $titleText );
		$this->editPage( $title, 'Whatever wikitext' );
		return MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $title )->getId();
	}

}
