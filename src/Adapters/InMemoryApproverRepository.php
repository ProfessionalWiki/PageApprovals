<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Adapters;

use ProfessionalWiki\PageApprovals\Application\ApproverRepository;

class InMemoryApproverRepository implements ApproverRepository {

	/**
	 * @var array<int, string[]>
	 */
	private array $categoriesByUserId = [];

	/**
	 * @return string[]
	 */
	public function getApproverCategories( int $userId ): array {
		return $this->categoriesByUserId[$userId] ?? [];
	}

	/**
	 * @param string[] $categoryNames
	 */
	public function setApproverCategories( int $userId, array $categoryNames ): void {
		$this->categoriesByUserId[$userId] = $categoryNames;
	}

}
