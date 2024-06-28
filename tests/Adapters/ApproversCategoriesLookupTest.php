<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Tests\Adapters;

use MediaWikiIntegrationTestCase;
use ProfessionalWiki\PageApprovals\Adapters\ApproversCategoriesLookup;
use Wikimedia\Rdbms\LoadBalancer;

/**
 * @group Database
 * @covers \ProfessionalWiki\PageApprovals\Adapters\ApproversCategoriesLookup
 */
class ApproversCategoriesLookupTest extends MediaWikiIntegrationTestCase {

	protected function setUp(): void {
		parent::setUp();
		$this->tablesUsed = [ 'approver_config' ];

		$userFactory = $this->getServiceContainer()->getUserFactory();
		$userGroupManager = $this->getServiceContainer()->getUserGroupManager();

		$testUser1 = $userFactory->newFromName( 'TestUser1' );
		$testUser1->addToDatabase();
		$userGroupManager->addUserToGroup( $testUser1, 'approvers' );

		$testUser2 = $userFactory->newFromName( 'TestUser2' );
		$testUser2->addToDatabase();
		$userGroupManager->addUserToGroup( $testUser2, 'approvers' );

		$db = $this->getServiceContainer()->getDBLoadBalancer()->getConnection( LoadBalancer::DB_PRIMARY );
		$db->insert( 'approver_config', [
			[ 'ac_user_id' => $testUser1->getId(), 'ac_categories' => 'Category1|Category2' ],
			[ 'ac_user_id' => $testUser2->getId(), 'ac_categories' => '' ]
		] );
	}

	public function testGetApproversWithCategories() {
		$db = $this->getServiceContainer()->getDBLoadBalancer()->getConnection( LoadBalancer::DB_REPLICA );
		$usersLookup = new ApproversCategoriesLookup( $db );
		$users = $usersLookup->getApproversWithCategories();

		$this->assertIsArray( $users );
		$this->assertCount( 2, $users );

		$this->assertSame( 'TestUser1', $users[0]['username'] );
		$this->assertSame( [ 'Category1', 'Category2' ], $users[0]['categories'] );

		$this->assertSame( 'TestUser2', $users[1]['username'] );
		$this->assertSame( [], $users[1]['categories'] );
	}

}
