<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Adapters;

use ProfessionalWiki\PageApprovals\Application\ApproverRepository;

class InMemoryApproverRepository implements ApproverRepository {

	/**
	 * @var array<int, string[]>
	 */
	private array $approversCategories = [];

	/**
	 * @return string[]
	 */
	public function getApproverCategories( int $userId ): array {
		return $this->approversCategories[$userId] ?? [];
	}

	/**
	 * @return string[]
	 */
	public function getApproversWithCategories(): array {
		return $this->approversCategories;
	}

	/**
	 * @param string[] $categoryNames
	 */
	public function setApproverCategories( int $userId, array $categoryNames ): void {
		$this->approversCategories[$userId] = $categoryNames;
	}

}
