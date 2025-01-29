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

namespace App\Http\Controllers\Web\Front;

// Increase the server resources
$iniConfigFile = __DIR__ . '/../../Helpers/Functions/ini.php';
if (file_exists($iniConfigFile)) {
	include_once $iniConfigFile;
}

use App\Helpers\Date;
use App\Models\Category;
use App\Models\City;
use App\Models\Page;
use App\Models\Post;
use App\Services\Localization\Country as CountryLocalization;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Watson\Sitemap\Facades\Sitemap;

class SitemapsController extends FrontController
{
	protected Carbon|string $defaultDate = '2015-10-30T20:10:00+02:00';
	protected bool $isDomainmappingAvailable = false;
	
	/**
	 * SitemapsController constructor.
	 *
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->commonQueries();
		
		$this->isDomainmappingAvailable = (
			plugin_exists('domainmapping')
			&& plugin_installed_file_exists('domainmapping')
		);
	}
	
	/**
	 * Common Queries
	 */
	public function commonQueries(): void
	{
		// Set the Country's Locale & Default Date
		$this->applyCountrySettings();
	}
	
	// Sitemap Indexes
	
	/**
	 * @return mixed
	 */
	public function getAllCountriesSitemapIndex()
	{
		foreach ($this->countries as $item) {
			// Get Country Settings
			$country = $this->getCountrySettings($item->get('code'), false);
			if (empty($country)) {
				continue;
			}
			
			$basePath = $country['icode'] . '/';
			if ($this->isDomainmappingAvailable) {
				$basePath = '';
			}
			
			Sitemap::addSitemap(dmUrl(collect($country), $basePath . 'sitemaps.xml'));
		}
		
		return Sitemap::index();
	}
	
	/**
	 * @param string|null $countryCode
	 * @return mixed
	 */
	public function getSitemapIndexByCountry(string $countryCode = null)
	{
		if (empty($countryCode)) {
			$countryCode = config('country.code');
		}
		
		// Get Country Settings
		$country = $this->getCountrySettings($countryCode);
		if (empty($country)) {
			return Sitemap::index();
		}
		
		$basePath = $country['icode'] . '/';
		if ($this->isDomainmappingAvailable) {
			$basePath = '';
		}
		
		Sitemap::addSitemap(dmUrl(collect($country), $basePath . 'sitemaps/pages.xml'));
		Sitemap::addSitemap(dmUrl(collect($country), $basePath . 'sitemaps/categories.xml'));
		Sitemap::addSitemap(dmUrl(collect($country), $basePath . 'sitemaps/cities.xml'));
		
		$countPosts = Post::verified()->inCountry($country['code'])->count();
		if ($countPosts > 0) {
			Sitemap::addSitemap(dmUrl(collect($country), $basePath . 'sitemaps/posts.xml'));
		}
		
		return Sitemap::index();
	}
	
	// Sitemaps
	
	/**
	 * @param string|null $countryCode
	 * @return mixed
	 * @throws \Exception
	 */
	public function getPagesSitemapByCountry(string $countryCode = null)
	{
		if (empty($countryCode)) {
			$countryCode = config('country.code');
		}
		
		// Get Country Settings
		$country = $this->getCountrySettings($countryCode);
		if (empty($country)) {
			return Sitemap::render();
		}
		
		$params = [];
		if (!config('plugins.domainmapping.installed')) {
			$params['country'] = $country['code'];
		}
		
		$url = url('/');
		$url = urlQuery($url)->setParameters($params)->toString();
		Sitemap::addTag($url, $this->defaultDate, 'daily', '1.0');
		
		$url = urlGen()->sitemap($country['icode']);
		$url = urlQuery($url)->setParameters($params)->toString();
		Sitemap::addTag($url, $this->defaultDate, 'daily', '0.5');
		
		$url = urlGen()->search([], false, $country['icode']);
		$url = urlQuery($url)->setParameters($params)->toString();
		Sitemap::addTag($url, $this->defaultDate, 'daily', '0.6');
		
		$pages = cache()->remember('pages.' . $country['locale'], $this->cacheExpiration, function () use ($country) {
			return Page::query()->orderBy('lft')->get();
		});
		
		if ($pages->count() > 0) {
			foreach ($pages as $page) {
				$url = urlGen()->page($page);
				Sitemap::addTag($url, $this->defaultDate, 'daily', '0.7');
			}
		}
		
		$url = urlGen()->contact();
		$url = urlQuery($url)->setParameters($params)->toString();
		Sitemap::addTag($url, $this->defaultDate, 'daily', '0.7');
		
		return Sitemap::render();
	}
	
