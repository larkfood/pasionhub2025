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

namespace App\Http\Controllers\Web\Admin;

// Increase the server resources
$iniConfigFile = __DIR__ . '/../../../Helpers/Functions/ini.php';
if (file_exists($iniConfigFile)) {
	include_once $iniConfigFile;
}

use App\Helpers\DBTool\DBEncoding;
use App\Helpers\Files\Tools\FileStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class ActionController extends Controller
{
	/**
	 * Clear Cache
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function clearCache(): \Illuminate\Http\RedirectResponse
	{
		$errorFound = false;
		
		// For LaraClassifier
		if (session()->has('curr')) {
			session()->forget('curr');
		}
		
		// Removing all the cache
		try {
			cache()->flush();
		} catch (\Throwable $e) {
			notification($e->getMessage(), 'error');
			$errorFound = true;
		}
		
		// Some time of pause
		// sleep(2);
		
		// Removing all Views Cache
		try {
			Artisan::call('view:clear');
		} catch (\Throwable $e) {
			notification($e->getMessage(), 'error');
			$errorFound = true;
		}
		
		// Some time of pause
		sleep(1);
		
		// Removing all Logs
		try {
			File::delete(File::glob(storage_path('logs') . DIRECTORY_SEPARATOR . '*.log'));
			
			$debugBarPath = storage_path('debugbar');
			if (File::exists($debugBarPath)) {
				File::delete(File::glob($debugBarPath . DIRECTORY_SEPARATOR . '*.json'));
			}
		} catch (\Throwable $e) {
			notification($e->getMessage(), 'error');
			$errorFound = true;
		}
		
		// Check if error occurred
		if (!$errorFound) {
			$message = trans('admin.The cache was successfully dumped');
			notification($message, 'success');
		}
		
		return redirect()->back();
	}
	
	/**
	 * Clear Images Thumbnails
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function clearImagesThumbnails(): \Illuminate\Http\RedirectResponse
	{
		$errorFound = false;
		
		// Get the upload path
		$uploadPaths = [
			'app' . DIRECTORY_SEPARATOR,
			'files' . DIRECTORY_SEPARATOR,    // New path
			'pictures' . DIRECTORY_SEPARATOR, // Old path
		];
		
		foreach ($uploadPaths as $uploadPath) {
			if (!$this->disk->exists($uploadPath)) {
				continue;
			}
			
			if (!$this->disk->directoryExists($uploadPath)) {
				continue;
			}
			
			// Removing all the images' thumbnails
			try {
				$directoryName = 'thumbnails';
				FileStorage::removeSubDirRecursive($this->disk, $uploadPath, $directoryName);
			} catch (\Throwable $e) {
				notification($e->getMessage(), 'error');
				$errorFound = true;
				break;
			}
			
			// Removing all the images' thumbnails
			try {
				$pattern = '~thumb-.*\.[a-z]*~ui';
				FileStorage::removeMatchedFilesRecursive($this->disk, $uploadPath, $pattern);
			} catch (\Throwable $e) {
				notification($e->getMessage(), 'error');
				$errorFound = true;
				break;
			}
			
			// Don't create '.gitignore' file or remove empty directories in the '/storage/app/public/app/' dir
			$appUploadedFilesPath = DIRECTORY_SEPARATOR
				. 'app' . DIRECTORY_SEPARATOR
				. 'public' . DIRECTORY_SEPARATOR
				. 'app' . DIRECTORY_SEPARATOR;
			
			if (!str_contains($appUploadedFilesPath, $uploadPath)) {
				// Removing all empty subdirectories (except the root directory)
				try {
					// Check if the .gitignore file exists in the root directory to prevent its removal
					if (!$this->disk->exists($uploadPath . '.gitignore')) {
						$content = '*' . "\n"
							. '!.gitignore' . "\n";
						$this->disk->put($uploadPath . '.gitignore', $content);
					}
					
					// Removing all empty subdirectories
					FileStorage::removeEmptySubDirs($this->disk, $uploadPath);
				} catch (\Throwable $e) {
					notification($e->getMessage(), 'error');
					$errorFound = true;
					break;
				}
			}
		}
		
		// Removing all the cache
		try {
			cache()->flush();
		} catch (\Throwable $e) {
			notification($e->getMessage(), 'error');
			$errorFound = true;
		}
		
		// Check if error occurred
		if (!$errorFound) {
			$message = trans('admin.action_performed_successfully');
			notification($message, 'success');
		}
		
		return redirect()->back();
	}
	
	/**
	 * Put & Back to Maintenance Mode
	 *
	 * @param $mode ('down' or 'up')
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function maintenance($mode, Request $request): \Illuminate\Http\RedirectResponse
	{
		$messageFilePath = storage_path('framework/down-message');
		
		// Create or delete maintenance message
		if ($mode == 'down') {
			$rules = ['message' => ['nullable', 'string', 'max:500']];
			$validated = $request->validate($rules);
			$message = $validated['message'] ?? null;
			
			// Save the maintenance mode message
			$data = ['message' => $message];
			File::put($messageFilePath, json_encode($data));
		} else {
			if (File::exists($messageFilePath)) {
				File::delete($messageFilePath);
			}
		}
		
		$errorFound = false;
		
		// Go to maintenance with DOWN status
		try {
			Artisan::call($mode);
		} catch (\Throwable $e) {
			notification($e->getMessage(), 'error');
			$errorFound = true;
		}
		
		// Check if error occurred
		if (!$errorFound) {
			$message = ($mode == 'down')
				? trans('admin.The website has been putted in maintenance mode')
				: trans('admin.The website has left the maintenance mode');
			notification($message, 'success');
		}
		
		return redirect()->back();
	}
	
	/**
	 * Update the database connection charset and collation
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function updateDBConnectionCharsetAndCollation(): \Illuminate\Http\RedirectResponse
	{
		$errorFound = false;
		
		// Run the Cron Job command manually
		try {
			DBEncoding::tryToFixConnectionCharsetAndCollation();
		} catch (\Throwable $e) {
			notification($e->getMessage(), 'error');
			$errorFound = true;
		}
		
		// Check if error occurred
		if (!$errorFound) {
			$message = trans('admin.database_charset_collation_updated_successfully');
			notification($message, 'success');
		}
		
		return redirect()->back();
	}
	
	/**
	 * Test the Listings Cleaner Command
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function callAdsCleanerCommand(): \Illuminate\Http\RedirectResponse
	{
		$errorFound = false;
		
		// Run the Cron Job command manually
		try {
			Artisan::call('listings:purge');
		} catch (\Throwable $e) {
			notification($e->getMessage(), 'error');
			$errorFound = true;
		}
		
		// Check if error occurred
		if (!$errorFound) {
			$message = trans('admin.The Listings Clear command was successfully run');
			notification($message, 'success');
		}
		
		return redirect()->back();
	}
}
