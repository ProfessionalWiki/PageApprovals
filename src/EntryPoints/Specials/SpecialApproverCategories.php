<?php

namespace ProfessionalWiki\PageApprovals\EntryPoints\Specials;

use MediaWiki\MediaWikiServices;
use ProfessionalWiki\PageApprovals\Adapters\DatabaseApproverRepository;
use ProfessionalWiki\PageApprovals\Application\UseCases\GetApproversWithCategories;
use SpecialPage;
use PermissionsError;
use LightnCandy\LightnCandy;
use WebRequest;

class SpecialApproverCategories extends SpecialPage {

	public function __construct() {
		parent::__construct( 'ApproverCategories' );
	}

	public function isListed(): bool {
		return $this->isAdmin(); // TODO: Add right permission checks
	}

	public function execute( $subPage ): void {
		if ( !$this->isAdmin() ) {
			throw new PermissionsError( 'sysop' );
		}

		$db = MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection( DB_PRIMARY );
		$databaseApproverRepository = new DatabaseApproverRepository( $db );

		$approversWithCategories = new GetApproversWithCategories( $databaseApproverRepository );

		$approversCategories = $approversWithCategories->getApproversWithCategories();

		$request = $this->getRequest();
		if ( $request->wasPosted() ) {
			$this->handlePostRequest( $request, $approversCategories, $databaseApproverRepository );
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
	 * @param array<array{username: string, userId: int, categories: string[]}> $approversCategories
	 */
	private function handlePostRequest( WebRequest $request, array $approversCategories, DatabaseApproverRepository $databaseApproverRepository ): void {
		$action = $request->getText( 'action' );
		$username = $request->getText( 'username' );
		$category = $request->getText( 'category' );

		$userData = array_filter( $approversCategories, fn( $approver ) => $approver['username'] === $username );
		if ( empty( $userData ) || empty( $category ) ) {
			return;
		}
		$userData = array_values( $userData )[0];
		$userId = $userData['userId'];
		$currentCategories = $userData['categories'];

		if ( $action === 'add' ) {
			$currentCategories[] = $category;
		} elseif ( $action === 'delete' ) {
			$currentCategories = array_filter( $currentCategories, fn( string $cat ) => $cat !== $category );
		}

		$databaseApproverRepository->setApproverCategories( $userId, $currentCategories );
	}

	private function renderHtml( array $approversCategories ): void {
		$template = file_get_contents( __DIR__ . '/../../../templates/ApproverCategories.mustache' );
		$compiledTemplate = LightnCandy::compile( $template, [ 'flags' => LightnCandy::FLAG_MUSTACHE ] );
		$this->getOutput()->addHTML(
			LightnCandy::prepare( $compiledTemplate )( [ 'approvers' => $approversCategories ] )
		);
	}

}
