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

namespace App\Exceptions\Handler;

use App\Helpers\Cookie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/*
 * Try to fix the cookies issue related the Laravel security release:
 * https://laravel.com/docs/5.6/upgrade#upgrade-5.6.30
 */

trait UnserializeExceptionHandler
{
	/**
	 * @param \Throwable $e
	 * @return bool
	 */
	protected function isUnserializeException(\Throwable $e): bool
	{
		return str_contains($e->getMessage(), 'unserialize()');
	}
	
	/**
	 * @param \Throwable $e
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
	 */
	protected function responseUnserializeException(\Throwable $e, Request $request): Response|JsonResponse|RedirectResponse
	{
		// All cookies need to be removed (Only for AJAX requests)
		// Note: API calls don't support cookies or session
		if (isFromAjax($request) && !isFromApi($request)) {
			Cookie::forgetAll();
		}
		
		// Generate a new App Key
		updateAppKeyWithArtisan();
		
		if (!isFromApi($request) && !isFromAjax($request)) {
			$previousUrl = $this->getUnserializeExceptionPreviousUrl();
			if (!empty($previousUrl)) {
				redirectUrl($previousUrl, 301, config('larapen.core.noCacheHeaders'));
			}
		}
		
		// Get status code
		$status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
		$status = isValidHttpStatus($status) ? $status : 500;
		
		// Get error message
		$message = $e->getMessage();
		$message = empty($message) ? getHttpStatusMessage($status) : $message;
		
		return $this->responseCustomError($e, $request, $message, $status);
	}
	
	// PRIVATE
	
	/**
	 * @return string|null
	 */
	private function getUnserializeExceptionPreviousUrl(): ?string
	{
		$previousUrl = url()->previous();
		
		$param = 'exception=unserialize';
		if (!str_contains($previousUrl, $param)) {
			$queryString = (parse_url($previousUrl, PHP_URL_QUERY) ? '&' : '?') . $param;
			
			return $previousUrl . $queryString;
		}
		
		return null;
	}
}
