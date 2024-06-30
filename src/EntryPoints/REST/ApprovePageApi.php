<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\EntryPoints\REST;

use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Rest\Response;
use MediaWiki\Rest\SimpleHandler;
use ProfessionalWiki\PageApprovals\Adapters\PageHtmlRetriever;
use ProfessionalWiki\PageApprovals\Application\ApprovalAuthorizer;
use ProfessionalWiki\PageApprovals\Application\ApprovalLog;
use ProfessionalWiki\PageApprovals\Application\HtmlRepository;
use Wikimedia\ParamValidator\ParamValidator;
use WikiPage;

class ApprovePageApi extends SimpleHandler {

	public function __construct(
		private ApprovalAuthorizer $authorizer,
		private ApprovalLog	$approvalLog,
		private HtmlRepository $htmlRepository,
		private PageHtmlRetriever $pageHtmlRetriever,
		private WikiPageFactory $wikiPageFactory
	) {
	}

	public function run( int $pageId ): Response {
		$page = $this->getPage( $pageId );

		if ( $page === null ) {
			return $this->newInvalidPageResponse();
		}

		if ( !$this->authorizer->canApprove( $page ) ) {
			return $this->newAuthorizationFailedResponse();
		}

		$this->approvalLog->approvePage( $pageId, $this->getAuthority()->getUser()->getId() );

		$html = $this->pageHtmlRetriever->getPageHtml( $pageId );
		if ( $html !== null ) {
			$this->htmlRepository->saveApprovedHtml( $pageId, $html );
		}

		return $this->newSuccessResponse();
	}

	private function getPage( int $pageId ): ?WikiPage {
		return $this->wikiPageFactory->newFromID( $pageId );
	}

	public function newSuccessResponse(): Response {
		return $this->getResponseFactory()->createNoContent();
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
