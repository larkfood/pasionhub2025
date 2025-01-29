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

class ReferrerChecker
{
	/**
	 * Restrict access for demo referrers only (If the option is enabled).
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure $next
	 * @return mixed
	 */
	public function handle(Request $request, Closure $next)
	{
		if (env('ENABLE_ACCESS_BY_REFERRERS')) {
			return $this->allowOnlyDemoReferrers($request, $next);
		}
		
		return $next($request);
	}
	
	/**
	 * Allow only demo referrers
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure $next
	 * @return \Illuminate\Http\JsonResponse|mixed
	 */
	private function allowOnlyDemoReferrers(Request $request, Closure $next)
	{
		if (!isDemoDomain()) {
			return $next($request);
		}
		
		// Referrers patterns
		$domainPattern = str_replace('.', '\.', config('larapen.core.demo.domain'));
		$referrersPatterns = [
			'.*codecanyon\.net.*',
			'.*themeforest\.net.*',
			'.*envato\.com.*',
			'https?://' . $domainPattern,
			'https?://demo\.' . $domainPattern,
		];
		
		// First we check to see if a valid session exists
		if (!session()->has('allowMeFromReferrer')) {
			$isFromValidReferrer = isFromValidReferrer($referrersPatterns);
			if ($isFromValidReferrer) {
				session()->put('allowMeFromReferrer', 1);
			}
			
			// If the user comes from a bad referrer...
			if (!$isFromValidReferrer) {
				$message = 'Access Forbidden. Please try later.';
				if (isFromAjax()) {
					$result = [
						'success' => false,
						'msg'     => $message,
					];
					
					return ajaxResponse()->json($result, 401);
				} else {
					// Solution #1: Invite to come from a good referrer
					$url = 'https://codecanyon.net/item/' . strtolower(config('app.name')) . '/' . config('larapen.core.item.id');
					redirectUrl($url, 302, config('larapen.core.noCacheHeaders'));
					
					// Solution #2: Block access bad referrer and no session
					$this->accessForbidden($message);
				}
			}
		}
		
		return $next($request);
	}
	
	/**
	 * Print Access Forbidden message and exit
	 *
	 * @param string $message
	 */
	private function accessForbidden(string $message = 'Unauthorized')
	{
		echo '<pre>';
		print_r($message);
		echo '</pre>';
		exit();
	}
}
