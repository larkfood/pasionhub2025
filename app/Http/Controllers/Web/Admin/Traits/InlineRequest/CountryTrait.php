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

namespace App\Http\Controllers\Web\Admin\Traits\InlineRequest;

use App\Helpers\DBTool;
use App\Models\City;
use App\Models\Post;
use App\Models\SubAdmin1;
use App\Models\SubAdmin2;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

trait CountryTrait
{
	/**
	 * Update the 'active' column of the country table
	 * And import|remove the Geonames data: Country, Admin Divisions & Cities
	 *
	 * @param $country
	 * @param $column
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	protected function updateCountryData($country, $column): \Illuminate\Http\JsonResponse
	{
		$isValidCondition = ($this->table == 'countries' && $column == 'active' && !empty($country));
		if (!$isValidCondition) {
			$error = trans('admin.inline_req_condition', ['table' => $this->table, 'column' => $column]);
			
			return $this->responseError($error, 400);
		}
		
		$defaultCountryCode = config('settings.localization.default_country_code');
		$isDefaultCountry = (strtolower($defaultCountryCode) == strtolower($country->code));
		
		// Update|import|remove data
		if ($country->{$column} == 0) {
			// Import Geonames Data
			$resImport = $this->importGeonamesSql($country->code);
			if (!$resImport) {
				return $this->responseError(trans('admin.inline_req_geonames_data_import_error'));
			}
		} else {
			// Don't disable|remove data for the default country
			if ($isDefaultCountry) {
				return $this->responseError(trans('admin.inline_req_skip_default_country'), Response::HTTP_UNAUTHORIZED);
			}
			
			// Remove Geonames Data
			$resImport = $this->removeGeonamesDataByCountryCode($country->code);
			if (!$resImport) {
				return $this->responseError(trans('admin.inline_req_geonames_data_removing_error'));
			}
		}
		
		// Save data
		$country->{$column} = ($country->{$column} != 1) ? 1 : 0;
		$country->save();
		
		return $this->responseSuccess($country, $column);
	}
	
	/**
	 * Import the Geonames data for the country
	 *
	 * @param $countryCode
	 * @return bool
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	private function importGeonamesSql($countryCode): bool
	{
		// Remove all the country's data
		$this->removeGeonamesDataByCountryCode($countryCode);
		
		// Default Country SQL File
		$filePath = storage_path('database/geonames/countries/' . strtolower($countryCode) . '.sql');
		if (!File::exists($filePath)) {
			return false;
		}
		
		// Import the SQL file
		DBTool::importSqlFile(DB::connection()->getPdo(), $filePath, DB::getTablePrefix());
		
		return true;
	}
	
	/**
	 * Remove all the country's data
	 *
	 * @param $countryCode
	 * @return bool
	 */
	private function removeGeonamesDataByCountryCode($countryCode): bool
	{
		// Delete all SubAdmin1
		$admin1s = SubAdmin1::inCountry($countryCode);
		if ($admin1s->count() > 0) {
			foreach ($admin1s->cursor() as $admin1) {
				$admin1->delete();
			}
		}
		
		// Delete all SubAdmin2
		$admin2s = SubAdmin2::inCountry($countryCode);
		if ($admin2s->count() > 0) {
			foreach ($admin2s->cursor() as $admin2) {
				$admin2->delete();
			}
		}
		
		// Delete all Cities
		$cities = City::inCountry($countryCode);
		if ($cities->count() > 0) {
			foreach ($cities->cursor() as $city) {
				$city->delete();
			}
		}
		
		// Delete all Posts
		$posts = Post::inCountry($countryCode);
		if ($posts->count() > 0) {
			foreach ($posts->cursor() as $post) {
				$post->delete();
			}
		}
		
		return true;
	}
}
