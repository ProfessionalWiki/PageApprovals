<?php

namespace ProfessionalWiki\PageApprovals\Tests\EntryPoints\REST;

use MediaWiki\Rest\RequestData;
use MediaWiki\Tests\Rest\Handler\HandlerTestTrait;
use MediaWiki\Tests\Unit\Permissions\MockAuthorityTrait;
use ProfessionalWiki\PageApprovals\EntryPoints\REST\ApprovePageApi;
use ProfessionalWiki\PageApprovals\Tests\PageApprovalsIntegrationTest;

/**
 * @covers \ProfessionalWiki\PageApprovals\EntryPoints\REST\ApprovePageApi
 * @group database
 */
class ApprovePageApiTest extends PageApprovalsIntegrationTest {
	use HandlerTestTrait;
	use MockAuthorityTrait;

	public function testApprovePageHappyPath(): void {
		$response = $this->executeHandler(
			$this->newApprovePageApi(),
			$this->createValidRequestData( 1 )
		);

		$this->assertSame( 200, $response->getStatusCode() );
		$this->assertSame( '{ pageId: 1 }', $response->getBody()->getContents() );
	}

	private function newApprovePageApi(): ApprovePageApi {
		return new ApprovePageApi();
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