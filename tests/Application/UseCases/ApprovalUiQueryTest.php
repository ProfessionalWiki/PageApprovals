<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Tests\Application\UseCases;

use ProfessionalWiki\PageApprovals\Adapters\InMemoryApprovalLog;
use ProfessionalWiki\PageApprovals\Adapters\InMemoryApproverRepository;
use ProfessionalWiki\PageApprovals\Application\UseCases\ApprovalUiQuery\ApprovalUiQuery;
use ProfessionalWiki\PageApprovals\Tests\PageApprovalsIntegrationTest;
use ProfessionalWiki\PageApprovals\Tests\TestDoubles\SucceedingApprovalAuthorizer;
use RequestContext;

/**
 * @covers \ProfessionalWiki\PageApprovals\Application\UseCases\ApprovalUiQuery\ApprovalUiQuery
 * @group Database
 */
class ApprovalUiQueryTest extends PageApprovalsIntegrationTest {

	protected function setUp(): void {
		parent::setUp();

		$this->tablesUsed = [ 'page', 'revision', 'categorylinks' ];
	}

	public function testPageWithNoApprovals(): void {
		$page = $this->createPageWithCategories( [ 'TestCat' ] );

		$outputPage = RequestContext::newExtraneousContext( $page->getTitle() )->getOutput();
		$outputPage->addParserOutput( $page->getParserOutput() );
		//$outputPage->setArticleFlag( true );

		$uiArguments = $this->newApprovalUiQuery()->getUiState( $outputPage );

		$this->assertFalse( $uiArguments->pageIsApproved );
		$this->assertSame( 0, $uiArguments->approvalTimestamp );
		$this->assertNull( $uiArguments->approverId );
	}

	private function newApprovalUiQuery(): ApprovalUiQuery {
		return new ApprovalUiQuery(
			new InMemoryApprovalLog(),
			new SucceedingApprovalAuthorizer(),
			new InMemoryApproverRepository()
		);
	}

	// TODO: moar tests

}
