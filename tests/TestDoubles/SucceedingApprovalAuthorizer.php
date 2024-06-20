<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Tests\TestDoubles;

use MediaWiki\Page\PageIdentity;
use ProfessionalWiki\PageApprovals\Application\ApprovalAuthorizer;

class SucceedingApprovalAuthorizer implements ApprovalAuthorizer {

	public function canApprove( PageIdentity $page ): bool {
		return true;
	}

}
