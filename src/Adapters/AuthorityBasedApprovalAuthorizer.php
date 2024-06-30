<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Adapters;

use Iterator;
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
			$this->titleArrayObjectToStringArray( $page->getCategories() )
		);

		return $sharedCategories !== [];
	}

	/**
	 * @param Iterator<Title> $titles
	 * @return string[]
	 */
	private function titleArrayObjectToStringArray( Iterator $titles ): array {
		return array_map(
			fn( Title $category ) => $category->getText(), // TODO: verify handling of different category names
			iterator_to_array( $titles ),
		);
	}

}
