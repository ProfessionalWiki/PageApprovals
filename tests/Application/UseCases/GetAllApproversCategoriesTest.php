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
	private $approverRep;
	private $permManager;

	protected function setUp(): void {
		$this->usersLookup = $this->createMock( UsersLookup::class );
		$this->approverRep = $this->createMock( DatabaseApproverRepository::class );
		$this->permManager = $this->createMock( PermissionManager::class );
		$mediaWikiServices = $this->createMock( MediaWikiServices::class );

		$mediaWikiServices->method( 'getPermissionManager' )->willReturn( $this->permManager );
		MediaWikiServices::forceGlobalInstance( $mediaWikiServices );
	}

	public function testWithRights(): void {
		$user = $this->createConfiguredMock( User::class, [ 'getId' => 1, 'getName' => 'User1' ] );

		$this->usersLookup->method( 'getAllUsers' )->willReturn( [ $user ] );
		$this->permManager->method( 'userHasRight' )->willReturn( true );
		$this->approverRep->method( 'getApproverCategories' )->willReturn( [ 'Category1', 'Category2' ] );

		$useCase = new GetAllApproversCategories( $this->usersLookup, $this->approverRep );
		$result = $useCase->getAllApproversCategories();

		$this->assertSame( [
			'userId' => 1,
			'username' => 'User1',
			'categories' => [ 'Category1', 'Category2' ]
		], $result[0] );
	}

	public function testNoRights(): void {
		$this->permManager->method( 'userHasRight' )->willReturn( false );

		$useCase = new GetAllApproversCategories( $this->usersLookup, $this->approverRep );
		$result = $useCase->getAllApproversCategories();

		$this->assertSame( [], $result );
	}

}
