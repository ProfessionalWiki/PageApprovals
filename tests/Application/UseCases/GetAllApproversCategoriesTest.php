<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Tests\Application\UseCases;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\PageApprovals\Application\UseCases\GetAllApproversCategories;
use ProfessionalWiki\PageApprovals\Adapters\UsersLookup;
use ProfessionalWiki\PageApprovals\Adapters\DatabaseApproverRepository;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\MediaWikiServices;
use User;

/**
 * @covers \ProfessionalWiki\PageApprovals\Application\UseCases\GetAllApproversCategories
 * @group Database
 */
class GetAllApproversCategoriesTest extends TestCase {

	private $usersLookup;
	private $dbApproverRepo;
	private $permManager;
	private $originalMediaWikiServices;

	protected function setUp(): void {
		$this->usersLookup = $this->createMock( UsersLookup::class );
		$this->dbApproverRepo = $this->createMock( DatabaseApproverRepository::class );
		$this->permManager = $this->createMock( PermissionManager::class );
		$mediaWikiServices = $this->createMock( MediaWikiServices::class );

		$mediaWikiServices->method( 'getPermissionManager' )->willReturn( $this->permManager );

		$this->originalMediaWikiServices = MediaWikiServices::getInstance();
		MediaWikiServices::forceGlobalInstance( $mediaWikiServices );
	}

	protected function tearDown(): void {
		MediaWikiServices::forceGlobalInstance( $this->originalMediaWikiServices );
	}

	public function testWithRights(): void {
		$user = $this->createConfiguredMock( User::class, [ 'getId' => 1, 'getName' => 'User1' ] );

		$this->usersLookup->method( 'getAllUsers' )->willReturn( [ $user ] );
		$this->permManager->method( 'userHasRight' )->willReturn( true );
		$this->dbApproverRepo->method( 'getApproverCategories' )->willReturn( [ 'Category1', 'Category2' ] );

		$useCase = new GetAllApproversCategories( $this->usersLookup, $this->dbApproverRepo );
		$result = $useCase->getAllApproversCategories();

		$this->assertSame( [
			'username' => 'User1',
			'categories' => [ 'Category1', 'Category2' ]
		], $result[0] );
	}

	public function testNoRights(): void {
		$this->permManager->method( 'userHasRight' )->willReturn( false );

		$useCase = new GetAllApproversCategories( $this->usersLookup, $this->dbApproverRepo );
		$result = $useCase->getAllApproversCategories();

		$this->assertSame( [], $result );
	}

}
