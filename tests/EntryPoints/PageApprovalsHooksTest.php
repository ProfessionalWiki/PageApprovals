<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Tests\Integration\HookHandler;

use OutputPage;
use ProfessionalWiki\PageApprovals\EntryPoints\PageApprovalsHooks;
use ProfessionalWiki\PageApprovals\Tests\PageApprovalsIntegrationTest;
use RequestContext;
use Title;

/**
 * @group Database
 * @covers \ProfessionalWiki\PageApprovals\EntryPoints\PageApprovalsHooks
 */
class PageApprovalsHooksTest extends PageApprovalsIntegrationTest {

	public function testOnOutputPageBeforeHTML() {
		$title = Title::newFromText( 'PageApprovalsIntegrationTest' );
		$this->insertPage( $title );

		$context = new RequestContext();
		$context->setTitle( $title );
		$context->setUser( $this->getTestUser()->getUser() );

		$out = new OutputPage( $context );
		$out->setTitle( $title );
		$out->setArticleFlag( true );
		$out->setRevisionId( $this->getServiceContainer()->getWikiPageFactory()->newFromTitle( $title )->getLatest() );

		PageApprovalsHooks::onOutputPageBeforeHTML( $out );

		$this->assertStringContainsString(
			'page-approval-status',
			$out->getHTML(),
			'The page approval status should be displayed.'
		);
	}

}
