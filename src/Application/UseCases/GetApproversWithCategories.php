<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Application\UseCases;

use MediaWiki\MediaWikiServices;
use ProfessionalWiki\PageApprovals\Application\ApproverRepository;

class GetApproversWithCategories {

	public function __construct(
		private readonly ApproverRepository $approverRepository
	) {
	}

	/**
	 * @return array<array{username: string, userId: int, categories: string[]}>
	 */
	public function getApproversWithCategories(): array {
		$approversWithCategories = $this->approverRepository->getApproversWithCategories();
		$userFactory = MediaWikiServices::getInstance()->getUserFactory(); // FIXME: this is not injected

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
