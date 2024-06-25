<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Tests\Adapters;

use MediaWikiIntegrationTestCase;
use ProfessionalWiki\PageApprovals\Adapters\DatabasePendingApprovalRetriever;
use ProfessionalWiki\PageApprovals\Adapters\InMemoryApproverRepository;
use ProfessionalWiki\PageApprovals\Application\PendingApproval;
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

	public function testGetPendingApprovalsForApproverWithNoCategories(): void {
		$this->assertSame( [], $this->retriever->getPendingApprovalsForApprover( 1 ) );
	}

	public function testGetPendingApprovalsForApprover(): void {
		$approverId = 1;
		$approverCategories = [ 'Category1', 'Category2' ];
		$this->approverRepository->setApproverCategories( $approverId, $approverCategories );

		$title = $this->createUniqueTitle();
		$page = $this->getServiceContainer()->getWikiPageFactory()->newFromTitle( $title );
		$this->editPage( $page, 'Test content' );
		$this->addCategory( $page, 'Category1' );

		$pageId = $page->getId();
		$this->insertApprovalLogEntry( $pageId, false );

		$pendingApprovals = $this->retriever->getPendingApprovalsForApprover( $approverId );

		$this->assertCount( 1, $pendingApprovals );
		$this->assertInstanceOf( PendingApproval::class, $pendingApprovals[0] );
		$this->assertEquals( $title->getText(), $pendingApprovals[0]->title->getText() );
		$this->assertContains( 'Category1', $pendingApprovals[0]->categories );
	}

	public function testGetPendingApprovalsExcludesApprovedPages(): void {
		$approverId = 1;
		$approverCategories = [ 'Category1' ];
		$this->approverRepository->setApproverCategories( $approverId, $approverCategories );

		$title1 = $this->createUniqueTitle();
		$page1 = $this->getServiceContainer()->getWikiPageFactory()->newFromTitle( $title1 );
		$this->editPage( $page1, 'Test content 1' );
		$this->addCategory( $page1, 'Category1' );

		$title2 = $this->createUniqueTitle();
		$page2 = $this->getServiceContainer()->getWikiPageFactory()->newFromTitle( $title2 );
		$this->editPage( $page2, 'Test content 2' );
		$this->addCategory( $page2, 'Category1' );

		$this->insertApprovalLogEntry( $page1->getId(), false );
		$this->insertApprovalLogEntry( $page2->getId(), true );

		$pendingApprovals = $this->retriever->getPendingApprovalsForApprover( $approverId );

		$this->assertCount( 1, $pendingApprovals );
		$this->assertEquals( $title1->getText(), $pendingApprovals[0]->title->getText() );
	}

	public function testGetPendingApprovalsReturnsLatestApprovalStatus(): void {
		$approverId = 1;
		$approverCategories = [ 'Category1' ];
		$this->approverRepository->setApproverCategories( $approverId, $approverCategories );

		$title = $this->createUniqueTitle();
		$page = $this->getServiceContainer()->getWikiPageFactory()->newFromTitle( $title );
		$this->editPage( $page, 'Test content' );
		$this->addCategory( $page, 'Category1' );

		$pageId = $page->getId();
		$this->insertApprovalLogEntry( $pageId, true, '20230101000000' );
		$this->insertApprovalLogEntry( $pageId, false, '20230102000000' );

		$pendingApprovals = $this->retriever->getPendingApprovalsForApprover( $approverId );

		$this->assertCount( 1, $pendingApprovals );
		$this->assertEquals( $title->getText(), $pendingApprovals[0]->title->getText() );
	}

	public function testGetPendingApprovalsRespectsLimit(): void {
		$approverId = 1;
		$approverCategories = [ 'Category1' ];
		$this->approverRepository->setApproverCategories( $approverId, $approverCategories );

		for ( $i = 1; $i <= 3; $i++ ) {
			$title = $this->createUniqueTitle();
			$page = $this->getServiceContainer()->getWikiPageFactory()->newFromTitle( $title );
			$this->editPage( $page, "Test content $i" );
			$this->addCategory( $page, 'Category1' );
			$this->insertApprovalLogEntry( $page->getId(), false );
		}

		$retriever = new DatabasePendingApprovalRetriever( $this->db, $this->approverRepository, 2 );
		$pendingApprovals = $retriever->getPendingApprovalsForApprover( $approverId );

		$this->assertCount( 2, $pendingApprovals );
	}

	private function addCategory( WikiPage $page, string $category ): void {
		$content = $page->getContent();
		$text = $content->getText();
		$newText = $text . "\n[[Category:$category]]";
		$this->editPage( $page, $newText );
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
}
