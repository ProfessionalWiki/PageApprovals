<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\EntryPoints\REST;

use MediaWiki\Page\PageIdentity;
use MediaWiki\Rest\Response;
use MediaWiki\Rest\SimpleHandler;
use ProfessionalWiki\PageApprovals\Application\ApprovalAuthorizer;
use Title;
use Wikimedia\ParamValidator\ParamValidator;

class UnapprovePageApi extends SimpleHandler {

	public function __construct(
		private ApprovalAuthorizer $authorizer
	) {
	}

	public function run( int $pageId ): Response {
		$page = $this->getPageIdentity( $pageId );

		if ( $page === null ) {
			return $this->presentInvalidPage();
		}

		if ( !$this->authorizer->canApprove( $page ) ) {
			return $this->presentAuthorizationFailed();
		}

		// TODO: $persistence->markAsUnapproved( $pageId, $userId );
		// TODO: $this->presentUnapproveFailed();

		return $this->presentSuccess();
	}

	private function getPageIdentity( int $pageId ): ?PageIdentity {
		return Title::newFromID( $pageId );
	}

	public function presentSuccess(): Response {
		return $this->getResponseFactory()->create();
	}

	public function presentAuthorizationFailed(): Response {
		return $this->getResponseFactory()->createHttpError( 403 );
	}

	public function presentInvalidPage(): Response {
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
