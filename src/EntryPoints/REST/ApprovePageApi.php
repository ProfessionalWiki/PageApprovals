<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\EntryPoints\REST;

use MediaWiki\Context\RequestContext;
use MediaWiki\Language\Language;
use MediaWiki\Message\Message;
use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Rest\Response;
use MediaWiki\Rest\SimpleHandler;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\User\UserIdentityLookup;
use MediaWiki\Utils\MWTimestamp;
use ProfessionalWiki\PageApprovals\Adapters\PageHtmlRetriever;
use ProfessionalWiki\PageApprovals\Application\ApprovalAuthorizer;
use ProfessionalWiki\PageApprovals\Application\ApprovalLog;
use ProfessionalWiki\PageApprovals\Application\ApprovalState;
use ProfessionalWiki\PageApprovals\Application\HtmlRepository;
use Wikimedia\ParamValidator\ParamValidator;
use WikiPage;

class ApprovePageApi extends SimpleHandler {

	public function __construct(
		private ApprovalAuthorizer $authorizer,
		private ApprovalLog	$approvalLog,
		private HtmlRepository $htmlRepository,
		private PageHtmlRetriever $pageHtmlRetriever,
		private WikiPageFactory $wikiPageFactory,
		private RevisionLookup $revisionLookup,
		private UserIdentityLookup $userIdentityLookup,
		private Language $language,
		private RequestContext $requestContext
	) {
	}

	public function run( int $revisionId ): Response {
		$page = $this->getPageFromRevisionId( $revisionId );

		if ( $page === null ) {
			return $this->newInvalidPageResponse();
		}

		if ( !$this->authorizer->canApprove( $page ) ) {
			return $this->newAuthorizationFailedResponse();
		}

		if ( !$this->revisionIsLatest( $revisionId, $page ) ) {
			return $this->newOutdatedRevisionResponse();
		}

		$this->approvalLog->approvePage( $page->getId(), $this->getAuthority()->getUser()->getId() );

		// Some extensions, like DisplayTitle, expect the current page to be in the request context.
		$this->requestContext->setTitle( $page->getTitle() );
		$html = $this->pageHtmlRetriever->getPageHtml( $page->getId() );
		if ( $html !== null ) {
			$this->htmlRepository->saveApprovedHtml( $page->getId(), $html );
		}

		return $this->newSuccessResponse( $this->approvalLog->getApprovalState( $page->getId() ) );
	}

	private function getPageFromRevisionId( int $revisionId ): ?WikiPage {
		$revision = $this->revisionLookup->getRevisionById( $revisionId );

		if ( $revision === null ) {
			return null;
		}

		return $this->wikiPageFactory->newFromTitle( $revision->getPage() );
	}

	private function revisionIsLatest( int $revisionId, WikiPage $page ): bool {
		return $revisionId === $page->getRevisionRecord()?->getId();
	}

	public function newSuccessResponse( ?ApprovalState $state ): Response {
		if ( $state === null ) {
			return $this->getResponseFactory()->createNoContent();
		}

		return $this->getResponseFactory()->createJson( [
			'approvalTimestamp' => $this->getFormattedTimestamp( $state->approvalTimestamp ),
			'approver' => $this->getUserNameFromUserId( $state->approverId ),
			'message' => ( new Message( 'pageapprovals-approve-page-text' ) )->params(
				$this->getUserNameFromUserId( $state->approverId ) ?? '',
				$this->getFormattedTimestamp( $state->approvalTimestamp )
			)->plain()
		] );
	}

	private function getFormattedTimestamp( int $timestamp ): string {
		return $this->language->getHumanTimestamp( new MWTimestamp( $timestamp ) );
	}

	private function getUserNameFromUserId( ?int $userId ): ?string {
		if ( $userId === null ) {
			return null;
		}

		return $this->userIdentityLookup->getUserIdentityByUserId( $userId )?->getName();
	}

	public function newAuthorizationFailedResponse(): Response {
		return $this->getResponseFactory()->createHttpError(
			403,
			[
				'message' => ( new Message( 'pageapprovals-approve-authorization-failed' ) )->plain()
			]
		);
	}

	public function newInvalidPageResponse(): Response {
		return $this->getResponseFactory()->createHttpError(
			404,
			[
				'message' => ( new Message( 'pageapprovals-approve-invalid-page' ) )->plain()
			]
		);
	}

	public function newOutdatedRevisionResponse(): Response {
		return $this->getResponseFactory()->createHttpError(
			409,
			[
				'message' => ( new Message( 'pageapprovals-approve-outdated-revision' ) )->plain()
			]
		);
	}

	/**
	 * @return array<string, array<string, mixed>>
	 */
	public function getParamSettings(): array {
		return [
			'revisionId' => [
				self::PARAM_SOURCE => 'path',
				ParamValidator::PARAM_TYPE => 'integer',
				ParamValidator::PARAM_REQUIRED => true,
			],
		];
	}

}
