<?php

namespace ProfessionalWiki\PageApprovals\EntryPoints\Specials;

use MediaWiki\MediaWikiServices;
use ProfessionalWiki\PageApprovals\Application\ApproverRepository;
use ProfessionalWiki\PageApprovals\Application\UseCases\GetApproversWithCategories;
use ProfessionalWiki\PageApprovals\PageApprovals;
use SpecialPage;
use LightnCandy\LightnCandy;
use WebRequest;

class SpecialManageApprovers extends SpecialPage {

	private ApproverRepository $approverRepository;

	public function __construct() {
		parent::__construct( 'ManageApprovers', restriction: 'manage-approvers' );
		$this->approverRepository = PageApprovals::getInstance()->getApproverRepository();
	}

	public function isListed(): bool {
		return $this->isAdmin(); // TODO: Add right permission checks
	}

	public function execute( $subPage ): void {
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
	}

	private function isAdmin(): bool {
		$userGroups = MediaWikiServices::getInstance()->getUserGroupManager()->getUserGroups( $this->getUser() );
		return in_array( 'sysop', $userGroups );
	}

	/**
	 * @param array $approversCategories
	 */
	private function handlePostRequest( WebRequest $request, array $approversCategories ): void {
		$action = $request->getText( 'action' );
		$username = $request->getText( 'username' );
		$category = $request->getText( 'category' );

		$userFactory = MediaWikiServices::getInstance()->getUserFactory();
		$user = $userFactory->newFromName( $username );
		$userId = $user->getId();

		if ( !$userId ) {
			return;
		}

		$userWithCategories = array_filter( $approversCategories, fn( $approver ) => $approver['username'] === $username
		);
		$currentCategories = $userWithCategories[0]['categories'] ?? [];

		$this->processCategoryAction( $action, $category, $userId, $currentCategories );
	}

	/**
	 * @param array $approversCategories
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

	private function renderHtml( array $approversCategories ): void {
		$template = file_get_contents( __DIR__ . '/../../../templates/ManageApprovers.mustache' );
		$compiledTemplate = LightnCandy::compile( $template, [ 'flags' => LightnCandy::FLAG_MUSTACHE ] );
		$this->getOutput()->addHTML(
			LightnCandy::prepare( $compiledTemplate )( [ 'approvers' => $approversCategories ] )
		);
	}

}
