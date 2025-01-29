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
use App\Models\Role;
use App\Models\Scopes\VerifiedScope;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;

class Clearance
{
	/**
	 * Handle an incoming request.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure $next
	 * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|mixed
	 */
	public function handle(Request $request, Closure $next)
	{
		try {
			$aclTableNames = config('permission.table_names');
			if (isset($aclTableNames['permissions'])) {
				// Check if the 'permissions' table exists
				$cacheId = 'permissionsTableExists';
				$cacheExpiration = (int)config('settings.optimization.cache_expiration', 86400) * 5;
				$permissionsTableExists = cache()->remember($cacheId, $cacheExpiration, function () use ($aclTableNames) {
					return Schema::hasTable($aclTableNames['permissions']);
				});
				
				if (!$permissionsTableExists) {
					return $next($request);
				}
			}
		} catch (\Throwable $e) {
			return $next($request);
		}
		
		// If user has this //permission
		if (userHasSuperAdminPermissions()) {
			return $next($request);
		}
		
		$authUser = auth()->check() ? auth()->user() : null;
		
		// Get all routes that have permissions
		$routesPermissions = Permission::getRoutesPermissions();
		if (!empty($routesPermissions)) {
			foreach ($routesPermissions as $route) {
				if (!isset($route['uri']) || !isset($route['permission']) || !isset($route['methods'])) {
					continue;
				}
				
				// If the current route found, ...
				if ($request->is($route['uri']) && in_array($request->method(), $route['methods'])) {
					
					// Check if user has permission to perform this action
					if (!doesUserHavePermission($authUser, $route['permission'])) {
						return $this->forbidden($request);
					}
					
				}
			}
		}
		
		// If the logged admin user has permissions to manage users and has not 'super-admin' role,
		// don't allow him to manage 'super-admin' role's users.
		if (isAdminPanel() && !empty($authUser)) {
			$userController = 'Admin\UserController';
			if (
				str_contains(currentRouteAction(), $userController . '@edit')
				|| str_contains(currentRouteAction(), $userController . '@update')
				|| str_contains(currentRouteAction(), $userController . '@show')
				|| str_contains(currentRouteAction(), $userController . '@destroy')
			) {
				// Get the current possible super-admin user ID
				$userId = request()->segment(3);
				if (!empty($userId) && is_numeric($userId)) {
					// If the logged admin user has not 'Role::getSuperAdminRole()' role...
					if (!doesUserHavePermission($authUser, Permission::getSuperAdminPermissions())) {
						try {
							$user = User::query()
								->withoutGlobalScopes([VerifiedScope::class])
								->where('id', $userId)
								->role(Role::getSuperAdminRole())
								->first(['id', 'created_at']);
							
							// If the current User ID is for a user that has the 'Role::getSuperAdminRole()' role,
							// don't allow the logged user (admin) to manage him
							// (since he doesn't have 'Role::getSuperAdminRole()' role).
							if (!empty($user)) {
								return $this->forbidden($request);
							}
						} catch (\Throwable $e) {
						}
					}
				}
			}
		}
		
		return $next($request);
	}
	
	/**
	 * Forbidden Access
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
	 */
	private function forbidden(Request $request)
	{
		logoutOnClient();
		
		$message = trans('admin.forbidden');
		
		$previousUrl = urlQuery(url()->previous())->removeAllParameters()->toString();
		$currentUrl = urlQuery(url()->current())->removeAllParameters()->toString();
		$loginUrl = isFromAdminPanel() ? admin_url('login') : urlGen()->login();
		
		$doesUserNeedToBeLogout = ($previousUrl == $currentUrl || $previousUrl == $loginUrl);
		if ($doesUserNeedToBeLogout) {
			$previousUrl = urlQuery($loginUrl)->setParameters([
				'permission' => 'forbidden',
				'uid'        => uniqid('', true),
			]);
		}
		
		if (isFromAjax($request)) {
			return ajaxResponse()->text($message, Response::HTTP_FORBIDDEN);
		}
		
		notification($message, 'error');
		
		return redirect()->to($previousUrl);
	}
}
