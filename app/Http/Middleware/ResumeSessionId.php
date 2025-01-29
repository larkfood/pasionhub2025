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

class ResumeSessionId
{
	/**
	 * Resume a saved session from an external referrer
	 *
	 * The session()->save() function needs to be called just before adding
	 * the '?sessionId=' . session()->getId() string to the external URL
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure $next
	 * @return mixed
	 */
	public function handle(Request $request, Closure $next)
	{
		// Get the session ID from the query parameters
		if ($request->filled('sessionId')) {
			$sessionId = getAsStringOrNull($request->input('sessionId'));
			
			// Resume the session
			if (!empty($sessionId)) {
				session()->setId($sessionId);
				session()->start();
			}
		}
		
		return $next($request);
	}
}
