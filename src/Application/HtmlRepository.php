<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Application;

interface HtmlRepository {

	public function getApprovedHtml( int $pageId ): ?string;

	public function saveApprovedHtml( int $pageId, string $html ): void;

}
