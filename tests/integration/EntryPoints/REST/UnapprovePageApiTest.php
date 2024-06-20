<?php

namespace ProfessionalWiki\PageApprovals\Tests\EntryPoints\REST;

use MediaWiki\MediaWikiServices;
use MediaWiki\Rest\RequestData;
use MediaWiki\Tests\Rest\Handler\HandlerTestTrait;
use MediaWiki\Tests\Unit\Permissions\MockAuthorityTrait;
use ProfessionalWiki\PageApprovals\EntryPoints\REST\UnapprovePageApi;
use ProfessionalWiki\PageApprovals\Tests\PageApprovalsIntegrationTest;
use ProfessionalWiki\PageApprovals\Tests\TestDoubles\FailingApprovalAuthorizer;
use ProfessionalWiki\PageApprovals\Tests\TestDoubles\SucceedingApprovalAuthorizer;
use Title;

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
			$this->createValidRequestData( $this->getIdOfExistingPage( 'Test 1' ) )
		);

		$this->assertSame( 200, $response->getStatusCode() );
	}

	private function newUnapprovePageApi(): UnapprovePageApi {
		return new UnapprovePageApi(
			new SucceedingApprovalAuthorizer()
		);
	}

	private function getIdOfExistingPage( string $titleText ): int {
		$title = Title::newFromText( $titleText );
		$this->editPage( $title, 'Whatever wikitext' );
		return MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $title )->getId();
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
			$this->createValidRequestData( $this->getIdOfExistingPage( 'Test 2' ) )
		);

		$this->assertSame( 403, $response->getStatusCode() );
	}

	private function newUnapprovePageApiWithFailingAuthorizer(): UnapprovePageApi {
		return new UnapprovePageApi(
			new FailingApprovalAuthorizer()
		);
	}

	public function testUnapprovalFailsForMissingPageId(): void {
		$response = $this->executeHandler(
			$this->newUnapprovePageApi(),
			$this->createValidRequestData( 404404404 )
		);

		$this->assertSame( 404, $response->getStatusCode() );
	}

}
