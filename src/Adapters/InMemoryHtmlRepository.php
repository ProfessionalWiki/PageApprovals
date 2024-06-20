<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Adapters;

use ProfessionalWiki\PageApprovals\Application\HtmlRepository;

class InMemoryHtmlRepository implements HtmlRepository {

	/**
	 * @var array<int, string>
	 */
	private array $htmlByPageId = [];

	public function getApprovedHtml( int $pageId ): ?string {
		return $this->htmlByPageId[$pageId] ?? null;
	}

	public function saveApprovedHtml( int $pageId, string $html ): void {
		$this->htmlByPageId[$pageId] = $html;
	}

}
