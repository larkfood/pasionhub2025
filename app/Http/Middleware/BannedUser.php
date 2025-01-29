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

use App\Models\Blacklist;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BannedUser
{
	protected string $message = 'This user has been banned';
	
	/**
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure $next
	 * @return mixed
	 */
	public function handle(Request $request, Closure $next)
	{
		// Exception for Install & Upgrade Routes
		if (isFromInstallOrUpgradeProcess()) {
			return $next($request);
		}
		
		$guard = getAuthGuard();
		$authUser = auth($guard)->check() ? auth($guard)->user() : null;
		
		if (empty($authUser)) {
			return $next($request);
		}
		
		$this->message = t($this->message);
		
		// Block the access if a User is blocked (as registered User)
		if ($this->doesUserIsBlocked($request, $authUser)) {
			if (isFromApi()) {
				return apiResponse()->forbidden($this->message);
			}
			
			if (isFromAjax($request)) {
				return ajaxResponse()->text($this->message, Response::HTTP_UNAUTHORIZED);
			}
			
			notification($this->message, 'error');
			
			$loginUrl = isAdminPanel() ? admin_uri('login') : urlGen()->loginPath();
			
			return redirect()->guest($loginUrl);
		}
		
		// Block & Delete the access if a User is banned (from Blacklist with its email address)
		if ($this->doesUserIsBanned($request, $authUser)) {
			if (isFromApi()) {
				return apiResponse()->forbidden($this->message);
			}
			
			if (isFromAjax($request)) {
				return ajaxResponse()->text($this->message, Response::HTTP_UNAUTHORIZED);
			}
			
			notification($this->message, 'error');
			
			$loginUrl = isAdminPanel() ? admin_uri('login') : urlGen()->loginPath();
			
			return redirect()->guest($loginUrl);
		}
		
		return $next($request);
	}
	
	/**
	 * Check if the user is blocked
	 * Block the access if User is blocked (as registered User)
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param $authUser
	 * @return bool
	 */
	private function doesUserIsBlocked(Request $request, $authUser): bool
	{
		return ($authUser->blocked == 1);
	}
	
	/**
	 * Check if the user is banned
	 * Block & Delete the access if a User is banned (from Blacklist with its email address)
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param $authUser
	 * @return bool
	 */
	private function doesUserIsBanned(Request $request, $authUser): bool
	{
		$cacheExpiration = (int)config('settings.optimization.cache_expiration', 86400);
		
		// Check if the user's email address has been banned
		$cacheId = 'blacklist.email.' . $authUser->email;
		$bannedUser = cache()->remember($cacheId, $cacheExpiration, function () use($authUser) {
			return Blacklist::ofType('email')->where('entry', $authUser->email)->first();
		});
		
		if (empty($bannedUser)) return false;
		
		$user = User::find($authUser->id);
		if (empty($user)) return false;
		
		$user->delete();
		
		return true;
	}
}
