<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Adapters;

use ProfessionalWiki\PageApprovals\Application\ApproverRepository;
use ProfessionalWiki\PageApprovals\Application\PendingApproval;
use ProfessionalWiki\PageApprovals\Application\PendingApprovalRetriever;
use TitleValue;
use Wikimedia\Rdbms\IDatabase;
use Wikimedia\Rdbms\IResultWrapper;
use Wikimedia\Rdbms\Subquery;

class DatabasePendingApprovalRetriever implements PendingApprovalRetriever {

	public function __construct(
		private IDatabase $db,
		private ApproverRepository $approverRepository,
		private int $limit = 500
	) {
	}

	/**
	 * @return PendingApproval[]
	 */
	public function getPendingApprovalsForApprover( int $approverId ): array {
		$approverCategories = $this->approverRepository->getApproverCategories( $approverId );

		if ( $approverCategories === [] ) {
			return [];
		}

		return $this->buildPendingApprovals( $this->queryPendingApprovals( $approverCategories ) );
	}

	/**
	 * @param string[] $categories
	 */
	private function queryPendingApprovals( array $categories ): IResultWrapper {
		$latestApprovalSubquery = $this->getLatestApprovalSubquery();

		return $this->db->select(
			[
				'page',
				'revision',
				'categorylinks',
				'latest_approval' => $latestApprovalSubquery
			],
			[
				'page_id',
				'page_namespace',
				'page_title',
				'rev_timestamp',
				'rev_actor',
				'GROUP_CONCAT(DISTINCT cl_to) AS categories',
				'latest_approval.al_is_approved',
				'latest_approval.al_timestamp'
			],
			[
				'cl_to' => $categories,
				$this->db->makeList(
					[
						'latest_approval.al_is_approved' => 0,
						'latest_approval.al_is_approved IS NULL'
					],
					IDatabase::LIST_OR
				)
			],
			__METHOD__,
			[
				'GROUP BY' => 'page_id',
				'LIMIT' => $this->limit
			],
			[
				'revision' => [ 'INNER JOIN', 'page_latest = rev_id' ],
				'categorylinks' => [ 'INNER JOIN', 'page_id = cl_from' ],
				'latest_approval' => [ 'LEFT JOIN', 'page_id = latest_approval.al_page_id' ]
			]
		);
	}

	private function getLatestApprovalSubquery(): Subquery {
		return $this->db->buildSelectSubquery(
			'approval_log',
			[ 'al_page_id', 'al_is_approved', 'al_timestamp' ],
			[],
			__METHOD__,
			[ 'ORDER BY' => 'al_timestamp DESC' ],
			[
				$this->db->buildSelectSubquery(
					'approval_log',
					'MAX(al_timestamp)',
					'al_page_id = outer_approval_log.al_page_id',
					__METHOD__
				) . ' = al_timestamp'
			]
		);
	}

	/**
	 * @return PendingApproval[]
	 */
	private function buildPendingApprovals( IResultWrapper $res ): array {
		$pendingApprovals = [];

		foreach ( $res as $row ) {
			$title = new TitleValue( (int)$row->page_namespace, $row->page_title );
			$categories = explode( ',', $row->categories );
			$lastEditUserName = $this->getUserNameFromActor( (int)$row->rev_actor );

			$pendingApprovals[] = new PendingApproval(
				$title,
				$categories,
				(int)$row->rev_timestamp,
				$lastEditUserName
			);
		}

		return $pendingApprovals;
	}

	private function getUserNameFromActor( int $actorId ): string {
		$name = $this->db->selectField(
			'actor',
			'actor_name',
			[ 'actor_id' => $actorId ],
			__METHOD__
		);

		if ( is_string( $name ) ) {
			return $name;
		}

		return '';
	}
}
