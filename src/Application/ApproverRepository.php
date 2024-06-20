<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Application;

interface ApproverRepository {

	/**
	 *
	 * @return string[]
	 */
	public function getApproverCategories( int $userId ): array;

	/**
	 * @param string[] $categoryNames
	 */
	public function setApproverCategories( int $userId, array $categoryNames ): void;

}
