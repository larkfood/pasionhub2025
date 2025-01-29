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

use App\Helpers\Files\Storage\StorageDisk;
use App\Models\City;
use App\Models\Country;
use App\Models\Post;
use App\Models\SubAdmin1;
use App\Models\SubAdmin2;
use Illuminate\Support\Facades\File;

class CountryObserver
{
	/**
	 * Listen to the Entry deleting event.
	 *
	 * @param Country $country
	 * @return bool
	 */
	public function deleting(Country $country): bool
	{
		if (!isset($country->code)) return false;
		
		// Storage Disk Init.
		$disk = StorageDisk::getDisk();
		
		// Cannot delete the current country when the Domain Mapping plugin is installed
		if (config('plugins.domainmapping.installed')) {
			if (strtolower($country->code) == strtolower(config('settings.localization.default_country_code'))) {
				$msg = trans('admin.Cannot delete the current country when the Domain Mapping plugin is installed');
				notification($msg, 'error');
				
				return false;
			}
		}
		
		// Remove background_image_path files (if exists)
		if (!empty($country->background_image_path)) {
			if (
				!str_contains($country->background_image_path, config('larapen.media.picture'))
				&& $disk->exists($country->background_image_path)
			) {
				$disk->delete($country->background_image_path);
			}
		}
		
		// Delete all SubAdmin1
		$admin1s = SubAdmin1::inCountry($country->code);
		if ($admin1s->count() > 0) {
			foreach ($admin1s->cursor() as $admin1) {
				$admin1->delete();
			}
		}
		
		// Delete all SubAdmin2
		$admin2s = SubAdmin2::inCountry($country->code);
		if ($admin2s->count() > 0) {
			foreach ($admin2s->cursor() as $admin2) {
				$admin2->delete();
			}
		}
		
		// Delete all Cities
		$cities = City::inCountry($country->code);
		if ($cities->count() > 0) {
			foreach ($cities->cursor() as $city) {
				$city->delete();
			}
		}
		
		// Delete all Posts
		$posts = Post::inCountry($country->code);
		if ($posts->count() > 0) {
			foreach ($posts->cursor() as $post) {
				$post->delete();
			}
		}
		
		if (config('plugins.domainmapping.installed')) {
			try {
				$domain = \extras\plugins\domainmapping\app\Models\Domain::where('country_code', '=', $country->code)->first();
				if (!empty($domain)) {
					$domain->delete();
				}
			} catch (\Throwable $e) {
			}
		}
		
		return true;
	}
	
	/**
	 * Listen to the Entry saved event.
	 *
	 * @param Country $country
	 * @return void
	 */
	public function saved(Country $country)
	{
		// Remove the robots.txt file
		$this->removeRobotsTxtFile();
		
		// Removing Entries from the Cache
		$this->clearCache($country);
	}
	
	/**
	 * Listen to the Entry deleted event.
	 *
	 * @param Country $country
	 * @return void
	 */
	public function deleted(Country $country)
	{
		// Remove the robots.txt file
		$this->removeRobotsTxtFile();
		
		// Removing Entries from the Cache
		$this->clearCache($country);
	}
	
	/**
	 * Removing the Entity's Entries from the Cache
	 *
	 * @param $country
	 * @return void
	 */
	private function clearCache($country): void
	{
		try {
			cache()->flush();
		} catch (\Exception $e) {
		}
	}
	
	/**
	 * Remove the robots.txt file (It will be re-generated automatically)
	 *
	 * @return void
	 */
	private function removeRobotsTxtFile(): void
	{
		$robotsFile = public_path('robots.txt');
		if (File::exists($robotsFile)) {
			File::delete($robotsFile);
		}
	}
}
