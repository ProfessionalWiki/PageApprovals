<?php

namespace ProfessionalWiki\PageApprovals\Tests\Integration;

use MediaWikiIntegrationTestCase;
use ProfessionalWiki\PageApprovals\EntryPoints\Specials\SpecialApproverCategories;
use RequestContext;
use FauxRequest;
use PermissionsError;

/**
 * @group Database
 * @covers \ProfessionalWiki\PageApprovals\EntryPoints\Specials\SpecialApproverCategories
 */
class SpecialApproverCategoriesTest extends MediaWikiIntegrationTestCase {

	protected function setUp(): void {
		parent::setUp();

		$userFactory = $this->getServiceContainer()->getUserFactory();
		$userGroupManager = $this->getServiceContainer()->getUserGroupManager();

		$testUser1 = $userFactory->newFromName( 'TestUser1' );
		$testUser1->addToDatabase();
		$userGroupManager->addUserToGroup( $testUser1, 'sysop' );

		$testUser2 = $userFactory->newFromName( 'TestUser2' );
		$testUser2->addToDatabase();
		$userGroupManager->addUserToGroup( $testUser2, 'sysop' );
	}

	private function createRequestContext( $user, $requestParams = [], $isPost = false ) {
		$context = new RequestContext();
		$context->setUser( $user );
		$context->setRequest( new FauxRequest( $requestParams, $isPost ) );
		return $context;
	}

	private function executeSpecialPage( RequestContext $context ) {
		$specialPage = new SpecialApproverCategories();
		$specialPage->setContext( $context );
		$specialPage->execute( null );
		return $context->getOutput()->getHTML();
	}

	public function testApproverPageForAdmin() {
		$context = $this->createRequestContext( $this->getTestSysop()->getUser() );
		$output = $this->executeSpecialPage( $context );
		$this->assertStringContainsString( '<table', $output );
	}

	public function testSpecialPageAccessForNonAdmin() {
		$context = $this->createRequestContext( $this->getTestUser()->getUser() );
		$this->expectException( PermissionsError::class );
		$this->executeSpecialPage( $context );
	}

	public function testAddDeleteCategoryPutRequest() {
		$testUser = $this->getTestUser( [ 'sysop' ] )->getUser();

		$this->executeSpecialPage( $this->createRequestContext( $testUser, [
			'action' => 'add',
			'username' => 'TestUser1',
			'category' => 'TestCategory'
		], true ) );

		$output = $this->executeSpecialPage( $this->createRequestContext( $testUser ) );
		$this->assertStringContainsString( 'TestCategory', $output );

		$context = $this->createRequestContext( $testUser );
		$output = $this->executeSpecialPage( $context );
		$this->assertStringContainsString( 'TestCategory', $output );

		$this->executeSpecialPage( $this->createRequestContext( $testUser, [
			'action' => 'delete',
			'username' => 'TestUser1',
			'category' => 'TestCategory'
		], true ) );

		$output = $this->executeSpecialPage( $this->createRequestContext( $testUser ) );
		$this->assertStringNotContainsString( 'TestCategory', $output );
	}

}
