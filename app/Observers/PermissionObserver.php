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

namespace App\Observers;

use App\Models\Permission;
use App\Models\Role;

class PermissionObserver
{
	/**
	 * Listen to the Entry deleting event.
	 *
	 * @param Permission $permission
	 * @return bool
	 */
	public function deleting(Permission $permission)
	{
		// Check if default permission exist, to prevent recursion of the deletion.
		if (Permission::checkDefaultPermissions()) {
			// Don't delete Super Admin default permissions
			$superAdminPermissions = Permission::getSuperAdminPermissions();
			$superAdminPermissions = collect($superAdminPermissions)
				->map(fn ($item, $key) => strtolower($item))
				->toArray();
			
			if (in_array(strtolower($permission->name), $superAdminPermissions)) {
				$msg = trans('admin.You cannot delete a Super Admin default permission');
				notification($msg, 'warning');
				
				// Since Laravel detaches all pivot entries before starting deletion,
				// Re-assign the permission to the Super Admin role.
				$permission->assignRole(Role::getSuperAdminRole());
				
				return false;
			}
			
			// Don't delete Staff default permissions
			$adminPermissions = Permission::getStaffPermissions();
			$adminPermissions = collect($adminPermissions)
				->map(fn ($item, $key) => strtolower($item))
				->toArray();
			
			if (in_array(strtolower($permission->name), $adminPermissions)) {
				$msg = trans('admin.You cannot delete a staff default permission');
				notification($msg, 'warning');
				
				// Optional
				$permission->assignRole(Role::getSuperAdminRole());
				
				return false;
			}
		}
	}
	
	/**
	 * Listen to the Entry saved event.
	 *
	 * @param Permission $permission
	 * @return void
	 */
	public function saved(Permission $permission)
	{
		// Removing Entries from the Cache
		$this->clearCache($permission);
	}
	
	/**
	 * Listen to the Entry deleted event.
	 *
	 * @param Permission $permission
	 * @return void
	 */
	public function deleted(Permission $permission)
	{
		// Removing Entries from the Cache
		$this->clearCache($permission);
	}
	
	/**
	 * Removing the Entity's Entries from the Cache
	 *
	 * @param $permission
	 * @return void
	 */
	private function clearCache($permission): void
	{
		try {
			cache()->flush();
		} catch (\Exception $e) {
		}
	}
}
