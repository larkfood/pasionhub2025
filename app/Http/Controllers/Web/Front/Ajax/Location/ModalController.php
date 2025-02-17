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

class ModalController extends FrontController
{
	/**
	 * Form Select Box
	 * Get country Locations (admin1 OR admin2)
	 *
	 * @param $countryCode
	 * @param $adminType
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getAdmins($countryCode, $adminType): \Illuminate\Http\JsonResponse
	{
		// Request's inputs to remove from new URLs' query string
		$unWantedInputs = [
			'currSearch', 'r', 'country', '_token', 'l', 'location', 'languageCode',
			'countryChanged', 'adminType', 'adminCode', 'query', 'cityId', 'page',
			'countryCode', 'country_code', 'language_code', 'create_from_ip', 'embed', 'sort', 'perPage', 'admin1Code', 'q',
		];
		
		$languageCode = request()->input('languageCode', config('app.locale'));
		$countryChanged = request()->input('countryChanged', 0);
		$currSearch = unserialize(base64_decode(request()->input('currSearch')));
		$page = request()->integer('page');
		$_token = request()->input('_token');
		$query = request()->input('query');
		
		// If the country is changed, Get the selected country's name
		$country = $this->getCountry($countryCode, ($countryChanged == 1));
		
		$adminEndpoints = [
			'1' => '/countries/' . $countryCode . '/subAdmins1',
			'2' => '/countries/' . $countryCode . '/subAdmins2',
		];
		
		// If an admin type does not exist, set the default type
		if (!isset($adminEndpoints[$adminType])) {
			$adminType = 1;
		}
		
		// Get the items per page number
		$entity = ($adminType == 2) ? 'subadmin2' : 'subadmin1';
		$perPage = getNumberOfItemsPerPage($entity);
		
		// XHR data
		$result = [];
		
		// Get country's admin. divisions - Call API endpoint
		$endpoint = $adminEndpoints[$adminType];
		$queryParams = [
			'q'             => $query,
			'sort'          => '-name',
			'language_code' => $languageCode,
			// 'perPage'    => ($adminType == 2) ? 38 : 39,
			'perPage'       => $perPage,
		];
		if ($adminType == 2) {
			$queryParams['embed'] = 'subAdmin1';
		}
		if (!empty($page)) {
			$queryParams['page'] = $page;
		}
		$queryParams = array_merge(request()->all(), $queryParams);
		$headers = [
			'X-WEB-REQUEST-URL' => request()->fullUrlWithoutQuery(['page']),
		];
		$data = makeApiRequest(method: 'get', uri: $endpoint, data: $queryParams, headers: $headers);
		
		$apiMessage = $this->handleHttpError($data);
		$apiResult = data_get($data, 'result');
		
		// Remove some filters (if they exist)
		foreach ($unWantedInputs as $input) {
			if (isset($currSearch[$input])) {
				unset($currSearch[$input]);
			}
		}
		
		// Variables for location's cities view
		$data = [
			'countryCode'    => $countryCode,
			'adminType'      => $adminType,
			'languageCode'   => $languageCode,
			'apiResult'      => $apiResult ?? [],
			'apiMessage'     => $apiMessage,
			'currSearch'     => $currSearch,
			'_token'         => $_token,
			'unWantedInputs' => $unWantedInputs,
		];
		
		// Get admin. division list HTML & the country's name
		$content = getViewContent('front.layouts.inc.modal.location.admins', $data);
		$countryName = data_get($country, 'name', config('country.name'));
		
		// XHR data
		$result['isCity'] = false;
		$result['admin'] = null;
		$result['locationsTitle'] = t('locations_in_country', ['country' => $countryName]);
		$result['locationsContent'] = $content;
		
		return ajaxResponse()->json($result);
	}
	
	/**
	 * Get cities by a given admin. division's code (in Modal)
	 * Note: Administrative divisions list is prepended
	 *
	 * @param $countryCode
	 * @param $adminType
	 * @param $adminCode
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getCities($countryCode, $adminType = null, $adminCode = null): \Illuminate\Http\JsonResponse
	{
		// Request's inputs to remove from new URLs' query string
		$unWantedInputs = [
			'currSearch', 'r', 'country', '_token', 'l', 'location', 'languageCode',
			'countryChanged', 'adminType', 'adminCode', 'query', 'cityId', 'page',
			'countryCode', 'country_code', 'language_code', 'create_from_ip', 'embed', 'sort', 'perPage', 'admin1Code', 'q',
		];
		
		$languageCode = request()->input('languageCode', config('app.locale'));
		$countryChanged = request()->input('countryChanged', 0);
		$currSearch = unserialize(base64_decode(request()->input('currSearch')));
		$perPage = getNumberOfItemsPerPage('cities');
		$page = request()->integer('page');
		$_token = request()->input('_token');
		$query = request()->input('query');
		$cityId = request()->input('cityId'); // The selected city from select box
		
		// If the country is changed, Get the selected country's name
		$country = $this->getCountry($countryCode, ($countryChanged == 1));
		
		// XHR data
		$result = [];
		
		$admin = null;
		if (!is_null($adminType) && !is_null($adminCode)) {
			// Get the Administrative Division Info - Call API endpoint
			$endpoint = '/subAdmins' . $adminType . '/' . $adminCode;
			$queryParams = [];
			if ($adminType == 2) {
				$queryParams['embed'] = 'subAdmin1';
			}
			$queryParams = array_merge(request()->all(), $queryParams);
			$data = makeApiRequest(method: 'get', uri: $endpoint, data: $queryParams);
			
			$apiMessage = $this->handleHttpError($data);
			$admin = data_get($data, 'result');
		}
		
		// Get the Administrative Division's Cities - Call API endpoint
		$endpoint = '/countries/' . $countryCode . '/cities';
		$queryParams = [
			'embed'         => 'subAdmin1,subAdmin2',
			'q'             => $query,
			'sort'          => [
				0 => 'population',
				1 => '-name',
			],
			'language_code' => $languageCode,
			'perPage'       => $perPage,
		];
		if (!empty($adminCode)) {
			$adminCodeQs = 'admin' . $adminType . 'Code';
			$queryParams['adminType'] = $adminType;
			$queryParams[$adminCodeQs] = $adminCode;
		}
		if (!empty($page)) {
			$queryParams['page'] = $page;
		}
		$queryParams = array_merge(request()->all(), $queryParams);
		$headers = [
			'X-WEB-REQUEST-URL' => request()->fullUrlWithoutQuery(['page']),
		];
		$data = makeApiRequest(method: 'get', uri: $endpoint, data: $queryParams, headers: $headers);
		
		$apiMessage = $this->handleHttpError($data);
		$apiResult = data_get($data, 'result');
		
		// Get current city ID (If exists) - From a link
		if (!empty($currSearch['l'])) {
			$cityId = $currSearch['l'];
		}
		
		// Remove some filters (if they exist)
		foreach ($unWantedInputs as $input) {
			if (isset($currSearch[$input])) {
				unset($currSearch[$input]);
			}
		}
		
		// Variables for location's cities view
		$data = [
			'countryCode'    => $countryCode,
			'adminType'      => $adminType,
			'adminCode'      => $adminCode,
			'languageCode'   => $languageCode,
			'admin'          => $admin,
			'apiResult'      => $apiResult ?? [],
			'apiMessage'     => $apiMessage,
			'currSearch'     => $currSearch,
			'cityId'         => $cityId,
			'_token'         => $_token,
			'unWantedInputs' => $unWantedInputs,
		];
		
		// Get cities' list HTML & the country's name
		$content = getViewContent('front.layouts.inc.modal.location.cities', $data);
		$countryName = data_get($country, 'name', config('country.name'));
		
		// Get locations base (regions) URL
		$baseUrl = url('ajax/locations/' . $countryCode . '/admins/' . $adminType);
		
		// Get subtitle
		if (!empty($adminCode)) {
			if (!empty($admin)) {
				$adminName = data_get($admin, 'name');
				if ($adminType == 2) {
					$admin1Name = data_get($admin, 'subAdmin1.name');
					$adminName = !empty($admin1Name) ? $adminName . ', ' . $admin1Name : $adminName;
				}
				
				$title = '<a href="" data-url="' . $baseUrl . '" class="btn btn-sm btn-success is-admin go-base-url">';
				$title .= '<i class="fa-solid fa-reply"></i> ' . t('all_regions', [], 'global', $languageCode);
				$title .= '</a>&nbsp;';
				$title .= t('popular_cities_in_location', ['location' => $adminName]);
			} else {
				$title = t('locations_in_country', ['country' => $countryName]);
			}
		} else {
			$countryAdminType = !empty($adminType) ? $adminType : config('country.admin_type', 0);
			
			$title = '';
			if (in_array($countryAdminType, ['1', '2'])) {
				$goBaseUrl = url('ajax/locations/' . $countryCode . '/admins/' . $countryAdminType);
				
				$title .= '<a href="" data-url="' . $goBaseUrl . '" class="btn btn-sm btn-success is-admin go-base-url">';
				$title .= '<i class="fa-solid fa-reply"></i> ' . t('cities_per_region', [], 'global', $languageCode);
				$title .= '</a>&nbsp;';
			}
			$title .= t('cities_in_location', ['location' => $countryName]);
		}
		
		// XHR data
		$result['isCity'] = true;
		$result['admin'] = $admin;
		$result['locationsTitle'] = $title;
		$result['locationsContent'] = $content;
		
		return ajaxResponse()->json($result);
	}
	
	/**
	 * If the country is changed, Get the selected country's name
	 *
	 * @param string|null $countryCode
	 * @param bool $countryChanged
	 * @return array
	 */
	private function getCountry(?string $countryCode, bool $countryChanged = false): array
	{
		$country = null;
		if ($countryChanged) {
			// Get the new country's info - Call API endpoint
			$endpoint = '/countries/' . $countryCode;
			$data = makeApiRequest(method: 'get', uri: $endpoint);
			
			$apiMessage = $this->handleHttpError($data);
			$country = data_get($data, 'result');
		}
		
		return is_array($country) ? $country : [];
	}
}
