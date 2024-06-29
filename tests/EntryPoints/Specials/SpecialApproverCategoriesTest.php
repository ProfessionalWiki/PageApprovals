<?php

namespace ProfessionalWiki\PageApprovals\Tests\Integration;

use FauxRequest;
use PermissionsError;
use ProfessionalWiki\PageApprovals\EntryPoints\Specials\SpecialApproverCategories;
use SpecialPageTestBase;
use User;

/**
 * @group Database
 * @covers \ProfessionalWiki\PageApprovals\EntryPoints\Specials\SpecialApproverCategories
 */
class SpecialApproverCategoriesTest extends SpecialPageTestBase {

	protected function setUp(): void {
		parent::setUp();

		$userFactory = $this->getServiceContainer()->getUserFactory();
		$userFactory->newFromName( 'TestUser1' )->addToDatabase();
		$userFactory->newFromName( 'TestUser2' )->addToDatabase();
	}

	protected function newSpecialPage(): SpecialApproverCategories {
		return new SpecialApproverCategories();
	}

	public function testNonAdminCannotAccessPage() {
		$this->expectException( PermissionsError::class );
		$this->getPageOutput( user: $this->getTestUser()->getUser() );
	}

	private function getPageOutput( User $user = null, array $request = [] ): string {
		[ $output ] = $this->executeSpecialPage(
			'',
			$request === [] ? null : $this->newPostRequest( $request ),
			'qqx',
			$user ?? $this->getTestSysop()->getUser()
		);
		return $output;
	}

	private function newPostRequest( array $parameters ): FauxRequest {
		return new FauxRequest( $parameters, true );
	}

	public function testAdminCanAccessPage(): void {
		$output = $this->getPageOutput( user: $this->getTestSysop()->getUser() );
		$this->assertStringContainsString( '<table', $output, 'Expected HTML output with table' );
	}

	public function testAddApproverAction(): void {
		$usernameToAdd = 'TestUser1';

		$this->getPageOutput(
			request: [
				'action' => 'add-approver',
				'username' => $usernameToAdd
			]
		);

		$output = $this->getPageOutput();

		$this->assertStringContainsString(
			$usernameToAdd,
			$output,
			'Expected HTML output to contain the new approver username'
		);
		$this->assertStringNotContainsString(
			'<div class="category-entry">',
			$output,
			'New approver should have no categories'
		);
	}

	public function testAddDeleteCategoryAction(): void {
		$this->getPageOutput(
			request: [
				'action' => 'add',
				'username' => 'TestUser2',
				'category' => 'TestCategory'
			]
		);

		$this->getPageOutput(
			request: [
				'action' => 'delete',
				'username' => 'TestUser2',
				'category' => 'TestCategory'
			]
		);

		$this->assertStringNotContainsString( 'TestCategory', $this->getPageOutput(), 'Category should be deleted' );
	}

}
