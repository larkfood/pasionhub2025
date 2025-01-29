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

namespace App\Http\Controllers\Web\Front\Traits;

trait HasIntlTelInput
{
	/**
	 * Country list for the 'intl-tel-input' plugin
	 * URI: common/js/intl-tel-input/countries.js
	 *
	 * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
	 */
	public function intlTelInputData()
	{
		// Get the country list with calling code
		$countries = $this->getItiCountries();
		
		// dd(collect($countries)->keyBy('iso2')->toArray()); // debug
		
		$out = 'var itiErrorMessage = "Impossible to load countries with phone codes.";';
		if (!empty($countries)) {
			$out = 'var phoneCountries = ' . json_encode($countries) . ';' . "\n";
			$code = 200;
		}
		
		return response($out, $code ?? 400)->header('Content-Type', 'application/javascript');
	}
	
	/**
	 * Get the 'Intl Tel Input' countries
	 *
	 * @return array
	 */
	private function getItiCountries(): array
	{
		$phoneOfCountries = config('settings.sms.phone_of_countries', 'local');
		$cacheExpiration = (int)config('settings.optimization.cache_expiration', 3600);
		$countryCode = config('country.code', 'US');
		
		$cacheId = isFromAdminPanel()
			? 'web.iti.countries'
			: 'web.iti.countries.' . $phoneOfCountries . '.' . $countryCode . '.' . app()->getLocale();
		
		$countries = cache()->remember($cacheId, $cacheExpiration, function () use ($countryCode) {
			return $this->getItiCountriesWithoutCache();
		});
		
		return is_array($countries) ? $countries : [];
	}
	
	/**
	 * Get the 'Intl Tel Input' countries (Without Cache)
	 *
	 * @return array
	 */
	private function getItiCountriesWithoutCache(): array
	{
		// Call API endpoint
		$endpoint = '/countries';
		$queryParams = [
			'iti'              => true,
			'isFromAdminPanel' => isFromAdminPanel(),
		];
		$data = makeApiRequest(method: 'get', uri: $endpoint, data: $queryParams);
		$countries = data_get($data, 'result', []);
		
		return is_array($countries) ? $countries : [];
	}
}
