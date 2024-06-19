<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals;

class PageApprovals {

	public static function getInstance(): self {
		/** @var ?PageApprovals $instance */
		static $instance = null;
		$instance ??= new self();
		return $instance;
	}

}
