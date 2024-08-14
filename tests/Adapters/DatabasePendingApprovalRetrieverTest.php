<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Tests\Adapters;

use ProfessionalWiki\PageApprovals\Adapters\DatabasePendingApprovalRetriever;
use ProfessionalWiki\PageApprovals\Adapters\InMemoryApproverRepository;
use ProfessionalWiki\PageApprovals\Application\PendingApproval;
use ProfessionalWiki\PageApprovals\Tests\PageApprovalsIntegrationTest;
use WikiPage;

/**
 * @covers \ProfessionalWiki\PageApprovals\Adapters\DatabasePendingApprovalRetriever
 * @group Database
 */
class DatabasePendingApprovalRetrieverTest extends PageApprovalsIntegrationTest {

	private DatabasePendingApprovalRetriever $retriever;
	private InMemoryApproverRepository $approverRepository;

	protected function setUp(): void {
		parent::setUp();

		$this->tablesUsed = [ 'page', 'revision', 'categorylinks', 'approval_log' ];

		$this->approverRepository = new InMemoryApproverRepository();
		$this->retriever = new DatabasePendingApprovalRetriever( $this->db, $this->approverRepository );
	}

	public function testReturnsEmptyArrayWhenThereAreNoApprovers(): void {
		$this->assertSame( [], $this->retriever->getPendingApprovalsForApprover( 1 ) );
	}

	public function testReturnsPendingApprovalsForApprover(): void {
		$approverId = 1;
		$approverCategories = [ 'Category1', 'Category2' ];
		$this->approverRepository->setApproverCategories( $approverId, $approverCategories );

		$page = $this->createPage( false, [ 'Category1' ] );

		$pendingApprovals = $this->retriever->getPendingApprovalsForApprover( $approverId );

		$this->assertCount( 1, $pendingApprovals );
		$this->assertSame( $page->getTitle()->getText(), $pendingApprovals[0]->title->getText() );
		$this->assertSame( [ 'Category1' ], $pendingApprovals[0]->categories );
	}

	public function testExcludesApprovedPages(): void {
		$approverId = 1;
		$this->approverRepository->setApproverCategories( $approverId, [ 'Category1' ] );

		$unapprovedPage = $this->createPage( false, [ 'Category1' ] );
		$this->createPage( true, [ 'Category1' ] );

		$pendingApprovals = $this->retriever->getPendingApprovalsForApprover( $approverId );

		$this->assertCount( 1, $pendingApprovals );
		$this->assertSame( $unapprovedPage->getTitle()->getText(), $pendingApprovals[0]->title->getText() );
	}

	private function createPage( bool $isApproved, array $categories = [] ): WikiPage {
		$page = $this->createPageWithCategories( $categories );

		$this->insertApprovalLogEntry( $page->getId(), $isApproved );

		return $page;
	}

	public function testReturnsLatestApprovalStatus(): void {
		$approverId = 1;
		$this->approverRepository->setApproverCategories( $approverId, [ 'Category1' ] );

		$page = $this->createPage( true, [ 'Category1' ] );
		$this->insertApprovalLogEntry( $page->getId(), false, '30230102000000' );

		$pendingApprovals = $this->retriever->getPendingApprovalsForApprover( $approverId );

		$this->assertCount( 1, $pendingApprovals );
		$this->assertSame( $page->getTitle()->getText(), $pendingApprovals[0]->title->getText() );
	}

	public function testReturnsNoApprovalStatusWhenLatestIsApproved(): void {
		$approverId = 1;
		$this->approverRepository->setApproverCategories( $approverId, [ 'Category1' ] );

		$page = $this->createPage( true, [ 'Category1' ] );
		$this->insertApprovalLogEntry( $page->getId(), false, '20230102000000' );

		$pendingApprovals = $this->retriever->getPendingApprovalsForApprover( $approverId );

		$this->assertCount( 0, $pendingApprovals );
	}

	public function testRespectsLimit(): void {
		$approverId = 1;
		$this->approverRepository->setApproverCategories( $approverId, [ 'Category1' ] );

		$this->createPage( false, [ 'Category1' ] );
		$this->createPage( false, [ 'Category1' ] );
		$this->createPage( false, [ 'Category1' ] );

		$retriever = new DatabasePendingApprovalRetriever( $this->db, $this->approverRepository, 2 );
		$pendingApprovals = $retriever->getPendingApprovalsForApprover( $approverId );

		$this->assertCount( 2, $pendingApprovals );
	}

	public function testExcludesPageWithoutTheCategoriesOfTheApprover(): void {
		$approverId = 1;
		$approverCategories = [ 'Valid1', 'Valid2' ];
		$this->approverRepository->setApproverCategories( $approverId, $approverCategories );

		$expectedPage1 = $this->createPage( false, [ 'Valid1' ] );
		$this->createPage( false, [ 'Invalid' ] );
		$expectedPage2 = $this->createPage( false, [ 'Valid2' ] );
		$expectedPage3 = $this->createPage( false, [ 'Invalid', 'Valid1', 'MoreInvalid' ] );
		$this->createPage( false );

		$pendingApprovals = $this->retriever->getPendingApprovalsForApprover( $approverId );

		$this->assertHasPendingApprovalsForPages(
			[ $expectedPage1, $expectedPage2, $expectedPage3 ],
			$pendingApprovals
		);
	}

	/**
	 * @param WikiPage[] $pages
	 * @param PendingApproval[] $pendingApprovals
	 */
	private function assertHasPendingApprovalsForPages( array $pages, array $pendingApprovals ): void {
		$this->assertEqualsCanonicalizing(
			array_map(
				fn( $page ) => $page->getTitle()->getText(),
				$pages
			),
			array_map(
				fn( $approval ) => $approval->title->getText(),
				$pendingApprovals
			)
		);
	}

	public function testReturnsCategoriesWithSpaces(): void {
		$approverId = 1;
		$approverCategories = [ 'Foo Bar', 'Bar baz' ];
		$this->approverRepository->setApproverCategories( $approverId, $approverCategories );

		$pageCategories = [ 'Foo Bar', 'Bar baz' ];
		$page = $this->createPage( false, $pageCategories );

		$pendingApprovals = $this->retriever->getPendingApprovalsForApprover( $approverId );

		$this->assertCount( 1, $pendingApprovals );
		$this->assertSame( $page->getTitle()->getText(), $pendingApprovals[0]->title->getText() );
		$this->assertEqualsCanonicalizing(
			[ 'Foo Bar', 'Bar baz' ],
			$pendingApprovals[0]->categories
		);
	}

	public function testReturnsNoPendingApprovalsAfterApprovingAnUnapprovedPage(): void {
		$approverId = 1;
		$this->approverRepository->setApproverCategories( $approverId, [ 'Foo' ] );

		$page = $this->createPageWithCategories( [ 'Foo' ] );

		$this->insertApprovalLogEntry( $page->getId(), true, '20240705000000' );

		$pendingApprovals = $this->retriever->getPendingApprovalsForApprover( $approverId );
		$this->assertCount( 0, $pendingApprovals );

		$this->insertApprovalLogEntry( $page->getId(), false, '20240706000001' );

		$pendingApprovals = $this->retriever->getPendingApprovalsForApprover( $approverId );
		$this->assertCount( 1, $pendingApprovals );

		$this->insertApprovalLogEntry( $page->getId(), true, '20240706000002' );

		$pendingApprovals = $this->retriever->getPendingApprovalsForApprover( $approverId );
		$this->assertCount( 0, $pendingApprovals );
	}

}
