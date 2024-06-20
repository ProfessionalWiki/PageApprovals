<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals;

use ProfessionalWiki\PageApprovals\Adapters\InMemoryApprovalLog;
use ProfessionalWiki\PageApprovals\Adapters\InMemoryHtmlRepository;
use ProfessionalWiki\PageApprovals\Application\ApprovalAuthorizer;
use ProfessionalWiki\PageApprovals\Application\UseCases\EvaluateApprovalState;
use ProfessionalWiki\PageApprovals\EntryPoints\REST\ApprovePageApi;
use ProfessionalWiki\PageApprovals\EntryPoints\REST\UnapprovePageApi;
use ProfessionalWiki\PageApprovals\Adapters\AuthorityBasedApprovalAuthorizer;
use RequestContext;

class PageApprovals {

	public static function getInstance(): self {
		/** @var ?PageApprovals $instance */
		static $instance = null;
		$instance ??= new self();
		return $instance;
	}

	public static function newApprovePageApi(): ApprovePageApi {
		return new ApprovePageApi(
			self::getInstance()->newPageApprovalAuthorizer()
		);
	}

	public static function newUnapprovePageApi(): UnapprovePageApi {
		return new UnapprovePageApi(
			self::getInstance()->newPageApprovalAuthorizer()
		);
	}

	private function newPageApprovalAuthorizer(): ApprovalAuthorizer {
		return new AuthorityBasedApprovalAuthorizer(
			RequestContext::getMain()->getUser()
		);
	}

	public function newEvaluateApprovalStateAction(): EvaluateApprovalState {
		return new EvaluateApprovalState(
			htmlRepository: new InMemoryHtmlRepository(), // TODO
			approvalLog: new InMemoryApprovalLog() // TODO
		);
	}

}
