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

use App\Models\Currency;

class CurrencyObserver
{
	/**
	 * Listen to the Entry saved event.
	 *
	 * @param Currency $currency
	 * @return void
	 */
	public function saved(Currency $currency)
	{
		// Removing Entries from the Cache
		$this->clearCache($currency);
	}
	
	/**
	 * Listen to the Entry deleted event.
	 *
	 * @param Currency $currency
	 * @return void
	 */
	public function deleted(Currency $currency)
	{
		// Removing Entries from the Cache
		$this->clearCache($currency);
	}
	
	/**
	 * Removing the Entity's Entries from the Cache
	 *
	 * @param $currency
	 * @return void
	 */
	private function clearCache($currency): void
	{
		try {
			cache()->flush();
		} catch (\Exception $e) {}
	}
}
