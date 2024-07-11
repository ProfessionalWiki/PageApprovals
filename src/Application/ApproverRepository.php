<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Application;

interface ApproverRepository {

	/**
	 * @return string[]
	 */
	public function getApproverCategories( int $userId ): array;

	/**
	 * @return array<array{userId: int, categories: string[]}>
	 */
	public function getApproversWithCategories(): array;

	/**
	 * @return string[]
	 */
	public function getAllAssignedCategories(): array;

	/**
	 * @param string[] $categoryNames
	 */
	public function setApproverCategories( int $userId, array $categoryNames ): void;

}
