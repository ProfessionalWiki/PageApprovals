<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Application;

interface ApprovalLog {

	public function getApprovalState( int $pageId ): ?ApprovalState;

	public function unapprovePage( int $pageId, ?int $userId ): void;

	public function approvePage( int $pageId, int $userId ): void;

}
