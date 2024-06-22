<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Tests\Adapters;

use IDatabase;
use MediaWikiIntegrationTestCase;
use ProfessionalWiki\PageApprovals\Adapters\DatabaseHtmlRepository;
use ProfessionalWiki\PageApprovals\Application\HtmlRepository;
use Wikimedia\Timestamp\ConvertibleTimestamp;

/**
 * @covers \ProfessionalWiki\PageApprovals\Adapters\DatabaseHtmlRepository
 * @group Database
 */
class DatabaseHtmlRepositoryTest extends MediaWikiIntegrationTestCase {

	protected function setUp(): void {
		parent::setUp();
		$this->tablesUsed[] = 'approved_html';
	}

	public function testReturnsNullForNonexistentPage(): void {
		$repository = $this->newRepository();

		$repository->saveApprovedHtml( 1, 'first' );
		$repository->saveApprovedHtml( 2, 'second' );

		$this->assertNull( $this->newRepository()->getApprovedHtml( pageId: 404 ) );
	}

	private function newRepository(
		IDatabase $db = null,
	): HtmlRepository {
		return new DatabaseHtmlRepository(
			$db ?? $this->db,
		);
	}

	public function testSaveAndRetrieveHtml(): void {
		$repository = $this->newRepository();

		$pageId = 2;
		$html = '<p>Test content</p>';

		$repository->saveApprovedHtml( 1, 'wrong' );
		$repository->saveApprovedHtml( $pageId, $html );
		$repository->saveApprovedHtml( 3, 'also wrong' );

		$this->assertSame( $html, $repository->getApprovedHtml( $pageId ) );
	}

	public function testCanUpdateHtml(): void {
		$repository = $this->newRepository();

		$repository->saveApprovedHtml( 1, 'first' );
		$repository->saveApprovedHtml( 1, 'second' );

		$this->assertSame( 'second', $repository->getApprovedHtml( 1 ) );
	}

	public function testTimestampIsSetCorrectly(): void {
		$fakeTime = '20230615120000'; // 2023-06-15 12:00:00
		ConvertibleTimestamp::setFakeTime( $fakeTime );

		$this->newRepository()->saveApprovedHtml( 1, '<p>Test content</p>' );

		$this->assertSame(
			$fakeTime,
			$this->getTimestampFromDatabase( 1 )
		);

		ConvertibleTimestamp::setFakeTime( false ); // Reset fake time
	}

	private function getTimestampFromDatabase( int $pageId ): string {
		return $this->db->selectField(
			'approved_html',
			'ah_timestamp',
			[ 'ah_page_id' => $pageId ],
			__METHOD__
		);
	}

}
