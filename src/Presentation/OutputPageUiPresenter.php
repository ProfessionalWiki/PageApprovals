<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Presentation;

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

		$this->out->addHTML(
			PageApprovals::getInstance()->getTemplateParser()->processTemplate(
				'PageApprovalStatus',
				[
					'isPageApproved' => $arguments->pageIsApproved,
					'canApprove' => $arguments->userIsApprover,
					'approveButtonText' => $this->out->msg( 'pageapprovals-approve-button' )->text(),
					'unapproveButtonText' => $this->out->msg( 'pageapprovals-unapprove-button' )->text(),
					'statusApproved' => $this->out->msg( 'pageapprovals-status-approved' )->text(),
					'statusNotApproved' => $this->out->msg( 'pageapprovals-status-not-approved' )->text(),
					'approvedPageText' => $this->out->msg( 'pageapprovals-approve-page-text' )->text(),
					'approverUserName' => $arguments->approverUserName,
					'approvalTimestamp' => $arguments->approvalTimestamp
				]
			)
		);

		$this->out->addModuleStyles( 'ext.pageApprovals.styles' );
		$this->out->addModules( 'ext.pageApprovals.scripts' );
	}

}
