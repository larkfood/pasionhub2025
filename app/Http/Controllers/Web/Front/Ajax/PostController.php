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

namespace App\Http\Controllers\Web\Front\Ajax;

use App\Http\Controllers\Web\Front\FrontController;
use Illuminate\Http\Request;
use Larapen\TextToImage\Facades\TextToImage;

class PostController extends FrontController
{
	/**
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function savePost(Request $request): \Illuminate\Http\JsonResponse
	{
		// Call API endpoint
		$endpoint = '/savedPosts';
		$data = makeApiRequest(method: 'post', uri: $endpoint, data: $request->all());
		
		// Parsing the API response
		$status = (int)data_get($data, 'status');
		$message = data_get($data, 'message', t('unknown_error'));
		
		// HTTP Error Found
		if (!data_get($data, 'isSuccessful')) {
			return ajaxResponse()->json(['message' => $message], $status);
		}
		
		// Get entry resource
		$savedPost = data_get($data, 'result');
		
		// AJAX response data
		$result = [
			'isLogged' => !($status == 401), // No longer used. Will be removed.
			'postId'   => $request->input('post_id'),
			'isSaved'  => !empty($savedPost),
			'message'  => $message,
			'loginUrl' => urlGen()->login(), // No longer used. Will be removed.
		];
		
		return ajaxResponse()->json($result, $status);
	}
	
	/**
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function saveSearch(Request $request): \Illuminate\Http\JsonResponse
	{
		// Call API endpoint
		$endpoint = '/savedSearches';
		$data = makeApiRequest(method: 'post', uri: $endpoint, data: $request->all());
		
		// Parsing the API response
		$status = (int)data_get($data, 'status');
		$message = data_get($data, 'message', t('unknown_error'));
		
		// HTTP Error Found
		if (!data_get($data, 'isSuccessful')) {
			return ajaxResponse()->json(['message' => $message], $status);
		}
		
		// Validate data extraction
		$query = null;
		$queryUrl = $request->input('search_url');
		if (!empty($queryUrl)) {
			$tmp = parse_url($queryUrl);
			$query = $tmp['query'] ?? null;
		}
		if (empty($query)) {
			$errorMsg = 'The "query" parameter cannot not be extracted.';
			
			return ajaxResponse()->json(['message' => $errorMsg], 400);
		}
		
		// Get entry resource
		$savedSearch = data_get($data, 'result');
		
		// AJAX response data
		$result = [
			'isLogged' => !($status == 401), // No longer used. Will be removed.
			'query'    => $query,
			'isSaved'  => !empty($savedSearch),
			'message'  => $message,
			'loginUrl' => urlGen()->login(), // No longer used. Will be removed.
		];
		
		return ajaxResponse()->json($result, $status);
	}
	
	/**
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getPhone(Request $request): \Illuminate\Http\JsonResponse
	{
		// Call API endpoint
		$endpoint = '/posts/' . $request->input('post_id', 0);
		$queryParams = [
			'unactivatedIncluded' => 1,
		];
		$queryParams = array_merge(request()->all(), $queryParams);
		$data = makeApiRequest(method: 'get', uri: $endpoint, data: $queryParams);
		
		// Parsing the API response
		$status = (int)data_get($data, 'status');
		$message = data_get($data, 'message', t('unknown_error'));
		
		// HTTP Error Found
		if (!data_get($data, 'isSuccessful')) {
			return ajaxResponse()->json(['message' => $message], $status);
		}
		
		// Get entry resource
		$post = data_get($data, 'result');
		
		// Get the phone
		$phone = data_get($post, 'phone');
		$phoneIntl = data_get($post, 'phone_intl');
		$phoneModal = $phoneIntl;
		$phoneLink = 'tel:' . $phone;
		
		if (config('settings.listing_page.convert_phone_number_to_img')) {
			try {
				$phone = TextToImage::make($phoneIntl, config('larapen.core.textToImage'));
			} catch (\Throwable $e) {
				$phone = data_get($post, 'phone_intl');
			}
		}
		
		if (config('settings.listing_page.show_security_tips') == '1') {
			$phone = t('phone_number');
		}
		
		$data = [
			'phone'      => $phone,
			'phoneModal' => $phoneModal,
			'link'       => $phoneLink,
		];
		
		return ajaxResponse()->json($data);
	}
}
