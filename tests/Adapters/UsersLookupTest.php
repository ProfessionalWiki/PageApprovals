<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Tests\Adapters;

use MediaWikiIntegrationTestCase;
use ProfessionalWiki\PageApprovals\Adapters\UsersLookup;
use Wikimedia\Rdbms\LoadBalancer;

class UsersLookupTest extends MediaWikiIntegrationTestCase {

	protected function setUp(): void {
		parent::setUp();
		$this->tablesUsed[] = 'user';
	}

	public function testGetAllUsers() {
		$db = $this->getServiceContainer()->getDBLoadBalancer()->getConnection( LoadBalancer::DB_REPLICA );
		$usersLookup = new UsersLookup( $db );
		$users = $usersLookup->getAllUsers();

		$this->assertIsArray( $users );
		$this->assertGreaterThanOrEqual( 1, count( $users ), "Users array does not contain 1 or more users" );
	}

}
