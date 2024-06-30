<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Application;

use WikiPage;

interface ApprovalAuthorizer {

	public function canApprove( WikiPage $page ): bool;

}
