<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Tests\Adapters;

use MediaWiki\MediaWikiServices;
use ProfessionalWiki\PageApprovals\Adapters\PageContentRetriever;
use ProfessionalWiki\PageApprovals\Tests\PageApprovalsIntegrationTest;

/**
 * @covers \ProfessionalWiki\PageApprovals\Adapters\PageContentRetriever
 * @group Database
 */
class PageContentRetrieverTest extends PageApprovalsIntegrationTest {

	public function testGetsContent(): void {
		$retriever = $this->newWikiPageContentRetriever();

		$content = $retriever->getPageContent( $this->getIdOfExistingPage( 'Foo Bar' ) );

		$this->assertSame( <<<EOT
<p>Whatever wikitext
</p>
EOT
			, $content );
	}

	private function newWikiPageContentRetriever(): PageContentRetriever {
		return new PageContentRetriever(
			MediaWikiServices::getInstance()->getWikiPageFactory()
		);
	}

	public function testGetsLatestContent(): void {
		$retriever = $this->newWikiPageContentRetriever();
		$pageId = $this->getIdOfExistingPage( 'Foo Bar Baz' );

		$this->editPage( 'Foo Bar Baz', 'Dolor sit amet' );

		$content = $retriever->getPageContent( $pageId );

		$this->assertSame( <<<EOT
<p>Dolor sit amet
</p>
EOT
			, $content );
	}

	public function testGetPageContentReturnsNullForMissingPage(): void {
		$retriever = $this->newWikiPageContentRetriever();

		$content = $retriever->getPageContent( 424242 );

		$this->assertNull( $content );
	}

}
