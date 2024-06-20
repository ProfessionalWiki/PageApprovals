<?php

namespace ProfessionalWiki\PageApprovals\Tests\EntryPoints\REST;

use MediaWiki\Rest\RequestData;
use MediaWiki\Tests\Rest\Handler\HandlerTestTrait;
use MediaWiki\Tests\Unit\Permissions\MockAuthorityTrait;
use ProfessionalWiki\PageApprovals\EntryPoints\REST\ApprovePageApi;
use ProfessionalWiki\PageApprovals\Tests\PageApprovalsIntegrationTest;
use ProfessionalWiki\PageApprovals\Tests\TestDoubles\FailingPageApprovalAuthorizer;
use ProfessionalWiki\PageApprovals\Tests\TestDoubles\SucceedingPageApprovalAuthorizer;

/**
 * @covers \ProfessionalWiki\PageApprovals\EntryPoints\REST\ApprovePageApi
 * @group Database
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
		return new ApprovePageApi(
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
			$this->newApprovePageApiWithFailingAuthorizer(),
			$this->createValidRequestData( 1 )
		);

		$this->assertSame( 403, $response->getStatusCode() );
		$this->assertSame( '{ pageId: 1 }', $response->getBody()->getContents() );
	}

	private function newApprovePageApiWithFailingAuthorizer(): ApprovePageApi {
		return new ApprovePageApi(
			new FailingPageApprovalAuthorizer()
		);
	}

}
