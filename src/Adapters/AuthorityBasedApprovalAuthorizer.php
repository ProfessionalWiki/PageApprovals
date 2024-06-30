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
		private int $userId,
		private ApproverRepository $approverRepository
	) {
	}

	public function canApprove( WikiPage $page ): bool {
		$sharedCategories = array_intersect(
			$this->approverRepository->getApproverCategories( $this->userId ),
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
