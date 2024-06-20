<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals;

use ProfessionalWiki\PageApprovals\Application\PageApprovalAuthorizer;
use ProfessionalWiki\PageApprovals\EntryPoints\REST\ApprovePageApi;
use ProfessionalWiki\PageApprovals\EntryPoints\REST\UnapprovePageApi;
use ProfessionalWiki\PageApprovals\Persistence\AuthorityBasedPageApprovalAuthorizer;
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

	private function newPageApprovalAuthorizer(): PageApprovalAuthorizer {
		return new AuthorityBasedPageApprovalAuthorizer(
			RequestContext::getMain()->getUser()
		);
	}

}
