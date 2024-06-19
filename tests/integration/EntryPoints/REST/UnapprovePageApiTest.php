<?php

namespace ProfessionalWiki\PageApprovals\Tests\EntryPoints\REST;

use MediaWiki\Rest\RequestData;
use MediaWiki\Tests\Rest\Handler\HandlerTestTrait;
use MediaWiki\Tests\Unit\Permissions\MockAuthorityTrait;
use ProfessionalWiki\PageApprovals\EntryPoints\REST\UnapprovePageApi;
use ProfessionalWiki\PageApprovals\Tests\PageApprovalsIntegrationTest;

/**
 * @covers \ProfessionalWiki\PageApprovals\EntryPoints\REST\UnapprovePageApi
 * @group database
 */
class UnapprovePageApiTest extends PageApprovalsIntegrationTest {
	use HandlerTestTrait;
	use MockAuthorityTrait;

	public function testUnapprovePageHappyPath(): void {
		$response = $this->executeHandler(
			$this->newUnapprovePageApi(),
			$this->createValidRequestData( 1 )
		);

		$this->assertSame( 200, $response->getStatusCode() );
		$this->assertSame( '{ pageId: 1 }', $response->getBody()->getContents() );
	}

	private function newUnapprovePageApi(): UnapprovePageApi {
		return new UnapprovePageApi();
	}

	private function createValidRequestData( int $pageId ): RequestData {
		return new RequestData( [
			'method' => 'POST',
			'pathParams' => [
				'pageId' => $pageId
			],
			'headers' => [
				'Content-Type' => 'application/json'
			]
		] );
	}

}
