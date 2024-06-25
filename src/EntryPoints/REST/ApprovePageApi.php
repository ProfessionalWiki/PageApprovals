<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\EntryPoints\REST;

use MediaWiki\Page\PageIdentity;
use MediaWiki\Rest\Response;
use MediaWiki\Rest\SimpleHandler;
use ProfessionalWiki\PageApprovals\Application\ApprovalAuthorizer;
use ProfessionalWiki\PageApprovals\Application\ApprovalLog;
use Title;
use Wikimedia\ParamValidator\ParamValidator;
use Wikimedia\Rdbms\DBError;

class ApprovePageApi extends SimpleHandler {

	public function __construct(
		private ApprovalAuthorizer $authorizer,
		private ApprovalLog	$approvalLog
	) {
	}

	public function run( int $pageId ): Response {
		$page = $this->getPageIdentity( $pageId );

		if ( $page === null ) {
			return $this->newInvalidPageResponse();
		}

		if ( !$this->authorizer->canApprove( $page ) ) {
			return $this->newAuthorizationFailedResponse();
		}

		try {
			$this->approvalLog->approvePage( $pageId, $this->getAuthority()->getUser()->getId() );
		} catch ( DBError $error ) {
			return $this->newApproveFailedResponse();
		}

		// TODO: HtmlRepository::saveApprovedHtml( $pageId, $html );

		return $this->newSuccessResponse();
	}

	private function getPageIdentity( int $pageId ): ?PageIdentity {
		return Title::newFromID( $pageId );
	}

	public function newSuccessResponse(): Response {
		return $this->getResponseFactory()->create();
	}

	public function newAuthorizationFailedResponse(): Response {
		return $this->getResponseFactory()->createHttpError( 403 );
	}

	public function newInvalidPageResponse(): Response {
		return $this->getResponseFactory()->createHttpError( 404 );
	}

	public function newApproveFailedResponse(): Response {
		return $this->getResponseFactory()->createHttpError( 500 );
	}

	/**
	 * @return array<string, array<string, mixed>>
	 */
	public function getParamSettings(): array {
		return [
			'pageId' => [
				self::PARAM_SOURCE => 'path',
				ParamValidator::PARAM_TYPE => 'integer',
				ParamValidator::PARAM_REQUIRED => true,
			],
		];
	}

}
