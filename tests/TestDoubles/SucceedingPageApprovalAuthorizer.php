<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Tests\TestDoubles;

use MediaWiki\Page\PageIdentity;
use ProfessionalWiki\PageApprovals\Application\PageApprovalAuthorizer;

class SucceedingPageApprovalAuthorizer implements PageApprovalAuthorizer {

	public function canApprove( PageIdentity $page ): bool {
		return true;
	}

}
