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

trait FiltersCleanerBtn
{
	/**
	 * Generate button link for the category filter removal
	 *
	 * @param null $cat
	 * @param null $city
	 * @return string
	 */
	public function getCategoryFilterClearLink($cat = null, $city = null): string
	{
		$out = '';
		
		if ($this->doesCategoryIsFiltered($cat)) {
			$url = $this->searchWithoutCategory($cat, $city);
			$out = getFilterClearBtn($url);
		}
		
		return $out;
	}
	
	/**
	 * Generate button link for the city filter removal
	 *
	 * @param null $cat
	 * @param null $city
	 * @return string
	 */
	public function getCityFilterClearLink($cat = null, $city = null): string
	{
		$out = '';
		
		if ($this->doesCityIsFiltered($city)) {
			$url = $this->searchWithoutCity($cat, $city);
			$out = getFilterClearBtn($url);
		}
		
		return $out;
	}
	
	/**
	 * Generate button link for the date filter removal
	 *
	 * @param null $cat
	 * @param null $city
	 * @return string
	 */
	public function getDateFilterClearLink($cat = null, $city = null): string
	{
		$out = '';
		
		if ($this->doesDateIsFiltered($cat, $city)) {
			$url = $this->searchWithoutDate($cat, $city);
			$out = getFilterClearBtn($url);
		}
		
		return $out;
	}
	
	/**
	 * Generate button link for the price filter removal
	 *
	 * @param null $cat
	 * @param null $city
	 * @return string
	 */
	public function getPriceFilterClearLink($cat = null, $city = null): string
	{
		$out = '';
		
		if ($this->doesPriceIsFiltered($cat, $city)) {
			$url = $this->searchWithoutPrice($cat, $city);
			$out = getFilterClearBtn($url);
		}
		
		return $out;
	}
	
	/**
	 * Generate button link for the listing type filter removal
	 *
	 * @param null $cat
	 * @param null $city
	 * @return string
	 */
	public function getTypeFilterClearLink($cat = null, $city = null): string
	{
		$out = '';
		
		if ($this->doesTypeIsFiltered($cat, $city)) {
			$url = $this->searchWithoutType($cat, $city);
			$out = getFilterClearBtn($url);
		}
		
		return $out;
	}
	
	/**
	 * Generate button link for the custom field filter removal
	 *
	 * @param $field
	 * @param null $cat
	 * @param null $city
	 * @return string
	 */
	public function getCustomFieldFilterClearLink($field, $cat = null, $city = null): string
	{
		$out = '';
		
		if ($this->doesCustomFieldIsFiltered($field, $cat)) {
			$url = $this->searchWithoutCustomField($field, $cat, $city);
			$out = getFilterClearBtn($url);
		}
		
		return $out;
	}
}
