<?php

namespace ProfessionalWiki\PageApprovals\Adapters;

use ProfessionalWiki\PageApprovals\Application\ApproversCategoriesLookupInterface;
use Wikimedia\Rdbms\IDatabase;

class ApproversCategoriesLookup implements ApproversCategoriesLookupInterface {

	private const APPROVERS_GROUP = 'approvers';

	public function __construct(
		private readonly IDatabase $database
	) {
	}

	/**
	 * @return array<array{userId: int, username: string, categories: string[]}>
	 */
	public function getApproversWithCategories(): array {
		$res = $this->database->select(
			[
				'u' => 'user',
				'ug' => 'user_groups',
				'ac' => 'approver_config'
			],
			[
				'u.user_id',
				'u.user_name',
				'ac.ac_categories'
			],
			[
				'ug.ug_group' => self::APPROVERS_GROUP
			],
			__METHOD__,
			[
				'GROUP BY' => 'u.user_id'
			],
			[
				'ug' => [ 'JOIN', 'u.user_id = ug.ug_user' ],
				'ac' => [ 'LEFT JOIN', 'u.user_id = ac.ac_user_id' ]
			]
		);

		return array_map( static function ( $row ) {
			return [
				'userId' => (int)$row->user_id,
				'username' => (string)$row->user_name,
				'categories' => $row->ac_categories ? explode( '|', $row->ac_categories ) : []
			];
		}, iterator_to_array( $res ) );
	}

}
