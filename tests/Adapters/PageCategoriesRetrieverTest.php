<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Tests\Adapters;

use ProfessionalWiki\PageApprovals\Adapters\PageCategoriesRetriever;
use ProfessionalWiki\PageApprovals\Tests\PageApprovalsIntegrationTest;

/**
 * @covers \ProfessionalWiki\PageApprovals\Adapters\PageCategoriesRetriever
 * @group Database
 */
class PageCategoriesRetrieverTest extends PageApprovalsIntegrationTest {

	private PageCategoriesRetriever $retriever;

	protected function setUp(): void {
		parent::setUp();
		$this->retriever = new PageCategoriesRetriever();
	}

	public function testGetPageCategoriesWithNoCategories(): void {
		$page = $this->createPageWithCategories();

		$categories = $this->retriever->getPageCategories( $page );

		$this->assertSame( [], $categories );
	}

	public function testGetPageCategoriesWithOneCategory(): void {
		$page = $this->createPageWithCategories( [ 'TestCategory' ] );

		$categories = $this->retriever->getPageCategories( $page );

		$this->assertSame( [ 'TestCategory' ], $categories );
	}

	public function testGetPageCategoriesWithMultipleCategories(): void {
		$page = $this->createPageWithCategories( [ 'Category1', 'Category2', 'Category3' ] );

		$categories = $this->retriever->getPageCategories( $page );

		$this->assertSame( [ 'Category1', 'Category2', 'Category3' ], $categories );
	}

	public function testGetPageCategoriesWithSpecialCharacters(): void {
		$page = $this->createPageWithCategories( [ 'Cété_gory', 'Test Category', 'Category_With_Underscores' ] );

		$categories = $this->retriever->getPageCategories( $page );

		$this->assertSame( [ 'Category With Underscores', 'Cété gory', 'Test Category' ], $categories );
	}

}
