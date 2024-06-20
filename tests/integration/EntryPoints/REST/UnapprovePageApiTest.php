<?php

namespace ProfessionalWiki\PageApprovals\Tests\EntryPoints\REST;

use MediaWiki\Rest\RequestData;
use MediaWiki\Tests\Rest\Handler\HandlerTestTrait;
use MediaWiki\Tests\Unit\Permissions\MockAuthorityTrait;
use ProfessionalWiki\PageApprovals\EntryPoints\REST\UnapprovePageApi;
use ProfessionalWiki\PageApprovals\Tests\PageApprovalsIntegrationTest;
use ProfessionalWiki\PageApprovals\Tests\TestDoubles\FailingPageApprovalAuthorizer;
use ProfessionalWiki\PageApprovals\Tests\TestDoubles\SucceedingPageApprovalAuthorizer;

/**
 * @covers \ProfessionalWiki\PageApprovals\EntryPoints\REST\UnapprovePageApi
 * @group Database
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
		return new UnapprovePageApi(
			new SucceedingPageApprovalAuthorizer()
		);
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

	public function testApprovalFailsWithoutPermission(): void {
		$response = $this->executeHandler(
			$this->newUnapprovePageApiWithFailingAuthorizer(),
			$this->createValidRequestData( 1 )
		);

		$this->assertSame( 403, $response->getStatusCode() );
		$this->assertSame( '{ pageId: 1 }', $response->getBody()->getContents() );
	}

	private function newUnapprovePageApiWithFailingAuthorizer(): UnapprovePageApi {
		return new UnapprovePageApi(
			new FailingPageApprovalAuthorizer()
		);
	}

}
