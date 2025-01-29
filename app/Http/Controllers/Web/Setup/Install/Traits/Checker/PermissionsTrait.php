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

namespace App\Http\Controllers\Web\Setup\Install\Traits\Checker;

use Illuminate\Support\Facades\File;

trait PermissionsTrait
{
	/**
	 * @return array
	 */
	protected function getPermissions(): array
	{
		$warning = 'The directory must be writable by the web server (0755).';
		$message = 'The directory is writable with the right permissions.';
		$rWarning = 'The directory must be writable (recursively) by the web server (0755).';
		$rMessage = 'The directory is writable (recursively) with the right permissions.';
		
		$permissions = [
			[
				'type'              => 'permission',
				'name'              => base_path('bootstrap/cache'),
				'required'          => true,
				'isOk'              => (
					file_exists(base_path('bootstrap/cache'))
					&& is_dir(base_path('bootstrap/cache'))
					&& is_writable(base_path('bootstrap/cache'))
					&& getPerms(base_path('bootstrap/cache')) >= 755
				),
				'permanentChecking' => true,
				'warning'           => $warning,
				'success'           => $message,
			],
			[
				'type'              => 'permission',
				'name'              => config_path(),
				'required'          => true,
				'isOk'              => (
					file_exists(config_path())
					&& is_dir(config_path())
					&& is_writable(config_path())
					&& getPerms(config_path()) >= 755
				),
				'permanentChecking' => true,
				'warning'           => $warning,
				'success'           => $message,
			],
			[
				'type'              => 'permission',
				'name'              => public_path(),
				'required'          => true,
				'isOk'              => (
					file_exists(public_path())
					&& is_dir(public_path())
					&& is_writable(public_path())
					&& getPerms(public_path()) >= 755
				),
				'permanentChecking' => true,
				'warning'           => $warning,
				'success'           => $message,
			],
			[
				'type'              => 'permission',
				'name'              => lang_path(),
				'required'          => true,
				'isOk'              => $this->checkResourcesLangPermissions(),
				'permanentChecking' => true,
				'warning'           => $rWarning,
				'success'           => $rMessage,
			],
			[
				'type'              => 'permission',
				'name'              => storage_path(),
				'required'          => true,
				'isOk'              => $this->checkStoragePermissions(),
				'permanentChecking' => true,
				'warning'           => $rWarning,
				'success'           => $rMessage,
			],
		];
		
		// Check and load Watermark plugin
		if (plugin_exists('watermark')) {
			$watermarkPath = plugin_path('watermark');
			$permissions[] = [
				'type'              => 'permission',
				'name'              => $watermarkPath,
				'required'          => false,
				'isOk'              => (
					file_exists($watermarkPath)
					&& is_dir($watermarkPath)
					&& is_writable($watermarkPath)
					&& getPerms($watermarkPath) >= 755
				),
				'permanentChecking' => false,
				'warning'           => $warning,
				'success'           => $message,
			];
		}
		
		return $permissions;
	}
	
	// PRIVATE
	
	/**
	 * @return bool
	 */
	private function checkResourcesLangPermissions(): bool
	{
		$permissions = $this->getResourcesLangPermissions();
		
		$success = true;
		foreach ($permissions as $path => $permission) {
			if (!$permission) {
				$success = false;
			}
		}
		
		return $success;
	}
	
	/**
	 * @return bool
	 */
	private function checkStoragePermissions(): bool
	{
		$permissions = $this->getStoragePermissions();
		
		$success = true;
		foreach ($permissions as $path => $permission) {
			if (!$permission) {
				$success = false;
			}
		}
		
		return $success;
	}
	
	/**
	 * @return array
	 */
	private function getResourcesLangPermissions(): array
	{
		$resourceLangPath = str(lang_path())->finish(DIRECTORY_SEPARATOR)->toString();
		$paths = array_filter(glob($resourceLangPath . '*'), 'is_dir');
		
		$permissions = [];
		
		// Insert the $resourceLangPath at the beginning of the array paths
		array_unshift($paths, $resourceLangPath);
		
		foreach ($paths as $fullPath) {
			// Create path if it does not exist
			if (!File::exists($fullPath)) {
				try {
					File::makeDirectory($fullPath, 0777, true);
				} catch (\Throwable $e) {
				}
			}
			
			// Get the path permission
			$permissions[$fullPath] = (
				file_exists($fullPath)
				&& is_dir($fullPath)
				&& is_writable($fullPath)
				&& getPerms($fullPath) >= 755
			);
		}
		
		return $permissions;
	}
	
	/**
	 * @return array
	 */
	private function getStoragePermissions(): array
	{
		$paths = [
			'/',
			'app/public/app',
			'app/public/app/categories/custom',
			'app/public/app/logo',
			'app/public/app/page',
			'app/public/files',
			'app/public/temporary',
			'framework',
			'framework/cache',
			'framework/plugins',
			'framework/sessions',
			'framework/views',
			'logs',
		];
		
		$permissions = [];
		
		foreach ($paths as $path) {
			$fullPath = storage_path($path);
			
			// Create path if it does not exist
			if (!File::exists($fullPath)) {
				try {
					File::makeDirectory($fullPath, 0777, true);
				} catch (\Throwable $e) {
				}
			}
			
			// Get the path permission
			$permissions[$fullPath] = (
				file_exists($fullPath)
				&& is_dir($fullPath)
				&& is_writable($fullPath)
				&& getPerms($fullPath) >= 755
			);
		}
		
		return $permissions;
	}
}
