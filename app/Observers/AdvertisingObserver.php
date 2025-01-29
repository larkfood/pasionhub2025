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

use App\Models\Advertising;

class AdvertisingObserver
{
	/**
	 * Listen to the Entry saved event.
	 *
	 * @param Advertising $advertising
	 * @return void
	 */
	public function saved(Advertising $advertising)
	{
		// Removing Entries from the Cache
		$this->clearCache($advertising);
	}
	
	/**
	 * Listen to the Entry deleted event.
	 *
	 * @param Advertising $advertising
	 * @return void
	 */
	public function deleted(Advertising $advertising)
	{
		// Removing Entries from the Cache
		$this->clearCache($advertising);
	}
	
	/**
	 * Removing the Entity's Entries from the Cache
	 *
	 * @param $advertising
	 * @return void
	 */
	private function clearCache($advertising): void
	{
		try {
			cache()->forget('advertising.top');
			cache()->forget('advertising.bottom');
			cache()->forget('advertising.auto');
		} catch (\Exception $e) {}
	}
}
