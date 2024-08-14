<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Adapters;

use ProfessionalWiki\PageApprovals\Application\ApproverRepository;
use Title;
use TitleValue;
use Wikimedia\Rdbms\IDatabase;

class DatabaseApproverRepository implements ApproverRepository {

	public function __construct(
		private readonly IDatabase $database,
	) {
	}

	/**
	 * @return string[]
	 */
	public function getApproverCategories( int $userId ): array {
		$row = $this->database->selectRow(
			'approver_config',
			[ 'ac_categories' ],
			[ 'ac_user_id' => $userId ],
			__METHOD__
		);

		if ( $row === false ) {
			return [];
		}

		return $this->deserializeCategories( $row->ac_categories );
	}

	/**
	 * @return array<array{userId: int, categories: string[]}>
	 */
	public function getApproversWithCategories(): array {
		$res = $this->database->select(
			'approver_config',
			[
				'ac_user_id AS userId',
				'ac_categories AS categories'
			],
			[],
			__METHOD__
		);

		$approvers = [];
		foreach ( $res as $row ) {
			$approvers[] = [
				'userId' => (int)$row->userId,
				'categories' => $this->deserializeCategories( $row->categories )
			];
		}
		return $approvers;
	}

	/**
	 * @param string[] $categoryNames
	 */
	public function setApproverCategories( int $userId, array $categoryNames ): void {
		$this->database->upsert(
			'approver_config',
			[
				'ac_user_id' => $userId,
				'ac_categories' => $this->serializeCategories( $categoryNames ),
			],
			[ 'ac_user_id' ],
			[
				'ac_categories' => $this->serializeCategories( $categoryNames ),
			],
			__METHOD__
		);
	}

	/**
	 * @return string[]
	 */
	public function getAllCategories(): array {
		$result = $this->database->select(
			'approver_config',
			[ 'ac_categories AS categories' ],
			[],
			__METHOD__
		);

		return array_unique(
			array_merge(
				...array_map(
					fn ( $row ) => $this->deserializeCategories( $row->categories ),
					iterator_to_array( $result )
				)
			)
		);
	}

	private function serializeCategories( array $categories ): string {
		return implode( '|', array_unique( array_map(
			fn ( string $category ) => $this->normalizeCategoryTitle( $category ),
			$categories
		) ) );
	}

	private function normalizeCategoryTitle( string $title ): string {
		return Title::newFromText( $title, NS_CATEGORY )?->getDBkey() ?? '';
	}

	private function deserializeCategories( string $serializedCategories ): array {
		return $serializedCategories === ''
			? []
			: $this->getCategoryNamesFromDbKeys( explode( '|', $serializedCategories ) );
	}

	/**
	 * @param string[] $categoryDbKeys
	 * @return string[]
	 */
	private function getCategoryNamesFromDbKeys( array $categoryDbKeys ): array {
		return array_filter( array_map(
			fn( string $dbKey ) => TitleValue::tryNew( NS_CATEGORY, $dbKey )?->getText() ?? '',
			$categoryDbKeys
		) );
	}

}
