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

namespace App\Services\UrlGen\SearchTrait;

trait Filters
{
	use FiltersCleaner;
	
	/**
	 * Check if filter has category
	 *
	 * @param null $cat
	 * @return bool
	 */
	public function doesCategoryIsFiltered($cat = null): bool
	{
		return (
			(
				str_contains(currentRouteAction(), 'Search\CategoryController')
				|| (
					$this->isFromSearchPage()
					&& (request()->filled('c') || request()->filled('sc'))
				)
			)
			&& !empty($cat)
		);
	}
	
	/**
	 * Check if filter has city
	 *
	 * @param null $city
	 * @return bool
	 */
	public function doesCityIsFiltered($city = null): bool
	{
		return (
			(
				str_contains(currentRouteAction(), 'Search\CityController')
				|| (
					$this->isFromSearchPage()
					&& (request()->filled('l') || request()->filled('location'))
				)
			)
			&& !empty($city)
		);
	}
	
	/**
	 * Check if filter has date
	 *
	 * @param null $cat
	 * @param null $city
	 * @return bool
	 */
	public function doesDateIsFiltered($cat = null, $city = null): bool
	{
		return (
			(
				$this->doesCategoryIsFiltered($cat)
				|| $this->doesCityIsFiltered($city)
				|| $this->isFromSearchPage()
			)
			&& request()->filled('postedDate')
		);
	}
	
	/**
	 * Check if filter has price
	 *
	 * @param null $cat
	 * @param null $city
	 * @return string
	 */
	public function doesPriceIsFiltered($cat = null, $city = null): string
	{
		return (
			(
				$this->doesCategoryIsFiltered($cat)
				|| $this->doesCityIsFiltered($city)
				|| $this->isFromSearchPage()
			)
			&& (request()->filled('minPrice') || request()->filled('maxPrice'))
		);
	}
	
	/**
	 * Check if filter has listing type
	 *
	 * @param null $cat
	 * @param null $city
	 * @return string
	 */
	public function doesTypeIsFiltered($cat = null, $city = null): string
	{
		return (
			(
				$this->doesCategoryIsFiltered($cat)
				|| $this->doesCityIsFiltered($city)
				|| $this->isFromSearchPage()
			)
			&& request()->filled('type')
		);
	}
	
	/**
	 * Check if filter has a specific custom field
	 *
	 * @param $field
	 * @param null $cat
	 * @return bool
	 */
	public function doesCustomFieldIsFiltered($field, $cat = null): bool
	{
		return (
			(
				$this->doesCategoryIsFiltered($cat)
				|| $this->isFromSearchPage()
			)
			&& request()->filled($field)
		);
	}
	
	/**
	 * Check if filter has tag
	 *
	 * @return bool
	 */
	public function doesTagIsFiltered(): bool
	{
		return (
			str_contains(currentRouteAction(), 'Search\TagController')
			|| (
				$this->isFromSearchPage()
				&& request()->filled('tag')
			)
		);
	}
	
	/**
	 * @return bool
	 */
	private function isFromSearchPage(): bool
	{
		// For API ---
		$isFromSearchPageApi = (
			isFromApi()
			&& str_contains(currentRouteAction(), 'Api\PostController@index')
			&& request()->input('op') == 'search'
		);
		
		// For Web ---
		$segmentIndex = (config('settings.seo.multi_country_urls') == '1') ? 2 : 1;
		
		// Get the URL first segment
		$firstSegment = request()->segment($segmentIndex);
		
		// Get routes patterns
		$routes = (array)config('routes');
		
		// Get search routes patterns
		$searchRoutes = collect($routes)
			->filter(fn ($item, $key) => str_starts_with($key, 'search'))
			->map(fn ($item) => str($item)
				->replaceFirst('{countryCode}/', '')
				->before('/')
				->finish('/')
				->toString()
			)
			->toArray();
		
		// Is the first segment match with a search route pattern?
		$isFromSearchPageWeb = (
			collect($searchRoutes)
				->contains(fn ($item) => str_starts_with($item, $firstSegment . '/'))
			&& !isFromApi()
		);
		
		return ($isFromSearchPageApi || $isFromSearchPageWeb);
	}
}
