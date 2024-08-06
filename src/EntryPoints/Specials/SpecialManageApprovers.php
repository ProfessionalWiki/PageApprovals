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

		$approversWithCategories = new GetApproversWithCategories( $this->approverRepository );
		$approversCategories = $approversWithCategories->getApproversWithCategories();

		$request = $this->getRequest();
		if ( $request->wasPosted() ) {
			$this->handlePostRequest( $request, $approversCategories );
			$this->getOutput()->redirect( $this->getPageTitle()->getLocalURL() );
			return;
		}

		$this->renderHtml( $approversCategories );

		$this->getOutput()->addModuleStyles( 'ext.pageApprovals.manageApprovers.styles' );
	}

	private function isAdmin(): bool {
		$userGroups = $this->userGroupManager->getUserGroups( $this->getUser() );
		return in_array( 'sysop', $userGroups );
	}

	/**
	 * @param array<Approver> $approversCategories
	 */
	private function handlePostRequest( WebRequest $request, array $approversCategories ): void {
		$action = $request->getText( 'action' );
		$username = $request->getText( 'username' );
		$category = $request->getText( 'category' );

		$user = $this->userFactory->newFromName( $username );
		$userId = $user->getId();

		if ( !$userId ) {
			return;
		}

		$userWithCategories = array_filter( $approversCategories, fn( $approver ) => $approver->username === $username
		);
		$currentCategories = $userWithCategories[0]->categories ?? [];

		$this->processCategoryAction( $action, $category, $userId, $currentCategories );
	}

	/**
	 * @param string[] $currentCategories
	 */
	private function processCategoryAction( string $action, string $category, int $userId, array $currentCategories ): void {
		switch ( $action ) {
			case 'add':
				$currentCategories[] = $category;
				break;
			case 'delete':
				$currentCategories = array_filter( $currentCategories, fn( string $cat ) => $cat !== $category );
				break;
			case 'add-approver':
				$currentCategories = [];
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
	 * @return array<array<string, mixed>>
	 */
	private function approversToViewModel( array $approvers ): array {
		return array_map( fn( Approver $approver ) => $this->approverToViewModel( $approver ), $approvers );
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
