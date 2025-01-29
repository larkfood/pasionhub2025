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

namespace App\Http\Controllers\Web\Setup\Install\Traits;

use App\Helpers\Cookie;
use App\Helpers\GeoIP;

trait ApiTrait
{
	/**
	 * Get the user's country code with his IP address
	 *
	 * @param array|null $defaultDrivers
	 * @return string|null
	 */
	private static function getCountryCodeFromIPAddr(?array $defaultDrivers = ['ipapi', 'ipapico']): ?string
	{
		if (empty($defaultDrivers)) {
			return null;
		}
		
		$countryCode = Cookie::get('ipCountryCode');
		if (empty($countryCode)) {
			// Localize the user's country
			try {
				foreach ($defaultDrivers as $driver) {
					config()->set('geoip.default', $driver);
					
					$data = (new GeoIP())->getData();
					$countryCode = data_get($data, 'countryCode');
					
					// Fix for some countries
					$countryCode = ($countryCode == 'UK') ? 'GB' : $countryCode;
					
					if (!is_string($countryCode) || strlen($countryCode) != 2) {
						// Remove the current element (driver) from the array
						$currDefaultDrivers = array_diff($defaultDrivers, [$driver]);
						if (!empty($currDefaultDrivers)) {
							return self::getCountryCodeFromIPAddr($currDefaultDrivers);
						}
						
						return null;
					} else {
						break;
					}
				}
			} catch (\Throwable $t) {
				return null;
			}
			
			// Set data in cookie
			Cookie::set('ipCountryCode', $countryCode);
		}
		
		return getAsStringOrNull($countryCode);
	}
}
