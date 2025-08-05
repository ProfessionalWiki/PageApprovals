<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Tests\Integration\HookHandler;

use MediaWiki\Context\RequestContext;
use MediaWiki\SpecialPage\SpecialPage;

/**
 * @group Database
 * @covers \ProfessionalWiki\PageApprovals\EntryPoints\PageApprovalsHooks
 */
class AdminLinksHookTest extends \MediaWikiIntegrationTestCase {

	protected function setUp(): void {
		parent::setUp();

		if ( !class_exists( \ALItem::class ) ) {
			$this->markTestSkipped( 'AdminLinks is not enabled – skipping.' );
		}
	}

	public function testPendingApprovalsLinkAppearsOnAdminLinksPage(): void {
		$this->assertStringContainsString(
			'Special:PendingApprovals',
			$this->getAdminLinksPageHtml(),
			'AdminLinks page should contain a link to Special:PendingApprovals'
		);
	}

	public function testManageApproversLinkAppearsOnAdminLinksPage(): void {
		$this->assertStringContainsString(
			'Special:ManageApprovers',
			$this->getAdminLinksPageHtml(),
			'AdminLinks page should contain a link to Special:ManageApprovers'
		);
	}

	private function getAdminLinksPageHtml(): string {
		$context = new RequestContext();
		$context->setTitle( SpecialPage::getTitleFor( 'AdminLinks' ) );

		$specialPage = $this->getServiceContainer()->getSpecialPageFactory()->getPage( 'AdminLinks' );
		$specialPage->setContext( $context );
		$specialPage->execute( null );

		return $context->getOutput()->getHTML();
	}

}
