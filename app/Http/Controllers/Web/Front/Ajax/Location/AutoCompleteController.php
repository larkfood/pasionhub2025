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

namespace App\Http\Controllers\Web\Front\Ajax\Location;

use App\Http\Controllers\Web\Front\FrontController;
use Illuminate\Http\JsonResponse;

class AutoCompleteController extends FrontController
{
	/**
	 * Autocomplete Cities
	 *
	 * @param $countryCode
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index($countryCode): JsonResponse
	{
		$languageCode = request()->input('languageCode', config('app.locale'));
		$query = request()->input('query');
		$limit = getNumberOfItemsToTake('auto_complete_cities');
		
		$citiesList = [];
		$result = [
			'query'       => $query,
			'suggestions' => $citiesList,
		];
		
		if (mb_strlen($query) <= 0) {
			return ajaxResponse()->json($result);
		}
		
		// Get country's cities - Call API endpoint
		$endpoint = '/countries/' . $countryCode . '/cities';
		$queryParams = [
			'embed'         => 'subAdmin1,subAdmin2',
			'q'             => $query,
			'autocomplete'  => 1,
			'sort'          => '-name',
			'language_code' => $languageCode,
			'perPage'       => $limit,
		];
		if (!empty($page)) {
			$queryParams['page'] = $page;
		}
		$queryParams = array_merge(request()->all(), $queryParams);
		$headers = [
			'X-WEB-REQUEST-URL' => request()->fullUrlWithQuery(['query' => $query]),
		];
		$data = makeApiRequest(method: 'get', uri: $endpoint, data: $queryParams, headers: $headers);
		
		$apiMessage = $this->handleHttpError($data);
		$apiResult = data_get($data, 'result');
		
		$cities = data_get($apiResult, 'data');
		
		// No cities found
		if (empty($cities)) {
			$status = (int)data_get($data, 'status', 200);
			$status = isValidHttpStatus($status) ? $status : 200;
			$result['message'] = $apiMessage;
			
			return ajaxResponse()->json($result, $status);
		}
		
		// Get & formats cities
		foreach ($cities as $city) {
			$cityName = data_get($city, 'name');
			$admin2Name = data_get($city, 'subAdmin2.name');
			$admin1Name = data_get($city, 'subAdmin1.name');
			
			$adminName = !empty($admin2Name) ? $admin2Name : (!empty($admin1Name) ? $admin1Name : '');
			// $cityNameDetailed = !empty($adminName) ? $cityName . ', ' . $adminName : $cityName;
			
			$citiesList[] = [
				'id'    => data_get($city, 'id'),
				'name'  => $cityName,
				'admin' => $adminName,
			];
		}
		
		// XHR Data
		$result['query'] = $query;
		$result['suggestions'] = $citiesList;
		
		return ajaxResponse()->json($result);
	}
}
