<?php

namespace ProfessionalWiki\PageApprovals\EntryPoints\Specials;

use MediaWiki\MediaWikiServices;
use SpecialPage;
use PermissionsError;
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

		$output = $this->getOutput();
		$approvers = [
			[
				'id' => 1,
				'name' => 'John Doe',
				'categories' => [ 'Category A', 'Category B' ]
			],
			[
				'id' => 2,
				'name' => 'Jane Smith',
				'categories' => [ 'Category B', 'Category C' ]
			]
		]; // Placeholder

		$templatePath = __DIR__ . '/../../../templates/ApproverCategories.mustache';
		$template = file_get_contents( $templatePath );

		$phpStr = LightnCandy::compile( $template, [
			'flags' => LightnCandy::FLAG_PARENT
		] );

		$renderer = LightnCandy::prepare( $phpStr );

		if ( !is_callable( $renderer ) ) {
			throw new \RuntimeException( "Renderer preparation failed." );
		}

		$html = $renderer( [ 'approvers' => $approvers ] );
		$output->addHTML( $html );
	}

	protected function isAdmin(): bool {
		$userGroupManager = MediaWikiServices::getInstance()->getService( 'UserGroupManager' );
		$userGroups = $userGroupManager->getUserGroups( $this->getUser() );
		return in_array( 'sysop', $userGroups );
	}

}
