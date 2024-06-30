<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\EntryPoints\REST;

use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Rest\Response;
use MediaWiki\Rest\SimpleHandler;
use MediaWiki\Revision\RevisionLookup;
use ProfessionalWiki\PageApprovals\Application\ApprovalAuthorizer;
use ProfessionalWiki\PageApprovals\Application\ApprovalLog;
use Wikimedia\ParamValidator\ParamValidator;
use WikiPage;

class UnapprovePageApi extends SimpleHandler {

	public function __construct(
		private ApprovalAuthorizer $authorizer,
		private ApprovalLog	$approvalLog,
		private WikiPageFactory $wikiPageFactory,
		private RevisionLookup $revisionLookup
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

		$this->approvalLog->unapprovePage( $page->getId(), $this->getAuthority()->getUser()->getId() );

		return $this->newSuccessResponse();
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

	public function newSuccessResponse(): Response {
		return $this->getResponseFactory()->createNoContent();
	}

	public function newAuthorizationFailedResponse(): Response {
		return $this->getResponseFactory()->createHttpError( 403 );
	}

	public function newInvalidPageResponse(): Response {
		return $this->getResponseFactory()->createHttpError( 404 );
	}

	public function newOutdatedRevisionResponse(): Response {
		return $this->getResponseFactory()->createHttpError( 409 );
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
