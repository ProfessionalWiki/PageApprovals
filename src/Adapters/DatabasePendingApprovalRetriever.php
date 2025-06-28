<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Adapters;

use ProfessionalWiki\PageApprovals\Application\ApproverRepository;
use ProfessionalWiki\PageApprovals\Application\PendingApproval;
use ProfessionalWiki\PageApprovals\Application\PendingApprovalRetriever;
use MediaWiki\Title\TitleValue;
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
				'cl_to' => $this->normalizeCategoryTitles( $categories ),
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
				'ORDER BY' => 'rev_timestamp DESC',
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
			[
				'a' => 'approval_log',
				'latest' => $this->db->buildSelectSubquery(
					'approval_log',
					[
						'al_page_id',
						'MAX(al_timestamp) AS max_timestamp'
					],
					[],
					__METHOD__,
					[
						'GROUP BY' => 'al_page_id'
					]
				)
			],
			[
				'a.al_page_id',
				'a.al_timestamp',
				'a.al_is_approved'
			],
			[
				'a.al_page_id = latest.al_page_id',
				'a.al_timestamp = latest.max_timestamp'
			],
			__METHOD__
		);
	}

	/**
	 * @param string[] $categories
	 * @return string[]
	 */
	private function normalizeCategoryTitles( array $categories ): array {
		return array_map(
			fn( string $category ) => TitleValue::tryNew( NS_CATEGORY, $category )?->getDBkey() ?? '',
			$categories
		);
	}

	/**
	 * @return PendingApproval[]
	 */
	private function buildPendingApprovals( IResultWrapper $res ): array {
		$pendingApprovals = [];

		foreach ( $res as $row ) {
			$title = new TitleValue( (int)$row->page_namespace, $row->page_title );
			$categories = $this->getCategoryNamesFromDbKeys( explode( ',', $row->categories ) );
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

	/**
	 * @param string[] $categoryDbKeys
	 * @return string[]
	 */
	private function getCategoryNamesFromDbKeys( array $categoryDbKeys ): array {
		return array_filter( array_map(
			fn( string $dbKey ) => TitleValue::tryNew( NS_CATEGORY, $dbKey )?->getText() ?? '',
			$categoryDbKeys
		) );
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
