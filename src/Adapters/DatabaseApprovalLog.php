<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Adapters;

use ProfessionalWiki\PageApprovals\Application\ApprovalLog;
use ProfessionalWiki\PageApprovals\Application\ApprovalState;
use Wikimedia\Rdbms\IDatabase;

class DatabaseApprovalLog implements ApprovalLog {

	public function __construct(
		private readonly IDatabase $database,
	) {
	}

	public function getApprovalState( int $pageId ): ?ApprovalState {
		$row = $this->database->selectRow(
			'approval_log',
			[ 'al_is_approved', 'al_timestamp', 'al_user_id' ],
			[ 'al_page_id' => $pageId ],
			__METHOD__,
			[ 'ORDER BY' => 'al_timestamp DESC' ]
		);

		if ( $row === false ) {
			return null;
		}

		return new ApprovalState(
			pageId: $pageId,
			isApproved: (bool)$row->al_is_approved,
			approvalTimestamp: $this->binaryToUnixTimestamp( $row->al_timestamp ),
			approverId: $row->al_user_id !== null ? (int)$row->al_user_id : null
		);
	}

	private function binaryToUnixTimestamp( string $binaryTimestamp ): int {
		return (int)wfTimestamp( TS_UNIX, $binaryTimestamp );
	}

	public function unapprovePage( int $pageId, ?int $userId ): void {
		$this->logApprovalChange( $pageId, false, $userId );
	}

	public function approvePage( int $pageId, int $userId ): void {
		$this->logApprovalChange( $pageId, true, $userId );
	}

	private function logApprovalChange( int $pageId, bool $isApproved, ?int $userId ): void {
		$this->database->insert(
			'approval_log',
			[
				'al_page_id' => $pageId,
				'al_timestamp' => $this->database->timestamp(),
				'al_is_approved' => $isApproved ? 1 : 0,
				'al_user_id' => $userId,
			],
			__METHOD__
		);
	}

}
