<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Application\UseCases;

use ProfessionalWiki\PageApprovals\Adapters\DatabaseApproverRepository;

class SetApproverCategories {

	public function __construct(
		private readonly DatabaseApproverRepository $databaseApproverRepository
	) {
	}

	/**
	 * @param string[] $categories
	 */
	public function setApproverCategories( int $userId, array $categories ): void {
		$this->databaseApproverRepository->setApproverCategories( $userId, $categories );
	}

}
