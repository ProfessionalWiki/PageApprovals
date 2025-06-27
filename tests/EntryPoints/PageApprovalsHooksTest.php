<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Tests\Integration\HookHandler;

use MediaWiki\Context\RequestContext;
use MediaWiki\Output\OutputPage;
use MediaWiki\Parser\ParserOutput;
use ProfessionalWiki\PageApprovals\EntryPoints\PageApprovalsHooks;
use ProfessionalWiki\PageApprovals\Tests\PageApprovalsIntegrationTest;
use ProfessionalWiki\PageApprovals\PageApprovals;

/**
 * @group Database
 * @covers \ProfessionalWiki\PageApprovals\EntryPoints\PageApprovalsHooks
 */
class PageApprovalsHooksTest extends PageApprovalsIntegrationTest {

	private OutputPage $out;

	protected function setUp(): void {
		parent::setUp();
		$this->tablesUsed[] = 'approver_config';

		$page = $this->createPageWithText( 'Test page content' );

		$this->out = new OutputPage( new RequestContext() );
		$this->out->setTitle( $page->getTitle() );
		$this->out->setArticleFlag( true );
		$this->out->setRevisionId( $page->getLatest() );
	}

	public function testOnOutputPageBeforeHTMLUiIsNotShown() {
		PageApprovalsHooks::onOutputPageBeforeHTML( $this->out );

		$this->assertStringNotContainsString(
			'page-approval-container',
			$this->out->getHTML(),
			'The page approval status should not be displayed without a matching category.'
		);
	}

	public function testOnOutputPageBeforeHTMLUiIsShown() {
		$parserOutput = new ParserOutput( 'Test page content' );
		$parserOutput->addCategory( 'ApprovalCategory', 'ApprovalCategory' );
		$this->out->addParserOutput( $parserOutput );

		$approverRepository = PageApprovals::getInstance()->getApproverRepository();
		$approverRepository->setApproverCategories( 1, [ 'ApprovalCategory' ] );

		PageApprovalsHooks::onOutputPageBeforeHTML( $this->out );

		$this->assertArrayHasKey(
			'ext-pageapprovals',
			$this->out->getIndicators(),
			'The page approval status should be displayed with a matching category.'
		);
	}

}
