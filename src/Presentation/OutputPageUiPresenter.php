<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Presentation;

use MediaWiki\Output\OutputPage;
use MediaWiki\Utils\MWTimestamp;
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
			'ext-pageapprovals' => PageApprovals::getInstance()->getTemplateParser()->processTemplate(
				'PageApprovalWrapper', [
					'pageApproved' => $arguments->pageIsApproved ? 'true' : 'false',
					'canApprove' => $arguments->userIsApprover ? 'true' : 'false',
					'approver' => $arguments->approverUserName,
					// JS expects ISO 8601 format
					'approvalTimestamp' => ( new MWTimestamp( $arguments->approvalTimestamp ) )->getTimestamp( TS_ISO_8601 )
				]
			)
		] );

		$this->out->addModules( 'ext.pageApprovals' );
	}

}
