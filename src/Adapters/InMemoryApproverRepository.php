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
	 * @return string[]
	 */
	public function getAllCategories(): array {
		return array_unique( array_merge( ...$this->approversCategories ) );
	}

	/**
	 * @param string[] $categoryNames
	 */
	public function setApproverCategories( int $userId, array $categoryNames ): void {
		$this->approversCategories[$userId] = array_map(
			fn( string $category ) => $this->normalizeCategoryTitle( $category ),
			$categoryNames
		);
	}

	private function normalizeCategoryTitle( string $title ): string {
		return Title::newFromText( $title, NS_CATEGORY )?->getDBkey() ?? '';
	}

}
