<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Tests\TestDoubles;

use MediaWiki\Page\PageIdentity;
use ProfessionalWiki\PageApprovals\Application\ApprovalAuthorizer;

class FailingApprovalAuthorizer implements ApprovalAuthorizer {

	public function canApprove( PageIdentity $page ): bool {
		return false;
	}

}
