<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Adapters;

use ProfessionalWiki\PageApprovals\Application\ApproverRepository;
use Title;
use function Wikimedia\Parsoid\Wt2Html\TT\array_flatten;

class InMemoryApproverRepository implements ApproverRepository {

	/**
	 * @var array<int, string[]>
	 */
	private array $categoriesPerUser = [];

	/**
	 * @return string[]
	 */
	public function getApproverCategories( int $userId ): array {
		return $this->categoriesPerUser[$userId] ?? [];
	}

	/**
	 * @return array<array{userId: int, categories: string[]}>
	 */
	public function getApproversWithCategories(): array {
		return []; // Note: not implemented
	}

	/**
	 * @return string[]
	 */
	public function getAllCategories(): array {
		return array_unique( array_merge( ...$this->categoriesPerUser ) );
	}

	/**
	 * @param string[] $categoryNames
	 */
	public function setApproverCategories( int $userId, array $categoryNames ): void {
		$this->categoriesPerUser[$userId] = array_map(
			fn( string $category ) => $category,
			$categoryNames
		);
	}

}
