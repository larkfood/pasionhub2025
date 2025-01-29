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

namespace App\Services\UrlGen;

use App\Helpers\Arr;
use App\Services\UrlGen\SearchTrait\Filters;

trait SearchTrait
{
	use Filters;
	
	/**
	 * @param bool $currentUrl
	 * @param string|null $countryCode
	 * @return string
	 */
	public function searchWithoutQuery(bool $currentUrl = false, string $countryCode = null): string
	{
		if (empty($countryCode)) {
			$countryCode = config('country.code');
		}
		
		$countryCodePath = '';
		if (config('settings.seo.multi_country_urls')) {
			if (!empty($countryCode)) {
				$countryCodePath = strtolower($countryCode) . '/';
			}
		}
		
		if ($currentUrl) {
			$url = request()->url();
		} else {
			$path = str_replace(['{countryCode}/'], [''], config('routes.search'));
			$url = $countryCodePath . $path;
		}
		
		return urlQuery($url)->removeAllParameters()->toString();
	}
	
	/**
	 * @param array $params
	 * @param bool $currentUrl
	 * @param string|null $countryCode
	 * @return string
	 */
	public function search(array $params = [], bool $currentUrl = false, string $countryCode = null): string
	{
		$url = $this->searchWithoutQuery($currentUrl, $countryCode);
		
		$params = array_merge(request()->query(), $params);
		$paramsToRemove = ['page', 'filterBy'];
		
		return urlQuery($url)->removeParameters($paramsToRemove)->setParameters($params)->toString();
	}
	
	/**
	 * @param $cat
	 * @param null $city
	 * @return string|null
	 */
	public function parentCategory($cat, $city = null): ?string
	{
		if (empty($cat)) {
			return null;
		}
		
		$cat = is_array($cat) ? Arr::toObject($cat) : $cat;
		$city = is_array($city) ? Arr::toObject($city) : $city;
		
		$routeSearchPostsByCat = str_replace('{countryCode}/', '', config('routes.searchPostsByCat'));
		$segmentIndex = (config('settings.seo.multi_country_urls') == '1') ? 2 : 1;
		$firstSegment = request()->segment($segmentIndex);
		
		$paramsToRemove = ['c', 'sc', 'cf', 'page', 'minPrice', 'maxPrice', 'filterBy'];
		if (str_starts_with($routeSearchPostsByCat, $firstSegment . '/')) {
			
			// Category permalink
			$catParentUrl = !empty($cat->parent)
				? $this->category($cat->parent, null, null, false)
				: $this->category($cat, null, null, false);
			$catParentUrl = urlQuery($catParentUrl)->removeParameters($paramsToRemove)->toString();
			
		} else {
			
			$params = [];
			$params['c'] = $cat->id;
			if (!empty($cat->parent)) {
				$params['c'] = $cat->parent->id;
				$params['sc'] = $cat->id;
			}
			
			$catParentUrl = urlQuery($this->search())
				->removeParameters($paramsToRemove)
				->setParameters($params)
				->toString();
		}
		
		return $catParentUrl;
	}
	
	/**
	 * @param $cat
	 * @param string|null $countryCode
	 * @param null $city
	 * @param bool $findParent
	 * @return string|null
	 */
	public function category($cat, string $countryCode = null, $city = null, bool $findParent = true): ?string
	{
		if (empty($cat)) {
			return null;
		}
		
		$cat = is_array($cat) ? Arr::toObject($cat) : $cat;
		$city = is_array($city) ? Arr::toObject($city) : $city;
		
		$paramsToRemove = ['c', 'sc', 'cf', 'page', 'minPrice', 'maxPrice', 'filterBy'];
		
		// Search base permalink + category queries string
		$locationExists = (!empty($city) && isset($city->id));
		$filterByExists = request()->filled('filterBy');
		if ($locationExists || $filterByExists) {
			$params = [];
			$params['c'] = $cat->id;
			if (!empty($cat->parent)) {
				$params['c'] = $cat->parent->id;
				$params['sc'] = $cat->id;
			}
			if ($locationExists) {
				$params['l'] = $city->id;
			}
			if ($filterByExists) {
				$params['filterBy'] = request()->input('filterBy');
			}
			
			return urlQuery($this->search())->removeParameters($paramsToRemove)->setParameters($params)->toString();
		}
		
		// Category permalink
		if (empty($countryCode)) {
			$countryCode = config('country.code');
		}
		
		$countryCodePath = '';
		if (config('settings.seo.multi_country_urls')) {
			if (!empty($countryCode)) {
				$countryCodePath = strtolower($countryCode) . '/';
			}
		}
		
		if (isset($cat->slug)) {
			if ($findParent && !empty($cat->parent)) {
				$path = str_replace(
					['{countryCode}/', '{catSlug}', '{subCatSlug}'],
					['', $cat->parent->slug, $cat->slug],
					config('routes.searchPostsBySubCat')
				);
			} else {
				$path = str_replace(['{countryCode}/', '{catSlug}'], ['', $cat->slug], config('routes.searchPostsByCat'));
			}
			$url = url($countryCodePath . $path);
			$url = urlQuery($url)
				->setParameters(request()->query())
				->removeParameters($paramsToRemove)
				->toString();
		} else {
			$url = urlQuery($this->search())
				->removeParameters($paramsToRemove)
				->toString();
		}
		
		return getAsString($url);
	}
	
