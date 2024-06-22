<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\EntryPoints;

use OutputPage;
use ParserOutput;
use ProfessionalWiki\PageApprovals\PageApprovals;

class PageApprovalsHooks {

	public static function onOutputPageParserOutput( OutputPage $out, ParserOutput $parserOutput ): void {
		if ( $out->isArticle() ) {
			PageApprovals::getInstance()->newEvaluateApprovalStateAction()->evaluate(
				pageId: $out->getWikiPage()->getId(),
				currentPageHtml: $parserOutput->getRawText(),
			);
		}
	}

}
