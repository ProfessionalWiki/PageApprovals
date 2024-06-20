<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\EntryPoints\REST;

use MediaWiki\Page\PageIdentity;
use MediaWiki\Rest\Response;
use MediaWiki\Rest\SimpleHandler;
use MediaWiki\Rest\StringStream;
use ProfessionalWiki\PageApprovals\Application\PageApprovalAuthorizer;
use Title;
use Wikimedia\ParamValidator\ParamValidator;

class ApprovePageApi extends SimpleHandler {

	public function __construct(
		private PageApprovalAuthorizer $authorizer
	) {
	}

	public function run( int $pageId ): Response {
		// TODO: logic
		$response = $this->getResponseFactory()->create();
		$response->setBody( new StringStream( "{ pageId: {$pageId} }" ) );

		$page = $this->getPageIdentity( $pageId );

		if ( $page === null ) {
			$response->setStatus( 404 );
			return $response;
		}

		if ( !$this->authorizer->canApprove( $page ) ) {
			$response->setStatus( 403 );
			return $response;
		}

		$response->setStatus( 200 );
		return $response;
	}

	private function getPageIdentity( int $pageId ): ?PageIdentity {
		return Title::newFromID( $pageId );
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
