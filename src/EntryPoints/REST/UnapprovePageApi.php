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

class UnapprovePageApi extends SimpleHandler {

	public function __construct(
		private PageApprovalAuthorizer $authorizer
	) {
	}

	public function run( int $pageId ): Response {
		// TODO: logic
		$response = $this->getResponseFactory()->create();

		if ( $this->authorizer->canApprove( $this->getPageIdentity( $pageId ) ) ) {
			$response->setStatus( 200 );
		} else {
			$response->setStatus( 403 );
		}

		$response->setBody( new StringStream( "{ pageId: ${pageId} }" ) );

		return $response;
	}

	private function getPageIdentity( int $pageId ): PageIdentity {
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
