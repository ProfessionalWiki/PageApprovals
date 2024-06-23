<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Tests\Integration\HookHandler;

use MediaWikiIntegrationTestCase;
use OutputPage;
use ProfessionalWiki\PageApprovals\EntryPoints\PageApprovalsHooks;
use Title;
use RequestContext;
use User;

/**
 * @group Database
 * @covers \ProfessionalWiki\PageApprovals\EntryPoints\PageApprovalsHooks
 */
class PageApprovalsHooksTest extends MediaWikiIntegrationTestCase {

	public function testOnOutputPageBeforeHTML() {
		$user = $this->getTestUser()->getUser();
		$title = Title::newFromText( 'TestPage' );

		$context = new RequestContext();
		$context->setTitle( $title );
		$context->setUser( $user );

		$out = new OutputPage( $context );
		$out->setTitle( $title );

		$text = '';

		PageApprovalsHooks::onOutputPageBeforeHTML( $out, $text );

		$this->assertStringContainsString(
			'page-approval-status',
			$out->getHTML(),
			'The page approval status should be displayed.'
		);
	}

}
