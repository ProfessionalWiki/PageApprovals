<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Application;

use MediaWiki\Page\PageIdentity;

interface PageApprovalAuthorizer {

	public function canApprove( PageIdentity $page ): bool;

}
