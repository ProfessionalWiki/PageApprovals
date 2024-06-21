<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Adapters;

use ProfessionalWiki\PageApprovals\Application\HtmlRepository;
use Wikimedia\Rdbms\IDatabase;

class DatabaseHtmlRepository implements HtmlRepository {

	public function __construct(
		private readonly IDatabase $database,
	) {
	}

	public function getApprovedHtml( int $pageId ): ?string {
		$row = $this->database->selectRow(
			'approved_html',
			[ 'ah_html' ],
			[ 'ah_page_id' => $pageId ],
			__METHOD__
		);

		if ( $row === false ) {
			return null;
		}

		return $row->ah_html;
	}

	public function saveApprovedHtml( int $pageId, string $html ): void {
		$this->database->upsert(
			'approved_html',
			[
				'ah_page_id' => $pageId,
				'ah_html' => $html,
				'ah_timestamp' => $this->database->timestamp(),
			],
			[ 'ah_page_id' ],
			[
				'ah_html' => $html,
				'ah_timestamp' => $this->database->timestamp(),
			],
			__METHOD__
		);
	}

}
