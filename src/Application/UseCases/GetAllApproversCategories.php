<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Application\UseCases;

use ProfessionalWiki\PageApprovals\Adapters\UsersLookup;
use ProfessionalWiki\PageApprovals\Adapters\DatabaseApproverRepository;
use Wikimedia\Rdbms\IDatabase;
use User;
use MediaWiki\MediaWikiServices;
use MediaWiki\Permissions\PermissionManager;

class GetAllApproversCategories {

	public const APPROVE_RIGHT = 'can-approve-pages';

	public function __construct(
		private readonly UsersLookup $usersLookup,
		private readonly DatabaseApproverRepository $databaseApproverRepository
	) {
	}

	/**
	 * @return array[]
	 */
	public function getAllApproversCategories(): array {
		$allUsers = $this->usersLookup->getAllUsers();
		$approversWithCategories = [];

		foreach ( $allUsers as $user ) {
			if ( MediaWikiServices::getInstance()->getPermissionManager()->userHasRight(
				$user,
				self::APPROVE_RIGHT
			) ) {
				$categories = $this->databaseApproverRepository->getApproverCategories( $user->getId() );
				$approversWithCategories[] = [
					'userid' => $user->getId(),
					'username' => $user->getName(),
					'categories' => $categories
				];
			}
		}

		return $approversWithCategories;
	}

}
