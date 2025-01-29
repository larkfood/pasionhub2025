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

namespace App\Models\Traits;

use App\Helpers\DBTool;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

trait RoleTrait
{
	// ===| ADMIN PANEL METHODS |===
	
	public function updateButton($xPanel = false): string
	{
		$out = '';
		
		if (strtolower($this->name) == strtolower(Role::getSuperAdminRole())) {
			return $out;
		}
		
		$url = admin_url('roles/' . $this->id . '/edit');
		
		$out = '<a href="' . $url . '" class="btn btn-xs btn-primary">';
		$out .= '<i class="fa-regular fa-pen-to-square"></i> ';
		$out .= trans('admin.edit');
		$out .= '</a>';
		
		return $out;
	}
	
	public function deleteButton($xPanel = false): string
	{
		$out = '';
		
		if (strtolower($this->name) == strtolower(Role::getSuperAdminRole())) {
			return $out;
		}
		
		$url = admin_url('roles/' . $this->id);
		
		$out = '<a href="' . $url . '" class="btn btn-xs btn-danger" data-button-type="delete">';
		$out .= '<i class="fa-regular fa-trash-can"></i> ';
		$out .= trans('admin.delete');
		$out .= '</a>';
		
		return $out;
	}
	
	// ===| OTHER METHODS |===
	
	/**
	 * Get Super Admin users role (from DB)
	 *
	 * @return \App\Models\Role|null
	 */
	public static function getSuperAdminRoleFromDb(): ?Role
	{
		try {
			return Role::where('name', Role::getSuperAdminRole())->first();
		} catch (\Throwable $e) {
			return null;
		}
	}
	
	/**
	 * Check Super Admin role
	 * NOTE: Must use try {...} catch {...}
	 *
	 * @return bool
	 */
	public static function checkSuperAdminRole(): bool
	{
		$role = Role::getSuperAdminRoleFromDb();
		
		return !empty($role);
	}
	
	/**
	 * Reset default roles
	 * NOTE: Must use try {...} catch {...}
	 *
	 * @return \App\Models\Role|null
	 */
	public static function resetDefaultRole(): ?Role
	{
		$role = null;
		
		try {
			// Remove all current roles & their relationship
			$roles = Role::all();
			$roles->each(function ($item) {
				if ($item->permissions()) {
					$item->permissions()->detach();
				}
				$item->delete();
			});
			
			// Reset roles table ID auto-increment
			$rolesTable = DBTool::table(config('permission.table_names.roles'));
			DB::statement('ALTER TABLE ' . $rolesTable . ' AUTO_INCREMENT = 1;');
			
			// Get default role
			$defaultRole = Role::getSuperAdminRole();
			
			// Create the default Super Admin role
			$role = Role::firstOrCreate(['name' => $defaultRole]);
		} catch (\Throwable $e) {
		}
		
		return $role;
	}
}
