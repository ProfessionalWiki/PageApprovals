<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals;

use PHPUnit\Framework\TestCase;

class PageApprovalsTest extends TestCase {

	public function testGetInstanceReturnsNewInstance(): void {
		$instance = PageApprovals::getInstance();

		$this->assertInstanceOf( PageApprovals::class, $instance );
	}

	public function testGetInstanceReturnsExistingInstance(): void {
		$instance1 = PageApprovals::getInstance();
		$instance2 = PageApprovals::getInstance();

		$this->assertSame( $instance1, $instance2 );
	}

}
