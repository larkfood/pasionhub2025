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

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

trait CleanUpTrait
{
	/**
	 * Clear Cache
	 *
	 * @return void
	 */
	private function clearCache(): void
	{
		$this->removeRobotsTxtFile();
		
		// Clear Laravel data cache
		Artisan::call('cache:clear');
		sleep(2);
		
		// Clear Laravel view cache
		Artisan::call('view:clear');
		sleep(1);
		
		File::delete(File::glob(storage_path('logs') . DIRECTORY_SEPARATOR . '*.log'));
	}
	
	/**
	 * Remove the robots.txt file (It will be re-generated automatically)
	 *
	 * @return void
	 */
	private function removeRobotsTxtFile(): void
	{
		$robotsFilePath = public_path('robots.txt');
		if (File::exists($robotsFilePath)) {
			File::delete($robotsFilePath);
		}
	}
	
	/**
	 * Clear Laravel data cache
	 *
	 * @param int|null $sleepTime
	 * @return void
	 */
	private function clearDataCache(?int $sleepTime = 0): void
	{
		$cacheDir = storage_path('framework/cache/data/');
		$this->clearLaravelCache('cache:clear', $cacheDir, $sleepTime);
	}
	
	/**
	 * Clear Laravel view cache
	 *
	 * @param int|null $sleepTime
	 * @return void
	 */
	private function clearViewCache(?int $sleepTime = 0): void
	{
		$cacheDir = storage_path('framework/views/');
		$this->clearLaravelCache('view:clear', $cacheDir, $sleepTime);
	}
	
	/**
	 * @param $cmd
	 * @param $cacheDir
	 * @param int|null $sleepTime - Sleeping time (in seconds)
	 * @return void
	 */
	private function clearLaravelCache($cmd, $cacheDir, ?int $sleepTime = 0): void
	{
		try {
			if (File::isDirectory($cacheDir)) {
				// Remove the cache directory (Using a fast method or algorithm)
				system('rm -rf ' . escapeshellarg($cacheDir));
				if (is_int($sleepTime) && $sleepTime > 0) {
					sleep($sleepTime);
				}
			}
			
			// Re-create the cache directory (If not exists)
			$this->createCacheDir($cacheDir);
		} catch (\Throwable $e) {
			// Re-create the cache directory (If not exists)
			$result = $this->createCacheDir($cacheDir);
			if (!$result) {
				Artisan::call($cmd);
				if (is_int($sleepTime) && $sleepTime > 0) {
					sleep($sleepTime);
				}
			}
		}
	}
	
	/**
	 * Re-create the cache directory (If not exists)
	 *
	 * @param $cacheDir
	 * @return bool
	 */
	private function createCacheDir($cacheDir): bool
	{
		$result = false;
		
		// Re-create the cache directory (If not exists)
		clearstatcache(); // <= Clears file status cache
		if (!File::isDirectory($cacheDir)) {
			File::makeDirectory($cacheDir, 0777, false, true);
			$result = true;
		}
		
		// Check if the .gitignore file exists in the root directory to prevent its removal
		clearstatcache(); // <= Clears file status cache
		$gitIgnoreFilePath = $cacheDir . '.gitignore';
		if (!File::exists($gitIgnoreFilePath)) {
			$content = '*' . "\n";
			$content .= '!.gitignore' . "\n";
			File::put($gitIgnoreFilePath, $content);
		}
		
		return $result;
	}
}