	/**
	 * @param $city
	 * @param string|null $countryCode
	 * @param null $cat
	 * @return string|null
	 */
	public function city($city, string $countryCode = null, $cat = null): ?string
	{
		if (empty($city)) {
			return null;
		}
		
		$city = is_array($city) ? Arr::toObject($city) : $city;
		$cat = is_array($cat) ? Arr::toObject($cat) : $cat;
		
		$paramsToRemove = ['l', 'page', 'location', 'filterBy', 'distance'];
		
		// Search base permalink + location queries string
		$categoryExists = (!empty($cat) && isset($cat->id));
		$filterByExists = request()->filled('filterBy');
		if ($categoryExists || $filterByExists) {
			$params = [];
			$params['l'] = $city->id;
			if ($categoryExists) {
				$params['c'] = $cat->id;
				if (!empty($cat->parent)) {
					$params['c'] = $cat->parent->id;
					$params['sc'] = $cat->id;
				}
			}
			if ($filterByExists) {
				$params['filterBy'] = request()->input('filterBy');
			}
			
			return urlQuery($this->search())
				->removeParameters($paramsToRemove)
				->setParameters($params)
				->toString();
		}
		
		// Location permalink
		if (empty($countryCode)) {
			$countryCode = !empty($city->country_code) ? $city->country_code : config('country.code');
		}
		
		$countryCodePath = '';
		if (config('settings.seo.multi_country_urls')) {
			if (!empty($countryCode)) {
				$countryCodePath = strtolower($countryCode) . '/';
			}
		}
		
		if (isset($city->id, $city->name)) {
			$path = str_replace(
				['{countryCode}/', '{city}', '{id}'],
				['', ($city->slug ?? slugify($city->name)), $city->id],
				config('routes.searchPostsByCity')
			);
			$path = $countryCodePath . $path;
			if (isAdminPanel()) {
				$url = dmUrl($city->country_code, $path);
			} else {
				$url = url($path);
			}
			$url = urlQuery($url)
				->setParameters(request()->query())
				->removeParameters($paramsToRemove)
				->toString();
		} else {
			$url = '/';
		}
		
		return getAsString($url);
	}
	
	/**
	 * @param $user
	 * @param string|null $countryCode
	 * @return string|null
	 */
	public function user($user, string $countryCode = null): ?string
	{
		if (empty($user)) {
			return null;
		}
		
		$user = is_array($user) ? Arr::toObject($user) : $user;
		
		if (empty($countryCode)) {
			$countryCode = config('country.code');
		}
		
		$countryCodePath = '';
		if (config('settings.seo.multi_country_urls')) {
			if (!empty($countryCode)) {
				$countryCodePath = strtolower($countryCode) . '/';
			}
		}
		
		if (!empty($user->username)) {
			$path = str_replace(['{countryCode}/', '{username}'], ['', $user->username], config('routes.searchPostsByUsername'));
			$url = url($countryCodePath . $path);
		} else {
			if (isset($user->id)) {
				$path = str_replace(['{countryCode}/', '{id}'], ['', $user->id], config('routes.searchPostsByUserId'));
				$url = url($countryCodePath . $path);
			} else {
				$url = '/';
			}
		}
		
		return urlQuery($url)->toString();
	}
	
	/**
	 * @param string $tag
	 * @param string|null $countryCode
	 * @return string
	 */
	public function tag(string $tag, string $countryCode = null): string
	{
		if (empty($countryCode)) {
			$countryCode = config('country.code');
		}
		
		$countryCodePath = '';
		if (config('settings.seo.multi_country_urls')) {
			if (!empty($countryCode)) {
				$countryCodePath = strtolower($countryCode) . '/';
			}
		}
		
		$path = str_replace(['{countryCode}/', '{tag}'], ['', $tag], config('routes.searchPostsByTag'));
		$url = url($countryCodePath . $path);
		
		return urlQuery($url)->toString();
	}
	
	/**
	 * @param int|null $companyId
	 * @param string|null $countryCode
	 * @return string|null
	 */
	public function company(int $companyId = null, string $countryCode = null): ?string
	{
		if (empty($companyId)) {
			return null;
		}
		
		if (empty($countryCode)) {
			$countryCode = config('country.code');
		}
		
		$countryCodePath = '';
		if (config('settings.seo.multi_country_urls')) {
			if (!empty($countryCode)) {
				$countryCodePath = strtolower($countryCode) . '/';
			}
		}
		
		$path = str_replace(['{countryCode}/', '{id}'], ['', $companyId], config('routes.searchPostsByCompanyId'));
		$url = url($countryCodePath . $path);
		
		return urlQuery($url)->toString();
	}
}
