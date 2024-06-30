<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Adapters;

use MediaWiki\Permissions\Authority;
use ProfessionalWiki\PageApprovals\Application\ApprovalAuthorizer;
use ProfessionalWiki\PageApprovals\Application\ApproverRepository;
use Title;
use WikiPage;

class AuthorityBasedApprovalAuthorizer implements ApprovalAuthorizer {

	public function __construct(
		private Authority $authority,
		private ApproverRepository $approverRepository
	) {
	}

	public function canApprove( WikiPage $page ): bool {
		$sharedCategories = array_intersect(
			$this->approverRepository->getApproverCategories( $this->authority->getUser()->getId() ),
			$this->getPageCategories( $page )
		);

		return $sharedCategories !== [];
	}

	/**
	 * @return string[]
	 */
	private function getPageCategories( WikiPage $page ): array {
		return array_map(
			fn( Title $categoryTitle ) => $categoryTitle->getText(),
			iterator_to_array( $page->getCategories() )
		);
	}

}
