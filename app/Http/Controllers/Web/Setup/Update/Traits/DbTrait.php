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

namespace App\Http\Controllers\Web\Setup\Update\Traits;

use App\Helpers\DotenvEditor;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

trait DbTrait
{
	/**
	 * Update files & Upgrade the database
	 *
	 * @param $lastVersion
	 * @param $currentVersion
	 * @return void
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	private function applyUpdateChanges($lastVersion, $currentVersion): void
	{
		$migrationFilesPath = str(database_path('updates'))->finish(DIRECTORY_SEPARATOR)->toString();
		$updatesFilesPaths = $this->getUpdatesFilesPaths($migrationFilesPath);
		
		// Upgrade the website database version by version
		foreach ($updatesFilesPaths as $version => $updateFile) {
			// Load and Apply migration files of the "iterated versions",
			// that are greater than the "website current version" && are lower than or equal to the "app's latest version"
			if (version_compare($version, $currentVersion, '>') && version_compare($version, $lastVersion, '<=')) {
				
				// Load and apply update migration
				if (File::exists($updateFile)) {
					require_once($updateFile);
				}
				
			}
		}
	}
	
	/**
	 * Get updates files paths (sorted chronologically)
	 *
	 * @param $migrationFilesPath
	 * @return array
	 */
	private function getUpdatesFilesPaths($migrationFilesPath): array
	{
		// Get all updates files recursively
		$versionsDirsPaths = array_filter(recursiveGlob($migrationFilesPath . '*update-*.php'), 'is_file');
		
		// Semver pattern
		$versionPattern = '^\d+\.\d+\.\d+$';
		
		$tmpArray = [];
		foreach ($versionsDirsPaths as $path) {
			// Get the iterated version
			$version = getUpdateFileVersion($path);
			
			// Check the semver format
			if (!preg_match('#' . $versionPattern . '#', $version)) {
				continue;
			}
			
			$tmpArray[$version] = $path;
		}
		
		if (empty($tmpArray)) {
			return $tmpArray;
		}
		
		// Sort versions chronologically
		$versions = array_keys($tmpArray);
		usort($versions, 'version_compare');
		
		// Get versions paths sorted (chronologically)
		$array = [];
		foreach ($versions as $version) {
			if (!isset($tmpArray[$version])) {
				continue;
			}
			
			$array[$version] = $tmpArray[$version];
		}
		
		return $array;
	}
	
	/**
	 * Check if all login columns are up-to-date in the database
	 *
	 * Note:
	 * This concerns only the important database structure (that can make fail the upgrades).
	 * Check out the concerned versions files.
	 *
	 * @return bool
	 */
	private function areDatabaseUpToDate(): bool
	{
		$databaseColumnsAreUpToDate = true;
		
		$allIsUpToDate = version_compare(getLatestVersion(), getCurrentVersion(), '<=');
		
		// v9.0.0
		$isCurrentVersionAffected = version_compare(getCurrentVersion(), '9.0.0', '<');
		if (!$allIsUpToDate && $isCurrentVersionAffected) {
			$databaseColumnsAreUpToDate = Schema::hasTable('personal_access_tokens');
		}
		
		// v12.0.0
		if ($databaseColumnsAreUpToDate) {
			$isCurrentVersionAffected = version_compare(getCurrentVersion(), '12.0.0', '<');
			if (!$allIsUpToDate && $isCurrentVersionAffected) {
				$databaseColumnsAreUpToDate = (
					Schema::hasColumn('users', 'email_verified_at')
					&& Schema::hasColumn('users', 'phone_verified_at')
				);
			}
		}
		
		return $databaseColumnsAreUpToDate;
	}
	
	/**
	 * Check if Admin User(s) can be found
	 *
	 * @return bool
	 */
	private function isAdminUserCanBeFound(): bool
	{
		$adminUserFound = true;
		
		$usersTable = (new User())->getTable();
		
		try {
			$firstUser = DB::table($usersTable)->orderBy('id')->first();
			if (!empty($firstUser)) {
				$admins = User::permission(Permission::getStaffPermissions())->get();
				if ($admins->count() > 0) {
					$adminsIds = $admins->keyBy('id')->keys()->toArray();
					if (!auth()->check()) {
						if (!in_array($firstUser->id, $adminsIds)) {
							$adminUserFound = false;
						}
					}
				} else {
					$adminUserFound = false;
				}
			}
		} catch (\Throwable $e) {
			$adminUserFound = false;
		}
		
		return $adminUserFound;
	}
	
	/**
	 * Fix Admin User Permissions
	 */
	private function fixAdminUserPermissions(): void
	{
		$usersTable = (new User())->getTable();
		$aclTableNames = config('permission.table_names');
		
		$firstUser = DB::table($usersTable)->orderBy('id')->first();
		if (!empty($firstUser)) {
			$brokenMasterAdmin = DB::table($usersTable)->where('id', $firstUser->id)->whereNull('is_admin')->first();
			if (!empty($brokenMasterAdmin)) {
				DB::table($usersTable)->where('id', '!=', $brokenMasterAdmin->id)->update(['is_admin' => 0]);
				DB::table($usersTable)->where('id', $brokenMasterAdmin->id)->update(['is_admin' => 1]);
				
				Schema::disableForeignKeyConstraints();
				if (isset($aclTableNames['permissions'])) {
					DB::table($aclTableNames['permissions'])->truncate();
				}
				if (isset($aclTableNames['model_has_roles'])) {
					DB::table($aclTableNames['model_has_roles'])->truncate();
				}
				Schema::enableForeignKeyConstraints();
			}
		}
	}
	
	/**
	 * Set database config if missing
	 *
	 * @return void
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	private function setDatabaseConfigIfMissing(): void
	{
		$doesDbTablesPrefixExist = DotenvEditor::keyExists('DB_TABLES_PREFIX');
		
		if (!$doesDbTablesPrefixExist && DotenvEditor::keyExists('DB_PREFIX')) {
			$dbTablesPrefix = DotenvEditor::getValue('DB_PREFIX');
			DotenvEditor::setKey('DB_TABLES_PREFIX', $dbTablesPrefix);
			DotenvEditor::deleteKey('DB_PREFIX');
			DotenvEditor::save();
			
			$defaultConnection = config('database.default');
			config()->set('database.connections.' . $defaultConnection . '.prefix', $dbTablesPrefix);
		}
	}
}
