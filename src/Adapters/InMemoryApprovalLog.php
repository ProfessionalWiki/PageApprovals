<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Adapters;

use ProfessionalWiki\PageApprovals\Application\ApprovalLog;
use ProfessionalWiki\PageApprovals\Application\ApprovalState;

class InMemoryApprovalLog implements ApprovalLog {

	/**
	 * @var array<int, ApprovalState[]>
	 */
	private array $events = [];

	private int $nextUnixTimestamp = 0;

	public function getApprovalState( int $pageId ): ?ApprovalState {
		$events = $this->events[$pageId] ?? [];
		return $events === [] ? null : end( $events );
	}

	public function unapprovePage( int $pageId, ?int $userId ): void {
		$this->events[$pageId][] = new ApprovalState(
			pageId: $pageId,
			isApproved: false,
			approvalTimestamp: $this->getTime(),
			approverId: $userId
		);
	}

	public function approvePage( int $pageId, int $userId ): void {
		$this->events[$pageId][] = new ApprovalState(
			pageId: $pageId,
			isApproved: true,
			approvalTimestamp: $this->getTime(),
			approverId: $userId
		);
	}

	private function getTime(): int {
		return $this->nextUnixTimestamp++;
	}

}
