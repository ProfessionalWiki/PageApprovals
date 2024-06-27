<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\EntryPoints\REST;

use MediaWiki\Page\PageIdentity;
use MediaWiki\Rest\Response;
use MediaWiki\Rest\SimpleHandler;
use ProfessionalWiki\PageApprovals\Adapters\PageContentRetriever;
use ProfessionalWiki\PageApprovals\Application\ApprovalAuthorizer;
use ProfessionalWiki\PageApprovals\Application\ApprovalLog;
use ProfessionalWiki\PageApprovals\Application\HtmlRepository;
use Title;
use Wikimedia\ParamValidator\ParamValidator;

class ApprovePageApi extends SimpleHandler {

	public function __construct(
		private ApprovalAuthorizer $authorizer,
		private ApprovalLog	$approvalLog,
		private HtmlRepository $htmlRepository,
		private PageContentRetriever $pageContentRetriever
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

		$this->approvalLog->approvePage( $pageId, $this->getAuthority()->getUser()->getId() );

		$content = $this->pageContentRetriever->getPageContent( $pageId );
		if ( $content !== null ) {
			$this->htmlRepository->saveApprovedHtml( $pageId, $content );
		}

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
