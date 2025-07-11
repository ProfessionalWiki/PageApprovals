<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals;

use MediaWiki\Context\RequestContext;
use MediaWiki\Html\TemplateParser;
use MediaWiki\MediaWikiServices;
use ProfessionalWiki\PageApprovals\Adapters\DatabaseApprovalLog;
use ProfessionalWiki\PageApprovals\Adapters\DatabaseApproverRepository;
use ProfessionalWiki\PageApprovals\Adapters\DatabaseHtmlRepository;
use ProfessionalWiki\PageApprovals\Adapters\DatabasePendingApprovalRetriever;
use ProfessionalWiki\PageApprovals\Adapters\PageHtmlRetriever;
use ProfessionalWiki\PageApprovals\Application\ApprovalAuthorizer;
use ProfessionalWiki\PageApprovals\Application\ApprovalLog;
use ProfessionalWiki\PageApprovals\Application\ApproverRepository;
use ProfessionalWiki\PageApprovals\Application\HtmlRepository;
use ProfessionalWiki\PageApprovals\Application\PendingApprovalRetriever;
use ProfessionalWiki\PageApprovals\Application\UseCases\ApprovalUiQuery\ApprovalUiQuery;
use ProfessionalWiki\PageApprovals\Application\UseCases\EvaluateApprovalState;
use ProfessionalWiki\PageApprovals\EntryPoints\REST\ApprovePageApi;
use ProfessionalWiki\PageApprovals\EntryPoints\REST\UnapprovePageApi;
use ProfessionalWiki\PageApprovals\Adapters\AuthorityBasedApprovalAuthorizer;
use ProfessionalWiki\PageApprovals\EntryPoints\Specials\SpecialManageApprovers;
use ProfessionalWiki\PageApprovals\EntryPoints\Specials\SpecialPendingApprovals;
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
			self::getInstance()->getApprovalLog(),
			self::getInstance()->getHtmlRepository(),
			self::getInstance()->getPageHtmlRetriever(),
			MediaWikiServices::getInstance()->getWikiPageFactory(),
			MediaWikiServices::getInstance()->getRevisionLookup(),
			MediaWikiServices::getInstance()->getUserIdentityLookup(),
			RequestContext::getMain()->getLanguage()
		);
	}

	public static function newUnapprovePageApi(): UnapprovePageApi {
		return new UnapprovePageApi(
			self::getInstance()->newPageApprovalAuthorizer(),
			self::getInstance()->getApprovalLog(),
			MediaWikiServices::getInstance()->getWikiPageFactory(),
			MediaWikiServices::getInstance()->getRevisionLookup(),
			MediaWikiServices::getInstance()->getUserIdentityLookup()
		);
	}

	private function newPageApprovalAuthorizer(): ApprovalAuthorizer {
		return new AuthorityBasedApprovalAuthorizer(
			RequestContext::getMain()->getUser()->getId(),
			$this->getApproverRepository()
		);
	}

	public function getApprovalLog(): ApprovalLog {
		return new DatabaseApprovalLog(
			$this->getDatabase()
		);
	}

	private function getDatabase(): IDatabase {
		return MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection( DB_PRIMARY );
	}

	public function newEvaluateApprovalStateAction(): EvaluateApprovalState {
		return new EvaluateApprovalState(
			htmlRepository: $this->getHtmlRepository(),
			approvalLog: $this->getApprovalLog()
		);
	}

	public function getHtmlRepository(): HtmlRepository {
		return new DatabaseHtmlRepository(
			$this->getDatabase()
		);
	}

	public function getPageHtmlRetriever(): PageHtmlRetriever {
		return new PageHtmlRetriever(
			MediaWikiServices::getInstance()->getWikiPageFactory()
		);
	}

	public function getTemplateParser(): TemplateParser {
		return new TemplateParser( __DIR__ . '/../templates/' );
	}

	public function getApproverRepository(): ApproverRepository {
		return new DatabaseApproverRepository(
			database: $this->getDatabase()
		);
	}

	public function newApprovalUiQuery(): ApprovalUiQuery {
		return new ApprovalUiQuery(
			approvalLog: $this->getApprovalLog(),
			approvalAuthorizer: $this->newPageApprovalAuthorizer(),
			approverRepository: $this->getApproverRepository()
		);
	}

	public static function newSpecialPendingApprovals(): SpecialPendingApprovals {
		return new SpecialPendingApprovals(
			self::getInstance()->getApproverRepository(),
			self::getInstance()->newPendingApprovalRetriever(),
			MediaWikiServices::getInstance()->getLinkRenderer()
		);
	}

	private function newPendingApprovalRetriever(): PendingApprovalRetriever {
		return new DatabasePendingApprovalRetriever(
			$this->getDatabase(),
			$this->getApproverRepository()
		);
	}

	public static function newSpecialManageApprovers(): SpecialManageApprovers {
		return new SpecialManageApprovers(
			self::getInstance()->getApproverRepository(),
			MediaWikiServices::getInstance()->getUserFactory()
		);
	}

}
