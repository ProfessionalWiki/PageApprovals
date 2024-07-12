<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Tests\Adapters;

use MediaWikiIntegrationTestCase;
use ProfessionalWiki\PageApprovals\Adapters\DatabaseApproverRepository;
use ProfessionalWiki\PageApprovals\Application\ApproverRepository;

/**
 * @covers \ProfessionalWiki\PageApprovals\Adapters\DatabaseApproverRepository
 * @group Database
 */
class DatabaseApproverRepositoryTest extends MediaWikiIntegrationTestCase {

	protected function setUp(): void {
		parent::setUp();
		$this->tablesUsed[] = 'approver_config';
	}

	private function newRepository(): ApproverRepository {
		return new DatabaseApproverRepository( $this->db );
	}

	public function testGetApproverCategoriesReturnsEmptyArrayForNonexistentUser(): void {
		$repository = $this->newRepository();

		$this->assertSame( [], $repository->getApproverCategories( 404 ) );
	}

	public function testGetAllCategories(): void {
		$repository = $this->newRepository();

		$repository->setApproverCategories( 1, [ 'Category1', 'Category2' ] );
		$repository->setApproverCategories( 2, [ 'Category3', 'Category4' ] );

		$this->assertSame(
			[ 'Category1', 'Category2', 'Category3', 'Category4' ],
			$repository->getAllCategories(),
			'getAllCategories should return all unique categories across all approvers'
		);
	}

	public function testSetAndGetApproverCategories(): void {
		$repository = $this->newRepository();
		$userId = 1;
		$categories = [ 'Category1', 'Category2', 'Category3' ];

		$repository->setApproverCategories( $userId, $categories );

		$this->assertSame( $categories, $repository->getApproverCategories( $userId ) );
	}

	public function testUpdateApproverCategories(): void {
		$repository = $this->newRepository();
		$userId = 1;
		$initialCategories = [ 'Category1', 'Category2' ];
		$updatedCategories = [ 'Category2', 'Category3', 'Category4' ];

		$repository->setApproverCategories( $userId, $initialCategories );
		$repository->setApproverCategories( $userId, $updatedCategories );

		$this->assertSame( $updatedCategories, $repository->getApproverCategories( $userId ) );
	}

	public function testSetEmptyCategoryList(): void {
		$repository = $this->newRepository();
		$userId = 1;
		$initialCategories = [ 'Category1', 'Category2' ];
		$emptyCategories = [];

		$repository->setApproverCategories( $userId, $initialCategories );
		$repository->setApproverCategories( $userId, $emptyCategories );

		$this->assertSame( $emptyCategories, $repository->getApproverCategories( $userId ) );
	}

	public function testMultipleUsers(): void {
		$repository = $this->newRepository();
		$user1Id = 1;
		$user2Id = 2;
		$categories1 = [ 'Category1', 'Category2' ];
		$categories2 = [ 'Category3', 'Category4' ];

		$repository->setApproverCategories( $user1Id, $categories1 );
		$repository->setApproverCategories( $user2Id, $categories2 );

		$this->assertSame( $categories1, $repository->getApproverCategories( $user1Id ) );
		$this->assertSame( $categories2, $repository->getApproverCategories( $user2Id ) );
	}

	/**
	 * @dataProvider provideCategoryTestCases
	 */
	public function testCategorySerializationAndDeserialization(
		array $categories,
		array $expectedRetrievedCategories,
		string $caseName
	): void {
		$repository = $this->newRepository();
		$userId = 42;

		$repository->setApproverCategories( $userId, $categories );
		$retrievedCategories = $repository->getApproverCategories( $userId );

		$this->assertSame(
			$expectedRetrievedCategories,
			$retrievedCategories,
			"Failed to correctly serialize and deserialize categories for case: $caseName"
		);
	}

	public function testGetApproversWithCategoriesReturnsCorrectData(): void {
		$repository = $this->newRepository();

		$userId1 = 1;
		$userId2 = 2;
		$categories1 = [ 'Category1' ];
		$categories2 = [];

		$repository->setApproverCategories( $userId1, $categories1 );
		$repository->setApproverCategories( $userId2, $categories2 );

		$approversWithCategories = $repository->getApproversWithCategories();

		$expected = [
			[
				'userId' => $userId1,
				'categories' => $categories1
			],
			[
				'userId' => $userId2,
				'categories' => $categories2
			]
		];

		$this->assertEquals(
			$expected,
			$approversWithCategories,
			"The fetched approvers did not match the expected approvers."
		);
	}

	public static function provideCategoryTestCases(): \Generator {
		yield 'Categories with spaces' => [
			[
				'Category with spaces',
				'Another category with spaces'
			],
			[
				'Category_with_spaces',
				'Another_category_with_spaces'
			],
			'Categories with spaces'
		];

		yield 'Categories with special characters' => [
			[
				'Category:Subcategory',
				'Category_with_underscores',
				'Category&with&ampersands'
			],
			[
				'Subcategory',
				'Category_with_underscores',
				'Category&with&ampersands'
			],
			'Categories with special characters'
		];

		yield 'Unicode categories' => [
			[
				'カテゴリ',
				'Категория',
				'فئة'
			],
			[
				'カテゴリ',
				'Категория',
				'فئة'
			],
			'Unicode categories'
		];

		yield 'Empty category list' => [
			[],
			[],
			'Empty category list'
		];

		yield 'Single category' => [
			[ 'SingleCategory' ],
			[ 'SingleCategory' ],
			'Single category'
		];

		yield 'Category with leading/trailing spaces' => [
			[
				' Leading space',
				'Trailing space ',
				' Both sides '
			],
			[
				'Leading_space',
				'Trailing_space',
				'Both_sides'
			],
			'Category with leading/trailing spaces'
		];

		yield 'Category with case insensitive letters' => [
			[
				'Foo Bar',
				'foo Bar',
				'Foo bar',
				'foo bar'
			],
			[
				'Foo_Bar',
				'Foo_bar'
			],
			'Category with case insensitive letters'
		];
	}

}
