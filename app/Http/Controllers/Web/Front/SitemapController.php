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

use App\Models\Category;
use App\Models\City;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Larapen\LaravelMetaTags\Facades\MetaTag;

class SitemapController extends FrontController
{
	/**
	 * @return \Illuminate\Contracts\View\View
	 */
	public function index()
	{
		$categoriesLimit = getNumberOfItemsToTake('categories');
		$citiesLimit = getNumberOfItemsToTake('cities');
		
		$data = [];
		
		// Get Categories
		$cacheId = 'categories.take.' . $categoriesLimit . '.' . config('app.locale');
		$cats = cache()->remember($cacheId, $this->cacheExpiration, function () use ($categoriesLimit) {
			return Category::root()
				->with([
					'parent',
					'children' => fn (Builder $query) => $query->orderBy('lft')->limit($categoriesLimit),
					'children.parent'
				])
				->orderBy('lft')
				->take($categoriesLimit)
				->get();
		});
		$cats = collect($cats)->keyBy('id');
		$data['cats'] = $cats;
		
		// Get Cities
		$cacheId = config('country.code') . '.cities.take.' . $citiesLimit;
		$cities = cache()->remember($cacheId, $this->cacheExpiration, function () use ($citiesLimit) {
			return City::query()
				->inCountry()
				->take($citiesLimit)
				->orderByDesc('population')
				->orderBy('name')
				->get();
		});
		$data['cities'] = $cities;
		
		// Meta Tags
		[$title, $description, $keywords] = getMetaTag('sitemap');
		MetaTag::set('title', $title);
		MetaTag::set('description', strip_tags($description));
		MetaTag::set('keywords', $keywords);
		
		return view('front.sitemap.index', $data);
	}
}
