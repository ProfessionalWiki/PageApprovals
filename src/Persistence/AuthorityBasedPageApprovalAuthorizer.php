<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Persistence;

use MediaWiki\Page\PageIdentity;
use MediaWiki\Permissions\Authority;
use ProfessionalWiki\PageApprovals\Application\PageApprovalAuthorizer;

class AuthorityBasedPageApprovalAuthorizer implements PageApprovalAuthorizer {

	public function __construct(
		private Authority $authority
	) {
	}

	public function canApprove( PageIdentity $page ): bool {
		// TODO: logic
		return true;
	}

}
