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
use Illuminate\Auth\Middleware\RedirectIfAuthenticated as Middleware;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated extends Middleware
{
	/**
	 * Handle an incoming request.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
	 * @param string ...$guards
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function handle(Request $request, Closure $next, string ...$guards): Response
	{
		// Redirections are not supported by API and AJAX requests
		if (isFromApi($request) || $request->expectsJson()) {
			return $next($request);
		}
		
		$guards = empty($guards) ? [null] : $guards;
		
		foreach ($guards as $guard) {
			if (auth()->guard($guard)->check()) {
				// Ensure to not redirect unverified users to avoid an infinite redirect loop
				$authUser = auth()->guard($guard)->user();
				if (!isVerifiedUser($authUser)) {
					return $next($request);
				}
				
				/*
				 * This middleware is applied only to the login, password forgot, and reset pages.
				 * Therefore, users should be redirected to another page, such as the homepage.
				 * Make sure not to apply this middleware (alias: 'guest') to the homepage to prevent an infinite redirect loop.
				 */
				return redirect()->to($this->redirectTo($request));
			}
		}
		
		return $next($request);
	}
	
	/**
	 * Get the path the user should be redirected to when they are authenticated.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return string|null
	 */
	protected function redirectTo(Request $request): ?string
	{
		if (isFromApi()) return null;
		if ($request->expectsJson()) return null;
		
		$url = isFromAdminPanel() ? admin_url() : url('/');
		
		return urlQuery($url)
			->setParameters(['login' => 'success'])
			->toString();
	}
}
