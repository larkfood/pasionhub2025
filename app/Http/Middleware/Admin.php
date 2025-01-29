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

use App\Models\Permission;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;

class Admin
{
	/**
	 * Handle an incoming request.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure $next
	 * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|mixed
	 */
	public function handle(Request $request, Closure $next)
	{
		$message = trans('admin.unauthorized');
		
		$guard = getAuthGuard();
		$authUser = auth($guard)->check() ? auth($guard)->user() : null;
		
		if (empty($authUser)) {
			// Block access if user is guest (not logged in)
			if (isFromAjax($request)) {
				return ajaxResponse()->text($message, Response::HTTP_UNAUTHORIZED);
			} else {
				if ($request->path() != admin_uri('login')) {
					notification($message, 'error');
					
					return redirect()->guest(admin_uri('login'));
				}
			}
		} else {
			try {
				$aclTableNames = config('permission.table_names');
				if (isset($aclTableNames['permissions'])) {
					if (!Schema::hasTable($aclTableNames['permissions'])) {
						return $next($request);
					}
				}
			} catch (\Throwable $e) {
				return $next($request);
			}
			
			$user = User::query()->count();
			if (!($user == 1)) {
				// If user does //not have this permission
				if (!doesUserHavePermission($authUser, Permission::getStaffPermissions())) {
					if (isFromAjax($request)) {
						return ajaxResponse()->text($message, Response::HTTP_UNAUTHORIZED);
					} else {
						auth($guard)->logout();
						notification($message, 'error');
						
						return redirect()->guest(admin_uri('login'));
					}
				}
			}
		}
		
		return $next($request);
	}
}