	/**
	 * @param string|null $countryCode
	 * @return mixed
	 */
	public function getCategoriesSitemapByCountry(string $countryCode = null)
	{
		if (empty($countryCode)) {
			$countryCode = config('country.code');
		}
		
		// Get Country Settings
		$country = $this->getCountrySettings($countryCode);
		if (empty($country)) {
			return Sitemap::render();
		}
		
		// Categories
		$cacheId = 'categories.' . $country['locale'] . '.all';
		$cats = cache()->remember($cacheId, $this->cacheExpiration, function () use ($country) {
			return Category::query()->with(['parent'])->orderBy('lft')->get();
		});
		
		if ($cats->count() > 0) {
			$cats = collect($cats)->keyBy('id');
			
			foreach ($cats as $cat) {
				$url = urlGen()->category($cat, $country['icode']);
				Sitemap::addTag($url, $this->defaultDate, 'weekly', '0.8');
			}
		}
		
		return Sitemap::render();
	}
	
	/**
	 * @param string|null $countryCode
	 * @return mixed
	 */
	public function getCitiesSitemapByCountry(string $countryCode = null)
	{
		if (empty($countryCode)) {
			$countryCode = config('country.code');
		}
		
		// Get Country Settings
		$country = $this->getCountrySettings($countryCode);
		if (empty($country)) {
			return Sitemap::render();
		}
		
		$limit = (int)env('XML_SITEMAP_LIMIT', 1000);
		$cacheId = $country['icode'] . '.cities.take.' . $limit;
		$cities = cache()->remember($cacheId, $this->cacheExpiration, function () use ($country, $limit) {
			return City::query()
				->inCountry($country['code'])
				->take($limit)
				->orderByDesc('population')
				->orderBy('name')
				->get();
		});
		
		if ($cities->count() > 0) {
			foreach ($cities as $city) {
				$city->name = trim(head(explode('/', $city->name)));
				$url = urlGen()->city($city, $country['icode']);
				Sitemap::addTag($url, $this->defaultDate, 'weekly', '0.7');
			}
		}
		
		return Sitemap::render();
	}
	
	/**
	 * @param string|null $countryCode
	 * @return mixed
	 * @throws \Exception
	 */
	public function getListingsSitemapByCountry(string $countryCode = null)
	{
		if (empty($countryCode)) {
			$countryCode = config('country.code');
		}
		
		// Get Country Settings
		$country = $this->getCountrySettings($countryCode);
		if (empty($country)) {
			return Sitemap::render();
		}
		
		$limit = (int)env('XML_SITEMAP_LIMIT', 1000);
		$cacheId = $country['icode'] . '.sitemaps.posts.xml';
		$posts = cache()->remember($cacheId, $this->cacheExpiration, function () use ($country, $limit) {
			return Post::query()
				->verified()
				->inCountry($country['code'])
				->take($limit)
				->orderByDesc('created_at')
				->get();
		});
		
		if ($posts->count() > 0) {
			foreach ($posts as $post) {
				$url = urlGen()->post($post);
				Sitemap::addTag($url, $post->created_at, 'daily', '0.6');
			}
		}
		
		return Sitemap::render();
	}
	
	/**
	 * Set the Country's Locale & Default Date
	 *
	 * @param string|null $locale
	 * @param string|null $timeZone
	 * @return void
	 */
	public function applyCountrySettings(string $locale = null, string $timeZone = null): void
	{
		// Set the App Language
		$locale = !empty($locale) ? $locale : config('app.locale');
		app()->setLocale($locale);
		
		// Date: Carbon object
		$this->defaultDate = Carbon::parse(date('Y-m-d H:i'));
		if (!empty($timeZone)) {
			$this->defaultDate->timezone($timeZone);
		} else {
			$this->defaultDate->timezone(Date::getAppTimeZone());
		}
	}
	
	/**
	 * Get Country Settings
	 *
	 * @param string|null $countryCode
	 * @param bool $canApplySettings
	 * @return array|null
	 */
	public function getCountrySettings(?string $countryCode, bool $canApplySettings = true): ?array
	{
		$tab = [];
		
		// Get Country Info
		$country = CountryLocalization::getCountryInfo($countryCode);
		if ($country->isEmpty()) {
			return null;
		}
		
		$tab['code'] = $country->get('code');
		$tab['icode'] = $country->get('icode');
		$tab['time_zone'] = $country->has('time_zone') ? $country->get('time_zone') : config('app.timezone');
		
		// Language
		$countryLang = $country->get('lang');
		$doesCountryLangExist = (
			$countryLang instanceof Collection
			&& $countryLang->isNotEmpty()
			&& $countryLang->has('code')
		);
		$tab['locale'] = $doesCountryLangExist ? $countryLang->get('code') : config('app.locale');
		
		// Set the Country's Locale & Default Date
		if ($canApplySettings) {
			$this->applyCountrySettings($tab['locale'], $tab['time_zone']);
		}
		
		return $tab;
	}
}
