<?php

namespace ProfessionalWiki\PageApprovals\Tests\Integration;

use MediaWikiIntegrationTestCase;
use ProfessionalWiki\PageApprovals\EntryPoints\Specials\SpecialApproverCategories;
use RequestContext;
use PermissionsError;

/**
 * @group Database
 * @covers \ProfessionalWiki\PageApprovals\EntryPoints\Specials\SpecialApproverCategories
 */
class SpecialApproverCategoriesTest extends MediaWikiIntegrationTestCase {

	protected function setUp(): void {
		parent::setUp();
	}

	public function testApproverPageForAdmin() {
		$context = new RequestContext();
		$context->setUser( $this->getTestSysop()->getUser() );
		$specialPage = new SpecialApproverCategories();
		$specialPage->setContext( $context );
		$specialPage->execute( null );
		$output = $context->getOutput()->getHTML();
		$this->assertStringContainsString( '<table', $output );
	}

	public function testSpecialPageAccessForNonAdmin() {
		$context = new RequestContext();
		$context->setUser( $this->getTestUser()->getUser() );
		$specialPage = new SpecialApproverCategories();
		$specialPage->setContext( $context );
		$this->expectException( PermissionsError::class );
		$specialPage->execute( null );
	}

}
