<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals;

use PHPUnit\Framework\TestCase;

class PageApprovalsTest extends TestCase {

	public function testTests(): void {
		$this->assertTrue( PageApprovals::todo() );
	}

}
