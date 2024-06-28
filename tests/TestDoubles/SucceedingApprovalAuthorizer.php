<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Tests\TestDoubles;

use ProfessionalWiki\PageApprovals\Application\ApprovalAuthorizer;
use WikiPage;

class SucceedingApprovalAuthorizer implements ApprovalAuthorizer {

	public function canApprove( WikiPage $page ): bool {
		return true;
	}

}
