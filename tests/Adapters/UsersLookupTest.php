<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Tests\Adapters;

use MediaWikiIntegrationTestCase;
use ProfessionalWiki\PageApprovals\Adapters\UsersLookup;
use Wikimedia\Rdbms\LoadBalancer;

/**
 * @covers \ProfessionalWiki\PageApprovals\Adapters\UsersLookup
 * @group Database
 */
class UsersLookupTest extends MediaWikiIntegrationTestCase {

	protected function setUp(): void {
		parent::setUp();
		$this->tablesUsed[] = 'user';

		$userFactory = $this->getServiceContainer()->getUserFactory();

		$user1 = $userFactory->newFromName( 'TestUser1' );
		$user1->addToDatabase();
		$user2 = $userFactory->newFromName( 'TestUser2' );
		$user2->addToDatabase();
	}

	public function testGetAllUsers() {
		$db = $this->getServiceContainer()->getDBLoadBalancer()->getConnection( LoadBalancer::DB_REPLICA );
		$usersLookup = new UsersLookup( $db );
		$users = $usersLookup->getAllUsers();

		$this->assertIsArray( $users );
		$this->assertNotEmpty( $users, "Users array is empty" );
	}

}
