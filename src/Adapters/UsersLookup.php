<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Adapters;

use ProfessionalWiki\PageApprovals\Application\UsersLookupInterface;
use Wikimedia\Rdbms\IDatabase;
use MediaWiki\MediaWikiServices;
use MediaWiki\User\UserFactory;
use User;

class UsersLookup implements UsersLookupInterface {

	private readonly UserFactory $userFactory;

	public function __construct(
		private readonly IDatabase $database
	) {
		$this->userFactory = MediaWikiServices::getInstance()->getUserFactory();
	}

	/**
	 * @return User[]
	 */
	public function getAllUsers(): array {
		$res = $this->database->select(
			'user',
			[ 'user_id', 'user_name' ],
			[],
			__METHOD__
		);

		$users = [];
		foreach ( $res as $row ) {
			$users[] = $this->userFactory->newFromId( (int)$row->user_id );
		}

		return $users;
	}

}
