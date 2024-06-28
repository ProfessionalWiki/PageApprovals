<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Tests;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\PageApprovals\PageApprovals;

/**
 * @covers \ProfessionalWiki\PageApprovals\PageApprovals
 */
class PageApprovalsTest extends TestCase {

	public function testGetInstanceIsSingleton(): void {
		$this->assertSame( PageApprovals::getInstance(), PageApprovals::getInstance() );
	}

}
