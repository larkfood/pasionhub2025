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

use App\Models\City;
use App\Models\Post;

class CityObserver
{
	/**
	 * Listen to the Entry deleting event.
	 *
	 * @param City $city
	 * @return void
	 */
	public function deleting(City $city)
	{
		// Get Posts
		$posts = Post::where('city_id', $city->id);
		if ($posts->count() > 0) {
			foreach ($posts->cursor() as $post) {
				$post->delete();
			}
		}
	}
	
	/**
	 * Listen to the Entry updated event.
	 *
	 * @param City $city
	 * @return void
	 */
	public function updated(City $city)
	{
		// Update all the City's Posts
		$posts = Post::where('city_id', $city->id);
		if ($posts->count() > 0) {
			foreach ($posts->cursor() as $post) {
				$post->lon = $city->longitude;
				$post->lat = $city->latitude;
				$post->save();
			}
		}
	}
	
	/**
	 * Listen to the Entry saved event.
	 *
	 * @param City $city
	 * @return void
	 */
	public function saved(City $city)
	{
		// Removing Entries from the Cache
		$this->clearCache($city);
	}
	
	/**
	 * Listen to the Entry deleted event.
	 *
	 * @param City $city
	 * @return void
	 */
	public function deleted(City $city)
	{
		// Removing Entries from the Cache
		$this->clearCache($city);
	}
	
	/**
	 * Removing the Entity's Entries from the Cache
	 *
	 * @param $city
	 * @return void
	 */
	private function clearCache($city): void
	{
		try {
			cache()->flush();
		} catch (\Exception $e) {
		}
	}
}
