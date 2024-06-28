<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Adapters;

use MediaWiki\Page\PageIdentity;
use MediaWiki\Permissions\Authority;
use ProfessionalWiki\PageApprovals\Application\ApprovalAuthorizer;
use ProfessionalWiki\PageApprovals\Application\ApproverRepository;

class AuthorityBasedApprovalAuthorizer implements ApprovalAuthorizer {

	public function __construct(
		private Authority $authority,
		private ApproverRepository $approverRepository,
		private PageCategoriesRetriever $pageCategoriesRetriever
	) {
	}

	public function canApprove( PageIdentity $page ): bool {
		$sharedCategories = array_intersect(
			$this->approverRepository->getApproverCategories( $this->authority->getUser()->getId() ),
			$this->pageCategoriesRetriever->getPageCategories( $page )
		);

		return $sharedCategories !== [];
	}

}
