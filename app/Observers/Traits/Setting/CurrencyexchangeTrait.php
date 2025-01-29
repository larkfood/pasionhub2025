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

namespace App\Observers\Traits\Setting;

use App\Models\Currency;
use App\Providers\AppService\ConfigTrait\CurrencyexchangeConfig;
use Illuminate\Support\Facades\DB;

trait CurrencyexchangeTrait
{
	use CurrencyexchangeConfig;
	
	/**
	 * Updating
	 *
	 * @param $setting
	 * @param $original
	 * @return void
	 */
	public function currencyexchangeUpdating($setting, $original)
	{
		// If the Currency Exchange driver is changed, then clear existing rates
		if (is_array($setting->value) && array_key_exists('driver', $setting->value)) {
			$origDriver = $original['value']['driver'] ?? null;
			$driver = $setting->value['driver'] ?? null;
			
			$isDriverChanged = ($driver != $origDriver);
			if ($isDriverChanged) {
				$defaultCurrencyBase = config('currencyexchange.drivers.' . $driver . '.currencyBase');
				$currencyBase = $setting->value[$driver . '_base'] ?? $defaultCurrencyBase;
				
				$origDefaultCurrencyBase = config('currencyexchange.drivers.' . $origDriver . '.currencyBase');
				$origCurrencyBase = $original['value'][$origDriver . '_base'] ?? $origDefaultCurrencyBase;
				
				$isCurrencyBaseChanged = ($currencyBase != $origCurrencyBase);
				if ($isCurrencyBaseChanged) {
					$affected = DB::table((new Currency)->getTable())->update(['rate' => null]);
				}
			}
		}
	}
	
	/**
	 * Saved
	 *
	 * @param $setting
	 */
	public function currencyexchangeSaved($setting): void
	{
		try {
			cache()->forget('update.currencies.rates');
		} catch (\Exception $e) {
		}
	}
}
