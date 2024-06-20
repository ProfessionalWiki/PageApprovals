<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Adapters;

use MediaWiki\Page\PageIdentity;
use MediaWiki\Permissions\Authority;
use ProfessionalWiki\PageApprovals\Application\ApprovalAuthorizer;

class AuthorityBasedApprovalAuthorizer implements ApprovalAuthorizer {

	public function __construct(
		private Authority $authority
	) {
	}

	public function canApprove( PageIdentity $page ): bool {
		// TODO: logic
		return true;
	}

}
