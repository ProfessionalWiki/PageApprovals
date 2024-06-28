<?php

namespace ProfessionalWiki\PageApprovals\Tests\Integration;

use MediaWikiIntegrationTestCase;
use ProfessionalWiki\PageApprovals\EntryPoints\Specials\SpecialApproverCategories;
use RequestContext;
use FauxRequest;
use PermissionsError;
use Wikimedia\Rdbms\Database;

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
		$userGroupManager->addUserToGroup( $testUser1, 'approvers' );

		$testUser2 = $userFactory->newFromName( 'TestUser2' );
		$testUser2->addToDatabase();
		$userGroupManager->addUserToGroup( $testUser2, 'approvers' );
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
		$this->assertStringContainsString( '<table', $output, 'Expected HTML output with table' );
	}

	public function testSpecialPageAccessForNonAdmin() {
		$context = $this->createRequestContext( $this->getTestUser()->getUser() );
		$this->expectException( PermissionsError::class );
		$this->executeSpecialPage( $context );
	}

	public function testAddDeleteCategoryPutRequest() {
		$testUser = $this->getTestSysop()->getUser();
		$actionParams = [
			[ 'action' => 'add', 'username' => 'TestUser1', 'category' => 'TestCategory' ],
			[ 'action' => 'delete', 'username' => 'TestUser1', 'category' => 'TestCategory' ]
		];

		foreach ( $actionParams as $params ) {
			$this->executeSpecialPage( $this->createRequestContext( $testUser, $params, true ) );
		}

		$output = $this->executeSpecialPage( $this->createRequestContext( $testUser ) );
		$this->assertStringNotContainsString( 'TestCategory', $output, 'Category should be deleted' );
	}

}
