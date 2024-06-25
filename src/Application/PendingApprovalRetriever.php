<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Application;

interface PendingApprovalRetriever {

	/**
	 * @return PendingApproval[]
	 */
	public function getPendingApprovalsForApprover( int $approverId ): array;

}
