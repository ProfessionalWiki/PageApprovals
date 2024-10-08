<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Presentation;

use Language;
use MWTimestamp;
use OutputPage;
use ProfessionalWiki\PageApprovals\Application\UseCases\ApprovalUiQuery\UiArguments;
use ProfessionalWiki\PageApprovals\PageApprovals;

class OutputPageUiPresenter {

	public function __construct(
		private readonly OutputPage $out
	) {
	}

	public function presentUi( UiArguments $arguments ): void {
		if ( !$arguments->showUi ) {
			return;
		}

		$this->out->setIndicators( [
			'page-approvals' => PageApprovals::getInstance()->getTemplateParser()->processTemplate(
				'PageApprovalStatus', [
					'isPageApproved' => $arguments->pageIsApproved,
					'canApprove' => $arguments->userIsApprover,
					'approveButtonText' => $this->out->msg( 'pageapprovals-approve-button' )->text(),
					'unapproveButtonText' => $this->out->msg( 'pageapprovals-unapprove-button' )->text(),
					'statusApproved' => $this->out->msg( 'pageapprovals-status-approved' )->text(),
					'statusNotApproved' => $this->out->msg( 'pageapprovals-status-not-approved' )->text(),
					'approvedPageText' => $this->out->msg( 'pageapprovals-approve-page-text' )->params(
						$arguments->approverUserName,
						$this->getFormattedTimestamp( $arguments->approvalTimestamp )
					)->text(),
					'approverUserName' => $arguments->approverUserName,
					'approvalTimestamp' => $this->getFormattedTimestamp( $arguments->approvalTimestamp )
				]
			)
		] );

		$this->out->addModuleStyles( 'ext.pageApprovals.styles' );
		$this->out->addModules( 'ext.pageApprovals.scripts' );
	}

	private function getFormattedTimestamp( int $timestamp ): string {
		return $this->out->getLanguage()->getHumanTimestamp( new MWTimestamp( $timestamp ) );
	}

}
