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

		$userFactory->newFromName( 'TestUser1' )->addToDatabase();
		$userFactory->newFromName( 'TestUser2' )->addToDatabase();
	}

	public function testGetAllUsers() {
		$db = $this->getServiceContainer()->getDBLoadBalancer()->getConnection( LoadBalancer::DB_REPLICA );
		$usersLookup = new UsersLookup( $db );
		$users = $usersLookup->getAllUsers();

		$this->assertIsArray( $users );
		$this->assertGreaterThanOrEqual( 2, count( $users ), "Users array does not contain 2 or more users" );

		$usernames = array_map( static function ( $user ) {
			return $user->getName();
		}, $users );

		$this->assertContains( 'TestUser1', $usernames, "Username 'TestUser1' not found" );
		$this->assertContains( 'TestUser2', $usernames, "Username 'TestUser2' not found" );
	}

}
