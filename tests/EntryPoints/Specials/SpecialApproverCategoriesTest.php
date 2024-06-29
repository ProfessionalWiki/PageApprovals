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

	protected function newSpecialPage(): SpecialApproverCategories {
		return new SpecialApproverCategories();
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

	public function testAddDeleteCategoryAction(): void {
		$username = self::getTestUser()->getUser()->getName();

		$this->post(
			request: [
				'action' => 'add',
				'username' => $username,
				'category' => 'TestCategory'
			]
		);

		$this->post(
			request: [
				'action' => 'delete',
				'username' => $username,
				'category' => 'TestCategory'
			]
		);

		$this->assertStringNotContainsString( 'TestCategory', $this->viewPage(), 'Category should be deleted' );
	}

}
