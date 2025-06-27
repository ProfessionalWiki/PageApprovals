<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\EntryPoints\REST;

use MediaWiki\Message\Message;
use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Rest\Response;
use MediaWiki\Rest\SimpleHandler;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\User\UserIdentityLookup;
use ProfessionalWiki\PageApprovals\Application\ApprovalAuthorizer;
use ProfessionalWiki\PageApprovals\Application\ApprovalLog;
use ProfessionalWiki\PageApprovals\Application\ApprovalState;
use Wikimedia\ParamValidator\ParamValidator;
use WikiPage;

class UnapprovePageApi extends SimpleHandler {

	public function __construct(
		private ApprovalAuthorizer $authorizer,
		private ApprovalLog	$approvalLog,
		private WikiPageFactory $wikiPageFactory,
		private RevisionLookup $revisionLookup,
		private UserIdentityLookup $userIdentityLookup
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
			'approvalTimestamp' => $state->approvalTimestamp,
			'approver' => $this->getUserNameFromUserId( $state->approverId ),
			'message' => ( new Message( 'pageapprovals-unapproved' ) )->plain()
		] );
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
				'message' => ( new Message( 'pageapprovals-unapprove-authorization-failed' ) )->plain()
			]
		);
	}

	public function newInvalidPageResponse(): Response {
		return $this->getResponseFactory()->createHttpError(
			404,
			[
				'message' => ( new Message( 'pageapprovals-unapprove-invalid-page' ) )->plain()
			]
		);
	}

	public function newOutdatedRevisionResponse(): Response {
		return $this->getResponseFactory()->createHttpError(
			409,
			[
				'message' => ( new Message( 'pageapprovals-unapprove-outdated-revision' ) )->plain()
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
