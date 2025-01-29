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

namespace App\Http\Middleware\InputRequest;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

trait XssProtection
{
	/**
	 * The following method loops through all request input and strips out all tags from
	 * the request. This to ensure that users are unable to set ANY HTML within the form
	 * submissions, but also cleans up input.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\Request
	 */
	protected function applyXssProtection(Request $request): Request
	{
		// Exception for Install & Upgrade Routes
		if (isFromInstallOrUpgradeProcess()) {
			return $request;
		}
		
		$request = $this->convertZeroToNull($request);
		
		if (request()->segment(1) == admin_uri()) {
			try {
				$aclTableNames = config('permission.table_names');
				if (isset($aclTableNames['permissions'])) {
					// Check if the 'permissions' table exists
					$cacheId = 'permissionsTableExists';
					$cacheExpiration = (int)config('settings.optimization.cache_expiration', 86400) * 5;
					$permissionsTableExists = cache()->remember($cacheId, $cacheExpiration, function () use ($aclTableNames) {
						return Schema::hasTable($aclTableNames['permissions']);
					});
					
					if (!$permissionsTableExists) {
						return $request;
					}
				}
			} catch (\Throwable $e) {
				return $request;
			}
			
			$guard = getAuthGuard();
			$authUser = auth($guard)->check() ? auth($guard)->user() : null;
			
			if (doesUserHavePermission($authUser, Permission::getStaffPermissions())) {
				return $request;
			}
		}
		
		// Get all fields values
		$inputs = $request->all();
		
		// Apply one (or more) action(s) recursively to every field of the array
		array_walk_recursive($inputs, function (&$value, $key) use ($request) {
			if (!is_string($value)) return;
			
			// Sanitize input to prevent XSS attacks and remove malicious characters
			// Except the: "description" field
			if ($key != 'description') {
				$value = sanitizeInput($value);
			}
			
			// Remove 4(+)-byte characters (If it is not enabled)
			$value = stripUtf8mb4CharsIfNotEnabled($value);
		});
		
		// Replace the fields values
		$request->merge($inputs);
		
		return $request;
	}
	
	/**
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\Request
	 */
	private function convertZeroToNull(Request $request): Request
	{
		// parent_id
		if ($request->filled('parent_id')) {
			$parentId = $request->input('parent_id');
			$parentId = !empty($parentId) ? $parentId : null;
			$request->request->set('parent_id', $parentId);
		}
		
		return $request;
	}
}
