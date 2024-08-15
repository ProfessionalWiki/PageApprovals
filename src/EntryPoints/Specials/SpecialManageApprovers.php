<?php

namespace ProfessionalWiki\PageApprovals\EntryPoints\Specials;

use MediaWiki\User\UserFactory;
use MediaWiki\User\UserGroupManager;
use ProfessionalWiki\PageApprovals\Application\Approver;
use ProfessionalWiki\PageApprovals\Application\ApproverRepository;
use ProfessionalWiki\PageApprovals\Application\UseCases\GetApproversWithCategories;
use SpecialPage;
use LightnCandy\LightnCandy;
use WebRequest;

class SpecialManageApprovers extends SpecialPage {

	public function __construct(
		private readonly ApproverRepository $approverRepository,
		private readonly UserGroupManager $userGroupManager,
		private readonly UserFactory $userFactory
	) {
		parent::__construct( 'ManageApprovers', restriction: 'manage-approvers' );
	}

	public function isListed(): bool {
		return $this->isAdmin(); // TODO: Add right permission checks
	}

	public function execute( $subPage ): void {
		$this->setHeaders();
		$this->checkPermissions();
		$this->checkReadOnly();

		$request = $this->getRequest();
		if ( $request->wasPosted() ) {
			$this->handlePostRequest( $request );
		}

		$approversWithCategories = new GetApproversWithCategories( $this->approverRepository );
		$approversCategories = $approversWithCategories->getApproversWithCategories();

		$this->renderHtml( $this->filterOutApproversWithNoCategories( $approversCategories ) );

		$this->getOutput()->addModuleStyles( 'ext.pageApprovals.manageApprovers.styles' );
	}

	private function isAdmin(): bool {
		$userGroups = $this->userGroupManager->getUserGroups( $this->getUser() );
		return in_array( 'sysop', $userGroups );
	}

	private function handlePostRequest( WebRequest $request ): void {
		$action = $request->getText( 'action' );
		$username = $request->getText( 'username' );
		$category = $request->getText( 'category' );

		$user = $this->userFactory->newFromName( $username );
		$userId = $user->getId();

		if ( !$userId ) {
			return;
		}

		$this->processCategoryAction( $action, $category, $userId );
	}

	private function processCategoryAction( string $action, string $category, int $userId ): void {
		$currentCategories = $this->approverRepository->getApproverCategories( $userId );

		switch ( $action ) {
			case 'add-approver':
			case 'add':
				$currentCategories[] = $category;
				break;
			case 'delete':
				$currentCategories = array_filter( $currentCategories, fn( string $cat ) => $cat !== $category );
				break;
			default:
				return;
		}
		$this->approverRepository->setApproverCategories( $userId, $currentCategories );
	}

	/**
	 * @param array<Approver> $approversCategories
	 */
	private function renderHtml( array $approversCategories ): void {
		$template = file_get_contents( __DIR__ . '/../../../templates/ManageApprovers.mustache' );
		$compiledTemplate = LightnCandy::compile( $template, [ 'flags' => LightnCandy::FLAG_MUSTACHE ] );
		$this->getOutput()->addHTML(
			LightnCandy::prepare( $compiledTemplate )(
				[ 'approvers' => $this->approversToViewModel( $approversCategories ) ]
			)
		);
	}

	/**
	 * @param array<Approver> $approvers
	 *
	 * @return array<Approver>
	 */
	private function filterOutApproversWithNoCategories( array $approvers ): array {
		$request = $this->getRequest();
		$user = $this->userFactory->newFromName( $request->getText( 'username' ) );

		return array_filter( $approvers, static function ( Approver $approver ) use ( $request, $user ) {
			if ( !empty( $approver->categories ) ) {
				return true;
			}
			return $request->wasPosted() && $approver->username === $user->getName();
		} );
	}

	/**
	 * @param array<Approver> $approvers
	 *
	 * @return array<array<string, mixed>>
	 */
	private function approversToViewModel( array $approvers ): array {
		return array_values(
			array_map(
				fn( Approver $approver ) => $this->approverToViewModel( $approver ),
				$approvers
			)
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	private function approverToViewModel( Approver $approver ): array {
		return [
			'username' => $approver->username,
			'userId' => $approver->userId,
			'categories' => $approver->categories
		];
	}

}
