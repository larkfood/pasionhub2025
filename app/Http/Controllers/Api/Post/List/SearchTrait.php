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

namespace App\Http\Controllers\Api\Post\List;

use App\Services\Search\PostQueries;
use App\Http\Controllers\Api\Post\List\Search\CategoryTrait;
use App\Http\Controllers\Api\Post\List\Search\LocationTrait;
use App\Http\Controllers\Api\Post\List\Search\SidebarTrait;
use App\Models\CategoryField;
use Larapen\LaravelDistance\Libraries\mysql\DistanceHelper;

trait SearchTrait
{
	use CategoryTrait, LocationTrait, SidebarTrait;
	
	/**
	 * @param string $op
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getPostsBySearch(string $op): \Illuminate\Http\JsonResponse
	{
		// Create the MySQL Distance Calculation function If it doesn't exist
		$distanceCalculationFormula = config('settings.listings_list.distance_calculation_formula', 'haversine');
		if (!DistanceHelper::checkIfDistanceCalculationFunctionExists($distanceCalculationFormula)) {
			DistanceHelper::createDistanceCalculationFunction($distanceCalculationFormula);
		}
		
		$preSearch = [];
		$fields = collect();
		
		// $embed = ['user', 'category', 'parent', 'postType', 'city', 'savedByLoggedUser', 'picture', 'pictures', 'payment', 'package'];
		$embed = ['user', 'savedByLoggedUser', 'picture', 'pictures', 'payment', 'package'];
		if (!config('settings.listings_list.hide_post_type')) {
			$embed[] = 'postType';
		}
		if (!config('settings.listings_list.hide_category')) {
			$embed[] = 'category';
			$embed[] = 'parent';
		}
		if (!config('settings.listings_list.hide_location')) {
			$embed[] = 'city';
		}
		request()->query->add(['embed' => implode(',', $embed)]);
		
		$perPage = getNumberOfItemsPerPage('posts', request()->integer('perPage'));
		
		$orderBy = request()->input('orderBy');
		$orderBy = ($orderBy != 'random') ? $orderBy : null;
		
		$input = [
			'op'      => $op,
			'perPage' => $perPage,
			'orderBy' => $orderBy,
		];
		
		$searchData = $this->searchPosts($input, $preSearch, $fields);
		$preSearch = $searchData['preSearch'] ?? $preSearch;
		
		$data = [
			'success' => true,
			'message' => $searchData['message'] ?? null,
			'result'  => $searchData['posts'],
			'extra'   => [
				'count'     => $searchData['count'] ?? [],
				'preSearch' => $preSearch,
				'sidebar'   => $this->getSidebar($preSearch, $fields->toArray()),
				'tags'      => $searchData['tags'] ?? [],
			],
		];
		
		return apiResponse()->json($data);
	}
	
	/**
	 * @param $input
	 * @param $preSearch
	 * @param $fields
	 * @return array
	 */
	protected function searchPosts($input, &$preSearch, &$fields): array
	{
		$location = $this->getLocation();
		
		$preSearch = [
			'cat'       => $this->getCategory(),
			'city'      => $location['city'] ?? null,
			'citiesIds' => $location['citiesIds'] ?? [],
			'admin'     => $location['admin'] ?? null,
		];
		
		if (!empty($preSearch['cat'])) {
			$fields = CategoryField::getFields($preSearch['cat']->id);
		}
		
		$queriesToRemove = ['op', 'embed'];
		
		return (new PostQueries($input, $preSearch))->fetch($queriesToRemove);
	}
}
