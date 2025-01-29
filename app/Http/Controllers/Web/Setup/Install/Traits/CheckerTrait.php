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

namespace App\Http\Controllers\Web\Setup\Install\Traits;

use App\Http\Controllers\Web\Setup\Install\Traits\Checker\ComponentsTrait;
use App\Http\Controllers\Web\Setup\Install\Traits\Checker\PermissionsTrait;

trait CheckerTrait
{
	use ComponentsTrait, PermissionsTrait;
	
	/**
	 * Is Manual Checking Allowed
	 *
	 * @return bool
	 */
	protected function isManualCheckingAllowed(): bool
	{
		return (request()->has('mode') && request()->input('mode') == 'manual');
	}
	
	/**
	 * @return bool
	 */
	protected function checkComponents(): bool
	{
		$components = $this->getComponents();
		
		$success = true;
		foreach ($components as $component) {
			if ($component['required'] && !$component['isOk']) {
				$success = false;
			}
		}
		
		return $success;
	}
	
	/**
	 * @return bool
	 */
	protected function checkPermissions(): bool
	{
		$permissions = $this->getPermissions();
		
		$success = true;
		foreach ($permissions as $permission) {
			if ($permission['required'] && !$permission['isOk']) {
				$success = false;
			}
		}
		
		return $success;
	}
}
