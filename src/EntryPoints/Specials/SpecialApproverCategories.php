<?php

namespace ProfessionalWiki\PageApprovals\EntryPoints\Specials;

use MediaWiki\MediaWikiServices;
use ProfessionalWiki\PageApprovals\Adapters\DatabaseApproverRepository;
use ProfessionalWiki\PageApprovals\Adapters\UsersLookup;
use SpecialPage;
use PermissionsError;
use ProfessionalWiki\PageApprovals\Application\UseCases\GetAllApproversCategories;
use LightnCandy\LightnCandy;

class SpecialApproverCategories extends SpecialPage {

	public function __construct() {
		parent::__construct( 'ApproverCategories' );
	}

	public function isListed(): bool {
		return $this->isAdmin();
	}

	public function execute( $subPage ): void {
		if ( !$this->isAdmin() ) {
			throw new PermissionsError( 'sysop' );
		}

		$db = MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection( DB_REPLICA );
		$usersLookup = new UsersLookup( $db );
		$databaseApproverRepository = new DatabaseApproverRepository( $db );

		$approversCategories = ( new GetAllApproversCategories(
			$usersLookup, $databaseApproverRepository
		) )->getAllApproversCategories();

		$this->renderHtml( $approversCategories );
	}

	protected function isAdmin(): bool {
		$userGroupManager = MediaWikiServices::getInstance()->getUserGroupManager();
		$userGroups = $userGroupManager->getUserGroups( $this->getUser() );
		return in_array( 'sysop', $userGroups );
	}

	private function renderHtml( array $approversCategories ): void {
		$template = file_get_contents( __DIR__ . '/../../../templates/ApproverCategories.mustache' );
		$compiledTemplate = LightnCandy::compile( $template, [ 'flags' => LightnCandy::FLAG_MUSTACHE ] );
		$prepareTemplate = LightnCandy::prepare( $compiledTemplate );
		$this->getOutput()->addHTML( $prepareTemplate( [ 'approvers' => $approversCategories ] ) );
	}

}
