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

namespace App\Http\Controllers\Web\Front\Page;

use App\Http\Controllers\Web\Front\FrontController;
use Larapen\LaravelMetaTags\Facades\MetaTag;

class PricingController extends FrontController
{
	/**
	 * @return \Illuminate\Contracts\View\View
	 * @throws \Exception
	 */
	public function index()
	{
		// Get Listings' Promo Packages
		$promoPackagesData = $this->getPromotionPackages();
		$promoPackagesErrorMessage = $this->handleHttpError($promoPackagesData);
		$promoPackages = data_get($promoPackagesData, 'result.data');
		
		// Get Subscriptions Packages
		$subsPackagesData = $this->getSubscriptionPackages();
		$subsPackagesErrorMessage = $this->handleHttpError($subsPackagesData);
		$subsPackages = data_get($subsPackagesData, 'result.data');
		
		// Meta Tags
		[$title, $description, $keywords] = getMetaTag('pricing');
		MetaTag::set('title', $title);
		MetaTag::set('description', strip_tags($description));
		MetaTag::set('keywords', $keywords);
		
		// Open Graph
		$this->og->title($title)->description($description)->type('website');
		view()->share('og', $this->og);
		
		return view(
			'front.pages.pricing',
			compact(
				'subsPackages',
				'subsPackagesErrorMessage',
				'promoPackages',
				'promoPackagesErrorMessage'
			)
		);
	}
	
	private function getPromotionPackages(): array
	{
		// Get Packages - Call API endpoint
		$endpoint = '/packages/promotion';
		$queryParams = [
			'embed' => 'currency',
			'sort'  => '-lft',
		];
		$queryParams = array_merge(request()->all(), $queryParams);
		$data = makeApiRequest(method: 'get', uri: $endpoint, data: $queryParams);
		
		// Select a Package and go to previous URL
		// Add Listing possible URLs
		$addListingUriArray = [
			'create',
			'post\/create',
			'post\/create\/[^\/]+\/photos',
		];
		// Default Add Listing URL
		$addListingUrl = urlGen()->addPost();
		
		if (request()->filled('from')) {
			$path = request()->input('from');
			if (!empty($path) && is_string($path)) {
				foreach ($addListingUriArray as $uriPattern) {
					if (preg_match('#' . $uriPattern . '#', $path)) {
						$addListingUrl = url($path);
						break;
					}
				}
			}
		}
		
		view()->share('addListingUrl', $addListingUrl);
		
		return $data;
	}
	
	private function getSubscriptionPackages(): array
	{
		// Get Packages - Call API endpoint
		$endpoint = '/packages/subscription';
		$queryParams = [
			'embed' => 'currency',
			'sort'  => '-lft',
		];
		$queryParams = array_merge(request()->all(), $queryParams);
		
		return makeApiRequest(method: 'get', uri: $endpoint, data: $queryParams);
	}
}
