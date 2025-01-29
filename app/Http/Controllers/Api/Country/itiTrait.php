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

namespace App\Http\Controllers\Api\Country;

use App\Exceptions\Custom\CustomException;
use App\Helpers\Arr;
use App\Models\Country;
use App\Models\Scopes\ActiveScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

trait itiTrait
{
	/**
	 * Get the 'Intl Tel Input' countries
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	public function getItiCountries(): JsonResponse
	{
		// Get the countries from the plugin JS file
		// to get eventual missed information (e.g. priority)
		$pluginCountries = $this->getItiCountriesFromConfig();
		if (!empty($pluginCountries)) {
			$pluginCountries = collect($pluginCountries)->keyBy('iso2')->toArray();
		}
		
		// Get the countries from DB
		$dbCountries = $this->getItiCountriesFromDb();
		
		if ($dbCountries->isEmpty()) {
			return apiResponse()->noContent();
		}
		
		$dbCountries = $dbCountries->toArray();
		
		$countries = [];
		foreach ($dbCountries as $country) {
			$countryName = getColumnTranslation($country['name']);
			if (
				empty($countryName)
				|| empty($country['code'])
				|| empty($country['phone'])
			) {
				continue;
			}
			
			$name = str($countryName)->limit(50)->toString();
			$iso2 = strtolower($country['code']);
			
			$newItem = [
				'name'      => $name,
				'iso2'      => $iso2,
				'dialCode'  => null,
				'priority'  => 0,
				'areaCodes' => null,
			];
			
			// dialCode
			$phoneCode = str_replace('+', '', $country['phone']);
			if (
				str_contains($phoneCode, '-')
				|| str_contains($phoneCode, '/')
				|| str_contains($phoneCode, ',')
				|| str_contains($phoneCode, '|')
			) {
				$areaCodes = [];
				if (str_contains($phoneCode, '-')) {
					$tmp = explode('-', $phoneCode);
					$newItem['dialCode'] = $tmp[0];
					if (isset($tmp[1])) {
						$tmp2 = preg_split('#/|,|\|#', $tmp[1]);
						$areaCodes = [$tmp2[0]];
					}
				}
				if (
					str_contains($phoneCode, '/')
					|| str_contains($phoneCode, ',')
					|| str_contains($phoneCode, '|')
				) {
					$tmp = preg_split('#/|,|\|#', $phoneCode);
					foreach ($tmp as $areaCode) {
						if (str_contains($areaCode, '-')) {
							$areaCode = Arr::last(explode('-', $areaCode));
						}
						$areaCodes[] = $areaCode;
					}
					$areaCodes = array_unique($areaCodes);
				}
				$newItem['areaCodes'] = $areaCodes;
			} else {
				$newItem['dialCode'] = $phoneCode;
			}
			
			if (empty($newItem['dialCode'])) {
				continue;
			}
			
			// priority
			$newItem['priority'] = $pluginCountries[$iso2]['priority'] ?? $newItem['priority'];
			
			$countries[] = $newItem;
		}
		
		$data = [
			'success' => true,
			'result'  => $countries,
		];
		
		return apiResponse()->json($data);
	}
	
	/**
	 * Get the countries from the plugin JS file
	 *
	 * @return array
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	public function getItiCountriesFromConfig(): array
	{
		$pluginFilePath = public_path('assets/plugins/intl-tel-input/17.0.18/js/intlTelInput.js');
		if (file_exists($pluginFilePath)) {
			$buffer = file_get_contents($pluginFilePath);
			$buffer = str($buffer)->betweenFirst('var allCountries =', '];')->trim()->toString() . ']';
			
			if (str_starts_with($buffer, '[') && str_ends_with($buffer, ']]')) {
				$itiCountries = $this->convertJsArrayToPHPArray($buffer);
			}
		}
		
		if (empty($itiCountries)) {
			return [];
		}
		
		// Build and output the intl-tel-input 'data.js' file
		$countries = [];
		foreach ($itiCountries as $key => $item) {
			$countries[$key] = [
				'name'      => str($item[0])->limit(50)->toString(),
				'iso2'      => $item[1],
				'dialCode'  => $item[2],
				'priority'  => $item[3] ?? 0,
				'areaCodes' => $item[4] ?? null,
			];
		}
		
		return $countries;
	}
	
	/**
	 * Get the countries from DB
	 *
	 * @return \Illuminate\Support\Collection
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	private function getItiCountriesFromDb(): Collection
	{
		$phoneOfCountries = config('settings.sms.phone_of_countries', 'local');
		$isFromAdminPanel = (request()->filled('isFromAdminPanel') && (int)request()->input('isFromAdminPanel') == 1);
		$countryCode = config('country.code', 'US');
		
		$dbQueryCanBeSkipped = (!isFromAdminPanel() && $phoneOfCountries == 'local' && !empty(config('country')));
		if ($dbQueryCanBeSkipped) {
			return collect([$countryCode => collect(config('country'))]);
		}
		
		try {
			$cacheId = $isFromAdminPanel
				? 'iti.countries'
				: 'iti.countries.' . $phoneOfCountries . '.' . $countryCode . '.' . app()->getLocale();
			$countries = cache()->remember(
				$cacheId,
				$this->cacheExpiration,
				function () use ($phoneOfCountries, $isFromAdminPanel, $countryCode) {
					$countries = Country::query();
					
					if ($isFromAdminPanel) {
						$countries->withoutGlobalScopes([ActiveScope::class]);
					} else {
						// Skipped
						if ($phoneOfCountries == 'local') {
							$countries->where('code', '=', $countryCode);
						}
						if ($phoneOfCountries == 'activated') {
							$countries->active();
						}
						if ($phoneOfCountries == 'all') {
							$countries->withoutGlobalScopes([ActiveScope::class]);
						}
					}
					
					$countries = $countries->orderBy('name')->get();
					
					if ($countries->count() > 0) {
						$countries = $countries->keyBy('code');
					}
					
					return $countries;
				});
		} catch (\Throwable $e) {
			$message = 'Impossible to get countries from database. Error: ' . getExceptionMessage($e);
			throw new CustomException($message);
		}
		
		$countries = collect($countries);
		
		// Sort
		return Arr::mbSortBy($countries, 'name', app()->getLocale());
	}
	
	/**
	 * @param $jsArrayString
	 * @return array
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	private function convertJsArrayToPHPArray($jsArrayString): array
	{
		// Remove any trailing commas within the array to ensure it's valid JSON
		// $jsArrayString = preg_replace('#,\s*([\]}])#', '$1', $jsArrayString);
		
		// Convert the JavaScript array string to a PHP array
		$phpArray = json_decode($jsArrayString, true);
		
		// Check if conversion was successful
		if (json_last_error() === JSON_ERROR_NONE) {
			return is_array($phpArray) ? $phpArray : [];
		} else {
			$message = "Error decoding JSON: " . json_last_error_msg();
			throw new CustomException($message, 400);
		}
	}
}
