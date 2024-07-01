<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Application;

use TitleValue;

class PendingApproval {

	/**
	 * @param string[] $categories The category DB keys.
	 */
	public function __construct(
		public readonly TitleValue $title,
		public readonly array $categories,
		public readonly int $lastEditTimestamp,
		public readonly string $lastEditUserName,
	) {
	}

}
