<?php

namespace ProfessionalWiki\PageApprovals\Tests\Integration;

use FauxRequest;
use PermissionsError;
use ProfessionalWiki\PageApprovals\Application\ApproverRepository;
use ProfessionalWiki\PageApprovals\EntryPoints\Specials\SpecialManageApprovers;
use ProfessionalWiki\PageApprovals\PageApprovals;
use SpecialPageTestBase;
use User;

/**
 * @group Database
 * @covers \ProfessionalWiki\PageApprovals\EntryPoints\Specials\SpecialManageApprovers
 */
class SpecialManageApproversTest extends SpecialPageTestBase {

	private ApproverRepository $approverRepository;

	protected function setUp(): void {
		parent::setUp();
		$this->approverRepository = PageApprovals::getInstance()->getApproverRepository();
	}

	protected function newSpecialPage(): SpecialManageApprovers {
		return new SpecialManageApprovers(
			$this->approverRepository,
			$this->getServiceContainer()->getUserGroupManager(),
			$this->getServiceContainer()->getUserFactory()
		);
	}

	public function testNonAdminCannotAccessPage() {
		$this->expectException( PermissionsError::class );
		$this->viewPage( user: $this->getTestUser()->getUser() );
	}

	private function viewPage( User $user = null ): string {
		[ $output ] = $this->executeSpecialPage(
			'',
			null,
			'qqx',
			$user ?? $this->getTestSysop()->getUser()
		);
		return $output;
	}

	public function testAdminCanAccessPage(): void {
		$this->assertStringContainsString(
			'<table',
			$this->viewPage( user: $this->getTestSysop()->getUser() ),
			'Expected HTML output with table'
		);
	}

	public function testAddApproverAction(): void {
		$username = self::getTestUser()->getUser()->getName();

		$this->post(
			request: [
				'action' => 'add-approver',
				'username' => $username
			]
		);

		$output = $this->viewPage();

		$this->assertStringContainsString(
			$username,
			$output,
			'Expected HTML output to contain the new approver username'
		);
		$this->assertStringNotContainsString(
			'<div class="category-entry">',
			$output,
			'New approver should have no categories'
		);
	}

	private function post( array $request ): void {
		$this->executeSpecialPage(
			'',
			new FauxRequest( $request, true ),
			'qqx',
			$this->getTestSysop()->getUser()
		);
	}

	public function testAddAndDeleteCategoryAction(): void {
		$username = self::getTestUser()->getUser()->getName();

		$this->post(
			request: [
				'action' => 'add',
				'username' => $username,
				'category' => 'TestCategory'
			]
		);

		$this->assertStringContainsString( 'TestCategory', $this->viewPage(), 'Category should be added' );

		$this->post(
			request: [
				'action' => 'delete',
				'username' => $username,
				'category' => 'TestCategory'
			]
		);

		$this->assertStringNotContainsString( 'TestCategory', $this->viewPage(), 'Category should be deleted' );
	}

	public function testCanAddAnotherCategoryWhenMultipleUsersHaveMultipleCategories(): void {
		$user1 = self::getTestUser( [ 'Group1' ] )->getUser();
		$user2 = self::getTestUser( [ 'Group2' ] )->getUser();
		$user3 = self::getTestUser( [ 'Group3' ] )->getUser();

		$this->approverRepository->setApproverCategories(
			$user1->getId(),
			[ 'TestCategory1', 'TestCategory2' ]
		);
		$this->approverRepository->setApproverCategories(
			$user2->getId(),
			[ 'TestCategory2', 'TestCategory3' ]
		);
		$this->approverRepository->setApproverCategories(
			$user3->getId(),
			[ 'TestCategory3' ]
		);

		$this->post(
			request: [
				'action' => 'add',
				'username' => $user3->getName(),
				'category' => 'TestCategory4'
			]
		);

		$this->assertSame(
			[ 'TestCategory1', 'TestCategory2' ],
			$this->approverRepository->getApproverCategories( $user1->getId() )
		);

		$this->assertSame(
			[ 'TestCategory2', 'TestCategory3' ],
			$this->approverRepository->getApproverCategories( $user2->getId() )
		);

		$this->assertSame(
			[ 'TestCategory3', 'TestCategory4' ],
			$this->approverRepository->getApproverCategories( $user3->getId() )
		);
	}

		public function testAddCategoryWithSpaces(): void {
		$user = self::getTestUser()->getUser();

		$this->post(
			request: [
				'action' => 'add',
				'username' => $user->getName(),
				'category' => 'Test - Category'
			]
		);

		$this->assertSame(
			[ 'Test_-_Category' ],
			$this->approverRepository->getApproverCategories( $user->getId() )
		);
	}

	public function testRemoveCategoryWithSpaces(): void {
		$user = self::getTestUser()->getUser();

		$this->approverRepository->setApproverCategories(
			$user->getId(),
			[ 'Test - Category' ]
		);

		$this->post(
			request: [
				'action' => 'delete',
				'username' => $user->getName(),
				'category' => 'Test - Category'
			]
		);

		$this->assertSame(
			[],
			$this->approverRepository->getApproverCategories( $user->getId() )
		);
	}

}
