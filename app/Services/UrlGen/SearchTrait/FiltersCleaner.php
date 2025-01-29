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

use App\Helpers\Arr;

trait FiltersCleaner
{
	use FiltersCleanerBtn;
	
	/**
	 * Remove category from filters
	 *
	 * @param null $cat
	 * @param null $city
	 * @return string
	 */
	public function searchWithoutCategory($cat = null, $city = null): string
	{
		$url = request()->fullUrl();
		
		$cat = is_array($cat) ? Arr::toObject($cat) : $cat;
		$city = is_array($city) ? Arr::toObject($city) : $city;
		
		if ($this->doesCategoryIsFiltered($cat)) {
			$paramsToRemove = ['page', 'cf', 'minPrice', 'maxPrice'];
			if (!empty($cat)) {
				$paramsToRemove[] = 'sc';
				if (empty($cat->parent)) {
					$paramsToRemove[] = 'c';
				}
			}
			
			$url = urlQuery(urlGen()->search())->removeParameters($paramsToRemove)->toString();
			
			if (!empty($city)) {
				if (empty($cat)) {
					$url = urlGen()->city($city);
				}
			} else {
				if (!empty($cat->parent)) {
					$url = urlGen()->category($cat->parent, null, $city);
				}
			}
		}
		
		return $url;
	}
	
	/**
	 * Remove city from filters
	 *
	 * @param null $cat
	 * @param null $city
	 * @return string
	 */
	public function searchWithoutCity($cat = null, $city = null): string
	{
		$url = request()->fullUrl();
		
		$cat = is_array($cat) ? Arr::toObject($cat) : $cat;
		$city = is_array($city) ? Arr::toObject($city) : $city;
		
		if ($this->doesCityIsFiltered($city)) {
			$paramsToRemove = ['l', 'r', 'location', 'distance', 'page'];
			$url = urlQuery(urlGen()->search())->removeParameters($paramsToRemove)->toString();
			
			if (!empty($cat)) {
				if (empty($city)) {
					$url = urlGen()->category($cat);
				}
			}
		}
		
		return $url;
	}
	
	/**
	 * Remove date from filters
	 *
	 * @param null $cat
	 * @param null $city
	 * @return string
	 */
	public function searchWithoutDate($cat = null, $city = null): string
	{
		$url = request()->fullUrl();
		
		$cat = is_array($cat) ? Arr::toObject($cat) : $cat;
		$city = is_array($city) ? Arr::toObject($city) : $city;
		
		if ($this->doesDateIsFiltered($cat, $city)) {
			$params = [];
			if (!empty($cat) && !empty($cat->id)) {
				$params['c'] = $cat->id;
				if (!empty($cat->parent)) {
					$params['c'] = $cat->parent->id;
					$params['sc'] = $cat->id;
				}
			}
			if (!empty($city) && !empty($city->id)) {
				$params['l'] = $city->id;
			}
			
			$paramsToRemove = ['page', 'postedDate'];
			$url = urlQuery(urlGen()->search())->removeParameters($paramsToRemove)->setParameters($params)->toString();
		}
		
		return $url;
	}
	
	/**
	 * Remove price from filters
	 *
	 * @param null $cat
	 * @param null $city
	 * @return string
	 */
	public function searchWithoutPrice($cat = null, $city = null): string
	{
		$url = request()->fullUrl();
		
		$cat = is_array($cat) ? Arr::toObject($cat) : $cat;
		$city = is_array($city) ? Arr::toObject($city) : $city;
		
		if ($this->doesPriceIsFiltered($cat, $city)) {
			$params = [];
			if (!empty($cat) && !empty($cat->id)) {
				$params['c'] = $cat->id;
				if (!empty($cat->parent)) {
					$params['c'] = $cat->parent->id;
					$params['sc'] = $cat->id;
				}
			}
			if (!empty($city) && !empty($city->id)) {
				$params['l'] = $city->id;
			}
			
			$paramsToRemove = ['page', 'minPrice', 'maxPrice'];
			$url = urlQuery(urlGen()->search())->removeParameters($paramsToRemove)->setParameters($params)->toString();
		}
		
		return $url;
	}
	
	/**
	 * Remove listing type from filters
	 *
	 * @param null $cat
	 * @param null $city
	 * @return string
	 */
	public function searchWithoutType($cat = null, $city = null): string
	{
		$url = request()->fullUrl();
		
		$cat = is_array($cat) ? Arr::toObject($cat) : $cat;
		$city = is_array($city) ? Arr::toObject($city) : $city;
		
		if ($this->doesTypeIsFiltered($cat, $city)) {
			$params = [];
			if (!empty($cat) && !empty($cat->id)) {
				$params['c'] = $cat->id;
				if (!empty($cat->parent)) {
					$params['c'] = $cat->parent->id;
					$params['sc'] = $cat->id;
				}
			}
			if (!empty($city) && !empty($city->id)) {
				$params['l'] = $city->id;
			}
			
			$paramsToRemove = ['page', 'type'];
			$url = urlQuery(urlGen()->search())->removeParameters($paramsToRemove)->setParameters($params)->toString();
		}
		
		return $url;
	}
	
	/**
	 * Remove a specific custom field from filters
	 *
	 * @param $field
	 * @param null $cat
	 * @param null $city
	 * @return string
	 */
	public function searchWithoutCustomField($field, $cat = null, $city = null): string
	{
		$url = request()->fullUrl();
		
		$cat = is_array($cat) ? Arr::toObject($cat) : $cat;
		$city = is_array($city) ? Arr::toObject($city) : $city;
		
		if ($this->doesCustomFieldIsFiltered($field, $cat)) {
			$params = [];
			if (!empty($cat) && !empty($cat->id)) {
				$params['c'] = $cat->id;
				if (!empty($cat->parent)) {
					$params['c'] = $cat->parent->id;
					$params['sc'] = $cat->id;
				}
			}
			if (!empty($city) && !empty($city->id)) {
				$params['l'] = $city->id;
			}
			
			$paramsToRemove = ['page', $field];
			$url = urlQuery(urlGen()->search())->removeParameters($paramsToRemove)->setParameters($params)->toString();
		}
		
		return $url;
	}
}
