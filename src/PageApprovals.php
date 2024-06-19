<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals;

use ProfessionalWiki\PageApprovals\EntryPoints\REST\ApprovePageApi;
use ProfessionalWiki\PageApprovals\EntryPoints\REST\UnapprovePageApi;

class PageApprovals {

	public static function getInstance(): self {
		/** @var ?PageApprovals $instance */
		static $instance = null;
		$instance ??= new self();
		return $instance;
	}

	public static function newApprovePageApi(): ApprovePageApi {
		return new ApprovePageApi();
	}

	public static function newUnapprovePageApi(): UnapprovePageApi {
		return new UnapprovePageApi();
	}

}
