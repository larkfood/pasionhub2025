<?php
/*
 * LaraClassifier - Classified Ads Web Application
 * Copyright (c) BeDigit. All Rights Reserved
 *
 * Website: https://laraclassifier.com
 * Author: Mayeul Akpovi (BeDigit - https://bedigit.com)
 *
 * LICENSE
 * -------
 * This software is provided under a license agreement and may only be used or copied
 * in accordance with its terms, including the inclusion of the above copyright notice.
 * As this software is sold exclusively on CodeCanyon,
 * please review the full license details here: https://codecanyon.net/licenses/standard
 */

namespace App\Providers\AppService;

use App\Models\Permission;

trait AclSystemTrait
{
	/**
	 * Setup ACL system
	 * Check & Migrate Old admin authentication to ACL system
	 */
	private function setupAclSystem(): void
	{
		if (isAdminPanel()) {
			/*
			 * Check Super Admin permissions
			 * Reset default permissions
			 * Define default super-admin user(s) (If it does not exist)
			 */
			Permission::defineDefaultSuperAdminIfItDoesNotExist();
		}
	}
}
