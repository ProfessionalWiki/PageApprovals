<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Tests\TestDoubles;

use ProfessionalWiki\PageApprovals\Application\ApprovalLog;
use ProfessionalWiki\PageApprovals\Application\ApprovalState;
use Wikimedia\Rdbms\DBError;

class ThrowingApprovalLog implements ApprovalLog {

	public function getApprovalState( int $pageId ): ?ApprovalState {
		throw new DBError( null, 'getApprovalState' );
	}

	public function unapprovePage( int $pageId, ?int $userId ): void {
		throw new DBError( null, 'unapprovePage' );
	}

	public function approvePage( int $pageId, int $userId ): void {
		throw new DBError( null, 'approvePage' );
	}

}
