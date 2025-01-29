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

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Country\itiTrait;
use App\Http\Resources\CountryResource;
use App\Http\Resources\EntityCollection;
use App\Models\Country;
use App\Models\Scopes\ActiveScope;

/**
 * @group Countries
 */
class CountryController extends BaseController
{
	use itiTrait;
	
	/**
	 * List countries
	 *
	 * @header Content-Language {local-code}
	 *
	 * @queryParam embed string Comma-separated list of the country relationships for Eager Loading - Possible values: currency,continent. Example: null
	 * @queryParam includeNonActive boolean Allow including the non-activated countries in the list. Example: false
	 * @queryParam iti boolean Allow getting the country list for the phone number input (No other parameters need except 'countryCode'). Possible value: 0 or 1. Example: 0
	 * @queryParam countryCode string The code of the current country (Only when the 'iti' parameter is filled to true). Example: null
	 * @queryParam sort string The sorting parameter (Order by DESC with the given column. Use "-" as prefix to order by ASC). Possible values: name. Example: -name
	 * @queryParam perPage int Items per page. Can be defined globally from the admin settings. Cannot be exceeded 100. Example: 2
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	public function index(): \Illuminate\Http\JsonResponse
	{
		// 'Intl Tel Input' countries list
		$isIti = (request()->filled('iti') && request()->integer('iti') == 1);
		if ($isIti) {
			return $this->getItiCountries();
		}
		
		$embed = explode(',', request()->input('embed'));
		
		// Normal countries list
		$keyword = request()->input('q');
		$columnWithOrder = request()->input('sort');
		$perPage = getNumberOfItemsPerPage('countries', request()->integer('perPage'));
		$page = request()->integer('page');
		$isNonActiveIncluded = (request()->filled('includeNonActive') && request()->integer('includeNonActive') == 1);
		
		// Cache ID
		$cacheEmbedId = request()->filled('embed') ? '.embed.' . request()->input('embed') : '';
		$cacheFiltersId = '.filters.' . $keyword . (int)$isNonActiveIncluded;
		$cacheOrderById = '.sort.' . (is_array($columnWithOrder) ? implode(',', $columnWithOrder) : $columnWithOrder);
		$cachePageId = '.page.' . $page . '.of.' . $perPage;
		$cacheId = 'countries.' . $cacheEmbedId . $cacheFiltersId . $cacheOrderById . $cachePageId;
		
		// Cached Query
		$countries = cache()->remember($cacheId, $this->cacheExpiration, function () use (
			$perPage, $embed, $keyword, $isNonActiveIncluded
		) {
			$countries = Country::query();
			
			if (in_array('currency', $embed)) {
				$countries->with('currency');
			}
			
			if (!empty($keyword)) {
				$countries->where('name', 'LIKE', '%' . $keyword . '%');
			}
			if ($isNonActiveIncluded) {
				$countries->withoutGlobalScopes([ActiveScope::class]);
			} else {
				$countries->active();
			}
			
			// Sorting
			$countries = $this->applySorting($countries, ['name', 'code']);
			
			return $countries->paginate($perPage);
		});
		
		// If the request is made from the app's Web environment,
		// use the Web URL as the pagination's base URL
		$countries = setPaginationBaseUrl($countries);
		
		$resourceCollection = new EntityCollection(class_basename($this), $countries);
		
		$message = ($countries->count() <= 0) ? t('no_countries_found') : null;
		
		return apiResponse()->withCollection($resourceCollection, $message);
	}
	
	/**
	 * Get country
	 *
	 * @queryParam embed string Comma-separated list of the country relationships for Eager Loading - Possible values: currency. Example: currency
	 *
	 * @urlParam code string required The country's ISO 3166-1 code. Example: DE
	 *
	 * @param $code
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show($code): \Illuminate\Http\JsonResponse
	{
		$embed = explode(',', request()->input('embed'));
		
		// Cache ID
		$cacheEmbedId = request()->filled('embed') ? '.embed.' . request()->input('embed') : '';
		$cacheId = 'country.' . $code . $cacheEmbedId;
		
		// Cached Query
		$country = cache()->remember($cacheId, $this->cacheExpiration, function () use ($code, $embed) {
			$country = Country::query()->where('code', '=', $code);
			
			if (in_array('currency', $embed)) {
				$country->with('currency');
			}
			
			return $country->first();
		});
		
		abort_if(empty($country), 404, t('country_not_found'));
		
		$resource = new CountryResource($country);
		
		return apiResponse()->withResource($resource);
	}
}
