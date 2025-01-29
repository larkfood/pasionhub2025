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

use App\Helpers\Cookie;
use App\Http\Controllers\Web\Front\FrontController;
use Illuminate\Http\Request;

class UserController extends FrontController
{
	/**
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function setDarkMode(Request $request): \Illuminate\Http\JsonResponse
	{
		$darkMode = $request->integer('dark_mode');
		$userId = $request->input('user_id');
		
		$status = 200;
		$message = null;
		
		if (auth()->check()) {
			// Call API endpoint
			$endpoint = '/users/' . $userId . '/dark-mode';
			$data = makeApiRequest(method: 'put', uri: $endpoint, data: $request->all(), files: $request->allFiles());
			
			// Parsing the API response
			$status = (int)data_get($data, 'status');
			$message = data_get($data, 'message', t('unknown_error'));
			
			// HTTP Error Found
			if (!data_get($data, 'isSuccessful')) {
				return ajaxResponse()->json(['message' => $message], $status);
			}
			
			// Get entry resource
			$user = data_get($data, 'result');
			$darkMode = (int)data_get($user, 'dark_mode', 0);
		}
		
		// Set or remove dark mode cookie
		if ($darkMode == 1) {
			Cookie::set('darkTheme', 'dark');
			$message = !empty($message) ? $message : t('dark_mode_is_set');
		} else {
			Cookie::forget('darkTheme');
			$message = !empty($message) ? $message : t('dark_mode_is_disabled');
		}
		
		// AJAX response data
		$result = [
			'userId'   => $request->integer('user_id'),
			'darkMode' => $darkMode,
			'message'  => $message,
		];
		
		return ajaxResponse()->json($result, $status);
	}
}
