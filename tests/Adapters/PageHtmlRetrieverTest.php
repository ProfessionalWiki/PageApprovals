<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Tests\Adapters;

use MediaWiki\MediaWikiServices;
use ProfessionalWiki\PageApprovals\Adapters\PageHtmlRetriever;
use ProfessionalWiki\PageApprovals\Tests\PageApprovalsIntegrationTest;

/**
 * @covers \ProfessionalWiki\PageApprovals\Adapters\PageHtmlRetriever
 * @group Database
 */
class PageHtmlRetrieverTest extends PageApprovalsIntegrationTest {

	public function testGetsHtml(): void {
		$retriever = $this->newPageHtmlRetriever();

		$html = $retriever->getPageHtml(
			$this->getIdOfExistingPage( 'Foo Bar', 'Lorem Ipsum' )
		);

		$this->assertSame( <<<EOT
<p>Lorem Ipsum
</p>
EOT
			, $html );
	}

	private function newPageHtmlRetriever(): PageHtmlRetriever {
		return new PageHtmlRetriever(
			MediaWikiServices::getInstance()->getWikiPageFactory()
		);
	}

	public function testGetsLatestHtml(): void {
		$retriever = $this->newPageHtmlRetriever();
		$pageId = $this->getIdOfExistingPage( 'Foo Bar Baz' );

		$this->editPage( 'Foo Bar Baz', 'Dolor sit amet' );

		$html = $retriever->getPageHtml( $pageId );

		$this->assertSame( <<<EOT
<p>Dolor sit amet
</p>
EOT
			, $html );
	}

	public function testGetPageHtmlReturnsNullForMissingPage(): void {
		$retriever = $this->newPageHtmlRetriever();

		$html = $retriever->getPageHtml( 424242 );

		$this->assertNull( $html );
	}

}
