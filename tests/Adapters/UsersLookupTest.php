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
		$users = [ 45 ];

		$this->assertIsArray( $users );
		$this->assertGreaterThanOrEqual( 1, count( $users ), "Users array does not contain 1 or more users" );
	}

}
