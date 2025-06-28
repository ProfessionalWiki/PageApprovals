<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Tests\Application\UseCases;

use MediaWiki\Context\RequestContext;
use MediaWiki\Output\OutputPage;
use ProfessionalWiki\PageApprovals\Adapters\InMemoryApprovalLog;
use ProfessionalWiki\PageApprovals\Adapters\InMemoryApproverRepository;
use ProfessionalWiki\PageApprovals\Application\ApproverRepository;
use ProfessionalWiki\PageApprovals\Application\UseCases\ApprovalUiQuery\ApprovalUiQuery;
use ProfessionalWiki\PageApprovals\Tests\PageApprovalsIntegrationTest;
use ProfessionalWiki\PageApprovals\Tests\TestDoubles\SucceedingApprovalAuthorizer;

/**
 * @covers \ProfessionalWiki\PageApprovals\Application\UseCases\ApprovalUiQuery\ApprovalUiQuery
 * @group Database
 */
class ApprovalUiQueryTest extends PageApprovalsIntegrationTest {

	private const APPROVER_CATEGORY = 'TestCat';
	private const CATEGORY_WITH_SPACES = 'Test - Category';

	protected function setUp(): void {
		parent::setUp();

		$this->tablesUsed = [ 'page', 'revision', 'categorylinks' ];
	}

	public function testPageWithNoApprovalsIsNotApproved(): void {
		$uiArguments = $this->newApprovalUiQuery()->getUiState( $this->createArticle() );

		$this->assertFalse( $uiArguments->pageIsApproved );
		$this->assertSame( 0, $uiArguments->approvalTimestamp );
		$this->assertNull( $uiArguments->approverId );
		$this->assertNull( $uiArguments->approverUserName );
	}

	private function createArticle( $categories = [ self::APPROVER_CATEGORY ] ): OutputPage {
		$page = $this->createPageWithCategories( $categories );

		$outputPage = RequestContext::newExtraneousContext( $page->getTitle() )->getOutput();
		$outputPage->addParserOutput( $page->getParserOutput() );
		$outputPage->setArticleFlag( true );
		$outputPage->setRevisionId( $page->getLatest() );

		return $outputPage;
	}

	private function newApprovalUiQuery(
		?InMemoryApprovalLog $approvalLog = null,
		?ApproverRepository $repository = null
	): ApprovalUiQuery {
		return new ApprovalUiQuery(
			$approvalLog ?? new InMemoryApprovalLog(),
			new SucceedingApprovalAuthorizer(),
			$repository ?? $this->newApproverRepo()
		);
	}

	private function newApproverRepo(): InMemoryApproverRepository {
		$approverRepo = new InMemoryApproverRepository();
		$approverRepo->setApproverCategories( 0, [ self::APPROVER_CATEGORY ] );
		return $approverRepo;
	}

	public function testApprovablePageCanBeApproved(): void {
		$uiArguments = $this->newApprovalUiQuery()->getUiState( $this->createArticle() );

		$this->assertTrue( $uiArguments->showUi );
		$this->assertTrue( $uiArguments->userIsApprover );
	}

	public function testOnlyArticlesAreApprovable(): void {
		$page = $this->createArticle();
		$page->setArticleFlag( false );

		$uiArguments = $this->newApprovalUiQuery()->getUiState( $page );

		$this->assertFalse( $uiArguments->showUi );
		$this->assertFalse( $uiArguments->userIsApprover );
	}

	public function testOnlyTheLatestRevisionIsApprovable(): void {
		$page = $this->createArticle();
		$page->setRevisionId( $page->getRevisionId() - 1 );

		$uiArguments = $this->newApprovalUiQuery()->getUiState( $page );

		$this->assertFalse( $uiArguments->showUi );
		$this->assertFalse( $uiArguments->userIsApprover );
	}

	public function testOnlyExistingPagesAreApprovable(): void {
		$page = $this->createArticle();
		$page->setRevisionId( null );

		$uiArguments = $this->newApprovalUiQuery()->getUiState( $page );

		$this->assertFalse( $uiArguments->showUi );
		$this->assertFalse( $uiArguments->userIsApprover );
	}

	public function testApprovalStateIsReturned(): void {
		$page = $this->createArticle();

		$approvalLog = new InMemoryApprovalLog();
		$approvalLog->approvePage( 1000001, 0 );
		$approvalLog->approvePage( $page->getWikiPage()->getId(), 0 ); // Timestamp 1
		$approvalLog->approvePage( 1000002, 0 );

		$uiArguments = $this->newApprovalUiQuery( $approvalLog )->getUiState( $page );

		$this->assertTrue( $uiArguments->pageIsApproved );
		$this->assertSame( 0, $uiArguments->approverId );
		$this->assertSame( 1, $uiArguments->approvalTimestamp );
		$this->assertSame( '', $uiArguments->approverUserName );
	}

	public function testPageIsApprovableWhenCategoryContainsSpaces(): void {
		$repository = new InMemoryApproverRepository();
		$repository->setApproverCategories( 0, [ self::CATEGORY_WITH_SPACES ] );

		$page = $this->createArticle( [ self::CATEGORY_WITH_SPACES ] );

		$uiArguments = $this->newApprovalUiQuery( repository: $repository )->getUiState( $page );

		$this->assertTrue( $uiArguments->showUi );
		$this->assertTrue( $uiArguments->userIsApprover );
	}

}
