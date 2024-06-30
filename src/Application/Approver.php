<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Application;

class Approver {

	/**
	 * @param string[] $categories
	 */
	public function __construct(
		public readonly string $username,
		public readonly int $userId,
		public readonly array $categories,
	) {
	}

}
