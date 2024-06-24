<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\PageApprovals\Application;

use User;

interface UsersLookupInterface {

	/**
	 * @return User[]
	 */
	public function getAllUsers(): array;

}
