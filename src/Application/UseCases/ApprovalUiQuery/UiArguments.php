<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Application\UseCases\ApprovalUiQuery;

class UiArguments {

	public function __construct(
		public readonly bool $showUi,
		public readonly bool $userIsApprover,
		public readonly bool $pageIsApproved,
		public readonly int $approvalTimestamp,
		public readonly ?int $approverId,
		public readonly ?string $approverRealName
	) {
	}

}
