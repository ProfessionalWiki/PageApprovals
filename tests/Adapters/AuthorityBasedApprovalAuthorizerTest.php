<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Tests\Adapters;

use MediaWiki\User\User;
use ProfessionalWiki\PageApprovals\Adapters\AuthorityBasedApprovalAuthorizer;
use ProfessionalWiki\PageApprovals\Adapters\InMemoryApproverRepository;
use ProfessionalWiki\PageApprovals\Tests\PageApprovalsIntegrationTest;

/**
 * @covers \ProfessionalWiki\PageApprovals\Adapters\AuthorityBasedApprovalAuthorizer
 * @group Database
 */
class AuthorityBasedApprovalAuthorizerTest extends PageApprovalsIntegrationTest {

	private AuthorityBasedApprovalAuthorizer $approvalAuthorizer;
	private InMemoryApproverRepository $approverRepository;
	private User $user;

	protected function setUp(): void {
		parent::setUp();

		$this->approverRepository = new InMemoryApproverRepository();
		$this->user = $this->getTestUser()->getUser();

		$this->approvalAuthorizer = new AuthorityBasedApprovalAuthorizer(
			$this->user->getId(),
			$this->approverRepository
		);
	}

	public function testCanApproveWhenSomeCategoriesMatch(): void {
		$page = $this->createPageWithCategories( [ 'Category1', 'Category2' ] );
		$this->approverRepository->setApproverCategories( $this->user->getId(), [ 'Category2', 'Category3' ] );

		$this->assertTrue( $this->approvalAuthorizer->canApprove( $page ) );
	}

	public function testCannotApproveWhenNoCategoriesMatch(): void {
		$page = $this->createPageWithCategories( [ 'Category1', 'Category2' ] );
		$this->approverRepository->setApproverCategories( $this->user->getId(), [ 'Category3', 'Category4' ] );

		$this->assertFalse( $this->approvalAuthorizer->canApprove( $page ) );
	}

	public function testCannotApproveWhenApproverHasNoCategories(): void {
		$page = $this->createPageWithCategories( [ 'Category1', 'Category2' ] );
		$this->approverRepository->setApproverCategories( $this->user->getId(), [] );

		$this->assertFalse( $this->approvalAuthorizer->canApprove( $page ) );
	}

	public function testCannotApproveWhenPageHasNoCategories(): void {
		$page = $this->createPageWithCategories( [] );
		$this->approverRepository->setApproverCategories( $this->user->getId(), [ 'Category1', 'Category2' ] );

		$this->assertFalse( $this->approvalAuthorizer->canApprove( $page ) );
	}

	public function testCanApproveWhenCategoryContainsSpaces(): void {
		$page = $this->createPageWithCategories( [ 'Test - Category' ] );
		$this->approverRepository->setApproverCategories( $this->user->getId(), [ 'Test - Category' ] );

		$this->assertTrue( $this->approvalAuthorizer->canApprove( $page ) );
	}

}
