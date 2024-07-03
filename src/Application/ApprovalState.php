<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Application;

class ApprovalState {

	/**
	 * @param int|null $approverId ID of the user who (un)approved the page, or null for automatic unapproval
	 * @param string|null $approverUserName
	 */
	public function __construct(
		public readonly int $pageId,
		public readonly bool $isApproved,
		public readonly int $approvalTimestamp,
		public readonly ?int $approverId,
		public readonly ?string $approverUserName,
	) {
	}

}
