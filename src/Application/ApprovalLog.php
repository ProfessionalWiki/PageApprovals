<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Application;

interface ApprovalLog {

	public function getApprovalState( int $pageId ): ApprovalState;

	public function markPageAsApproved( int $pageId, int $userId ): void;

	public function markPageAsUnapproved( int $pageId, ?int $userId ): void;

}
