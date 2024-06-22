<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Adapters;

use ProfessionalWiki\PageApprovals\Application\ApproverRepository;
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

	private function serializeCategories( array $categories ): string {
		return implode( '|', $categories );
	}

	private function deserializeCategories( string $serializedCategories ): array {
		return $serializedCategories === '' ? [] : explode( '|', $serializedCategories );
	}

}
