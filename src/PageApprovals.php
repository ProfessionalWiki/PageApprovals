<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals;

use MediaWiki\MediaWikiServices;
use ProfessionalWiki\PageApprovals\Adapters\DatabaseApprovalLog;
use ProfessionalWiki\PageApprovals\Adapters\DatabaseHtmlRepository;
use ProfessionalWiki\PageApprovals\Adapters\PageHtmlRetriever;
use ProfessionalWiki\PageApprovals\Application\ApprovalAuthorizer;
use ProfessionalWiki\PageApprovals\Application\ApprovalLog;
use ProfessionalWiki\PageApprovals\Application\HtmlRepository;
use ProfessionalWiki\PageApprovals\Application\UseCases\EvaluateApprovalState;
use ProfessionalWiki\PageApprovals\EntryPoints\REST\ApprovePageApi;
use ProfessionalWiki\PageApprovals\EntryPoints\REST\UnapprovePageApi;
use ProfessionalWiki\PageApprovals\Adapters\AuthorityBasedApprovalAuthorizer;
use RequestContext;
use Wikimedia\Rdbms\IDatabase;

class PageApprovals {

	public static function getInstance(): self {
		/** @var ?PageApprovals $instance */
		static $instance = null;
		$instance ??= new self();
		return $instance;
	}

	public static function newApprovePageApi(): ApprovePageApi {
		return new ApprovePageApi(
			self::getInstance()->newPageApprovalAuthorizer(),
			self::getInstance()->newApprovalLog(),
			self::getInstance()->newHtmlRepository(),
			self::getInstance()->newPageHtmlRetriever()
		);
	}

	public static function newUnapprovePageApi(): UnapprovePageApi {
		return new UnapprovePageApi(
			self::getInstance()->newPageApprovalAuthorizer(),
			self::getInstance()->newApprovalLog()
		);
	}

	private function newPageApprovalAuthorizer(): ApprovalAuthorizer {
		return new AuthorityBasedApprovalAuthorizer(
			RequestContext::getMain()->getUser()
		);
	}

	public function newApprovalLog(): ApprovalLog {
		return new DatabaseApprovalLog(
			$this->getDatabase()
		);
	}

	private function getDatabase(): IDatabase {
		return MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection( DB_PRIMARY );
	}

	public function newEvaluateApprovalStateAction(): EvaluateApprovalState {
		return new EvaluateApprovalState(
			htmlRepository: $this->newHtmlRepository(),
			approvalLog: $this->newApprovalLog()
		);
	}

	public function newHtmlRepository(): HtmlRepository {
		return new DatabaseHtmlRepository(
			$this->getDatabase()
		);
	}

	public function newPageHtmlRetriever(): PageHtmlRetriever {
		return new PageHtmlRetriever(
			MediaWikiServices::getInstance()->getWikiPageFactory()
		);
	}

}
