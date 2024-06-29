<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Application\UseCases;

use ProfessionalWiki\PageApprovals\Adapters\DatabaseApproverRepository;
use MediaWiki\MediaWikiServices;

class GetApproversWithCategories {

	public function __construct(
		private readonly DatabaseApproverRepository $databaseApproverRepository
	) {
	}

	/**
	 * @return array<array{username: string, userId: int, categories: string[]}>
	 */
	public function getApproversWithCategories(): array {
		$approversWithCategories = $this->databaseApproverRepository->getApproversWithCategories();
		$userFactory = MediaWikiServices::getInstance()->getUserFactory();

		$approvers = [];
		foreach ( $approversWithCategories as $approver ) {
			$user = $userFactory->newFromId( (int)$approver['userId'] );
			$approvers[] = [
				'username' => $user->getName(),
				'userId' => $approver['userId'],
				'categories' => $approver['categories']
			];
		}

		return $approvers;
	}

}
