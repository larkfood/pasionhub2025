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

class RoleObserver
{
	/**
	 * Listen to the Entry deleting event.
	 *
	 * @param Role $role
	 * @return bool
	 */
	public function deleting(Role $role)
	{
		// Check if default permission exist, to prevent recursion of the deletion.
		if (Permission::checkDefaultPermissions()) {
			$superAdminRole = Role::getSuperAdminRole();
			
			if (strtolower($role->name) == strtolower($superAdminRole)) {
				$msg = trans('admin.You cannot delete the Super Admin role');
				notification($msg, 'warning');
				
				// Since Laravel detaches all pivot entries before starting deletion,
				// Re-give the Super Admin permissions to the role.
				$role->syncPermissions(Permission::getSuperAdminPermissions());
				
				return false;
			}
		}
	}
	
	/**
	 * Listen to the Entry saved event.
	 *
	 * @param Role $role
	 * @return void
	 */
	public function saved(Role $role)
	{
		// Removing Entries from the Cache
		$this->clearCache($role);
	}
	
	/**
	 * Listen to the Entry deleted event.
	 *
	 * @param Role $role
	 * @return void
	 */
	public function deleted(Role $role)
	{
		// Removing Entries from the Cache
		$this->clearCache($role);
	}
	
	/**
	 * Removing the Entity's Entries from the Cache
	 *
	 * @param $role
	 * @return void
	 */
	private function clearCache($role): void
	{
		try {
			cache()->flush();
		} catch (\Exception $e) {
		}
	}
}
