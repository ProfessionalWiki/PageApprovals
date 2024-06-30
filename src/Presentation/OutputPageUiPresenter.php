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

		// TODO: use $arguments->approvalTimestamp and $arguments->approverId

		$this->out->addHTML(
			PageApprovals::getInstance()->getTemplateParser()->processTemplate(
				'PageApprovalStatus',
				[
					'isPageApproved' => $arguments->pageIsApproved,
					'canApprove' => $arguments->userIsApprover,
					'approveButtonText' => $this->out->msg( 'pageapprovals-approve-button' )->text(),
					'unapproveButtonText' => $this->out->msg( 'pageapprovals-unapprove-button' )->text(),
					'approvalStatusMessage' => $this->out->msg(
						$arguments->pageIsApproved ? 'pageapprovals-status-approved' : 'pageapprovals-status-not-approved'
					)->text(),
					'approverRealName' => $arguments->approverRealName,
					'approvalTimestamp' => $arguments->approvalTimestamp
				]
			)
		);

		$this->out->addModules( 'ext.pageApprovals.resources' );
	}

}
