<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Application;

use MediaWiki\Page\PageIdentity;

interface ApprovalAuthorizer {

	public function canApprove( PageIdentity $page ): bool;

}
