<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Tests\Adapters;

use MediaWikiIntegrationTestCase;
use ProfessionalWiki\PageApprovals\Adapters\DatabasePendingApprovalRetriever;
use ProfessionalWiki\PageApprovals\Adapters\InMemoryApproverRepository;
use Title;
use WikiPage;

/**
 * @covers \ProfessionalWiki\PageApprovals\Adapters\DatabasePendingApprovalRetriever
 * @group Database
 */
class DatabasePendingApprovalRetrieverTest extends MediaWikiIntegrationTestCase {

	private DatabasePendingApprovalRetriever $retriever;
	private InMemoryApproverRepository $approverRepository;
	private int $pageCounter = 0;

	protected function setUp(): void {
		parent::setUp();

		$this->tablesUsed = [ 'page', 'revision', 'categorylinks', 'approval_log' ];

		$this->approverRepository = new InMemoryApproverRepository();
		$this->retriever = new DatabasePendingApprovalRetriever( $this->db, $this->approverRepository );
	}

	private function createUniqueTitle(): Title {
		$this->pageCounter++;
		return Title::newFromText( 'TestPage' . $this->pageCounter );
	}

	public function testReturnsNullWhenThereAreNoApprovers(): void {
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

		$unnaprovedPage = $this->createPage( false, [ 'Category1' ] );
		$this->createPage( true, [ 'Category1' ] );

		$pendingApprovals = $this->retriever->getPendingApprovalsForApprover( $approverId );

		$this->assertCount( 1, $pendingApprovals );
		$this->assertSame( $unnaprovedPage->getTitle()->getText(), $pendingApprovals[0]->title->getText() );
	}

	private function createPage( bool $isApproved, array $categories = [] ): WikiPage {
		$page = $this->getServiceContainer()->getWikiPageFactory()->newFromTitle( $this->createUniqueTitle() );

		$this->editPage( $page, $page->getTitle()->getText() . $this->buildCategoryWikitext( $categories ) );

		$this->insertApprovalLogEntry( $page->getId(), $isApproved );

		return $page;
	}

	private function buildCategoryWikitext( array $categories ): string {
		return implode(
			"\n",
			array_map(
				fn( $category ) => "[[Category:$category]]",
				$categories
			)
		);
	}

	private function insertApprovalLogEntry( int $pageId, bool $isApproved, string $timestamp = null ): void {
		$this->db->insert(
			'approval_log',
			[
				'al_page_id' => $pageId,
				'al_timestamp' => $timestamp ?? $this->db->timestamp(),
				'al_is_approved' => $isApproved ? 1 : 0,
				'al_user_id' => 1
			]
		);
	}

	public function testReturnsLatestApprovalStatus(): void {
		$approverId = 1;
		$this->approverRepository->setApproverCategories( $approverId, [ 'Category1' ] );

		$page = $this->createPage( true, [ 'Category1' ] );
		$this->insertApprovalLogEntry( $page->getId(), false, '20230102000000' );

		$pendingApprovals = $this->retriever->getPendingApprovalsForApprover( $approverId );

		$this->assertCount( 1, $pendingApprovals );
		$this->assertSame( $page->getTitle()->getText(), $pendingApprovals[0]->title->getText() );
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

	public function testExcludesPageWithoutTheApproverTheirCategories(): void {
		$approverId = 1;
		$approverCategories = [ 'Valid1', 'Valid2' ];
		$this->approverRepository->setApproverCategories( $approverId, $approverCategories );

		$expectedPage1 = $this->createPage( false, [ 'Valid1' ] );
		$this->createPage( false, [ 'Invalid' ] );
		$expectedPage2 = $this->createPage( false, [ 'Valid2' ] );
		$expectedPage3 = $this->createPage( false, [ 'Invalid', 'Valid1', 'MoreInvalid' ] );
		$this->createPage( false );

		$pendingApprovals = $this->retriever->getPendingApprovalsForApprover( $approverId );

		$this->assertSame(
			[
				$expectedPage1->getTitle()->getText(),
				$expectedPage2->getTitle()->getText(),
				$expectedPage3->getTitle()->getText()
			],
			array_map(
				fn( $approval ) => $approval->title->getText(),
				$pendingApprovals
			)
		);
	}

}
