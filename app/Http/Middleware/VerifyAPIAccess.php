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

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyAPIAccess
{
	/**
	 * Handle an incoming request.
	 *
	 * Prevent any other application to call the API
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure $next
	 * @return mixed
	 */
	public function handle(Request $request, Closure $next)
	{
		if (
			!(app()->environment('local'))
			&& (
				!request()->hasHeader('X-AppApiToken')
				|| request()->header('X-AppApiToken') !== config('larapen.core.api.token')
			)
		) {
			$message = 'You don\'t have access to this API.';
			
			return apiResponse()->forbidden($message);
		}
		
		return $next($request);
	}
}
