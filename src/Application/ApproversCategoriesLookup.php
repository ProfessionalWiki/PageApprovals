<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Application;

use User;

interface ApproversCategoriesLookup {

	/**
	 * @return array<array{username: string, userId: int, categories: string[]}>
	 */
	public function getApproversWithCategories(): array;

}
