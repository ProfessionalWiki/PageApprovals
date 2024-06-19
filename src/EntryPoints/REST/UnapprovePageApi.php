<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\EntryPoints\REST;

use MediaWiki\Rest\Response;
use MediaWiki\Rest\SimpleHandler;
use MediaWiki\Rest\StringStream;
use Wikimedia\ParamValidator\ParamValidator;

class UnapprovePageApi extends SimpleHandler {

	public function run( int $pageId ): Response {
		// TODO: logic
		$response = $this->getResponseFactory()->create();
		$response->setStatus( 200 );
		$response->setBody( new StringStream( "{ pageId: ${pageId} }" ) );
		return $response;
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
