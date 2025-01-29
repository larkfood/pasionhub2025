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

namespace App\Services\Search\Traits\Filters;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;
use Larapen\LaravelDistance\Distance;

trait LocationFilter
{
	protected static int $defaultDistance = 50; // km
	protected static ?int $distance = null;     // km
	protected static int $maxDistance = 500;    // km
	
	protected function applyLocationFilter(): void
	{
		if (!(isset($this->posts) && isset($this->postsTable))) {
			return;
		}
		
		// Distance (Max & Default distance)
		self::$maxDistance = abs((int)config('settings.listings_list.search_distance_max', 0));
		self::$defaultDistance = abs((int)config('settings.listings_list.search_distance_default', 0));
		
		// Get the requested distance
		$distance = request()->input('distance');
		
		// Set the distance
		$isExtendedSearchesEnabled = (config('settings.listings_list.cities_extended_searches') == '1');
		self::$distance = is_numeric($distance)
			? Number::clamp($distance, min: 0, max: self::$maxDistance)
			: ($isExtendedSearchesEnabled ? self::$defaultDistance : 0);
		
		// Priority Settings
		
		// Exception when admin. division searched (City not found)
		// Skip arbitrary (fake) city with signed (-) ID, lon & lat
		if (!empty($this->city)) {
			if (isset($this->city->id) && $this->city->id <= 0) {
				return;
			}
		}
		
		if (str_contains(currentRouteAction(), 'Search\CityController')) {
			if (!empty($this->city)) {
				$this->applyLocationByCity($this->city);
			}
		} else {
			if (request()->filled('l')) {
				if (!empty($this->city)) {
					$this->applyLocationByCity($this->city);
				}
			} else {
				if (request()->filled('r')) {
					if (!empty($this->admin)) {
						$this->applyLocationByAdminCode($this->admin->code);
					}
				} else {
					if (request()->filled('location')) {
						// Must find all cities containing 'location' input's value
						if (!empty($this->citiesIds) && is_array($this->citiesIds)) {
							$this->applyLocationByCitiesIds($this->citiesIds);
						} else {
							if (!empty($this->city)) {
								$this->applyLocationByCity($this->city);
							}
						}
					}
				}
			}
		}
	}
	
	/**
	 * Apply administrative division filter
	 * Search including Administrative Division by adminCode
	 *
	 * @param $adminCode
	 * @return void
	 */
	private function applyLocationByAdminCode($adminCode): void
	{
		if (in_array(config('country.admin_type'), ['1', '2'])) {
			// Get the admin. division table info
			$adminType = config('country.admin_type');
			$adminRelation = 'subAdmin' . $adminType;
			$adminForeignKey = 'subadmin' . $adminType . '_code';
			
			$this->posts->whereHas('city', function ($query) use ($adminForeignKey, $adminCode) {
				$query->where($adminForeignKey, $adminCode);
			});
		}
	}
	
	/**
	 * Apply city filter (Using city's coordinates)
	 * Search including City by City Coordinates (lat & lon)
	 *
	 * @param $city
	 * @return void
	 */
	private function applyLocationByCity($city): void
	{
		if (!isset($city->id) || !isset($city->longitude) || !isset($city->latitude)) {
			return;
		}
		
		if (empty($city->longitude) || empty($city->latitude)) {
			return;
		}
		
		// Set City Globally
		$this->city = $city;
		
		// OrderBy Priority for Location
		$this->orderBy[] = $this->postsTable . '.created_at DESC';
		
		$isExtendedSearchesEnabled = (config('settings.listings_list.cities_extended_searches') == '1');
		if ($isExtendedSearchesEnabled) {
			
			// Use the Cities Extended Searches
			config()->set('distance.functions.default', config('settings.listings_list.distance_calculation_formula'));
			config()->set('distance.countryCode', config('country.code'));
			
			$sql = Distance::select('lon', 'lat', $city->longitude, $city->latitude);
			if ($sql) {
				$this->posts->addSelect(DB::raw($sql));
				$this->having[] = Distance::having(self::$distance);
				$this->orderBy[] = Distance::orderBy('ASC');
			} else {
				$this->applyLocationByCityId($city->id);
			}
			
		} else {
			
			// Use the Cities Standard Searches
			$this->applyLocationByCityId($city->id);
			
		}
	}
	
	/**
	 * Apply city filter (Using city's ID)
	 * Search including City by City ID
	 *
	 * @param $cityId
	 * @return void
	 */
	private function applyLocationByCityId($cityId): void
	{
		if (empty(trim($cityId))) {
			return;
		}
		
		$this->posts->where('city_id', $cityId);
	}
	
	/**
	 * Apply city filter (Using cities' IDs)
	 * Search including Cities by Cities IDs
	 *
	 * @param $citiesIds
	 * @return void
	 */
	private function applyLocationByCitiesIds($citiesIds): void
	{
		if (empty($citiesIds) || !is_array($citiesIds)) {
			return;
		}
		
		$this->posts->whereIn('city_id', $citiesIds);
	}
	
	/**
	 * Remove Distance from Request
	 */
	private function removeDistanceFromRequest(): void
	{
		$input = request()->all();
		
		// (If it's not necessary) Remove the 'distance' parameter from request()
		$isExtendedSearchesEnabled = (config('settings.listings_list.cities_extended_searches') == '1');
		if (!$isExtendedSearchesEnabled || empty($this->city)) {
			if (in_array('distance', array_keys($input))) {
				unset($input['distance']);
				request()->replace($input);
			}
		}
	}
}
