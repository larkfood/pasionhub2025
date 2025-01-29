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
use Closure;
use Illuminate\Http\Request;

class DemoRestriction
{
	/**
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure $next
	 * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|mixed
	 */
	public function handle(Request $request, Closure $next)
	{
		if (!isDemoDomain()) {
			return $next($request);
		}
		
		if (!$this->isRestricted()) {
			return $next($request);
		}
		
		$message = t('demo_mode_message');
		
		if (isFromApi()) {
			
			$result = [
				'success' => false,
				'message' => $message,
				'result'  => null,
			];
			
			return response()->json($result, 403, [], JSON_UNESCAPED_UNICODE);
			
		} else {
			if (isFromAjax($request)) {
				$result = [
					'success' => false,
					'error'   => $message,
				];
				
				return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE);
			} else {
				notification($message, 'info');
				
				return redirect()->back();
			}
		}
	}
	
	/**
	 * @return bool
	 */
	private function isRestricted(): bool
	{
		$isRestricted = false;
		
		$frontRoutesRestricted = $this->frontRoutesRestricted();
		foreach ($frontRoutesRestricted as $route) {
			if (str_contains(currentRouteAction(), $route)) {
				$isRestricted = true;
				break;
			}
		}
		
		$guard = getAuthGuard();
		$authUser = auth($guard)->check() ? auth($guard)->user() : null;
		
		if (!empty($authUser)) {
			if (
				doesUserHavePermission($authUser, Permission::getStaffPermissions())
				&& isDemoSuperAdmin($authUser)
			) {
				return false;
			}
			
			$adminRoutesRestricted = $this->adminRoutesRestricted();
			foreach ($adminRoutesRestricted as $route) {
				if (
					(
						str_starts_with($route, '@')
						&& str_contains(currentRouteAction(), 'Admin\\')
						&& str_contains(currentRouteAction(), $route)
					)
					|| (
						!str_starts_with($route, '@')
						&& str_contains(currentRouteAction(), $route)
					)
				) {
					$isRestricted = true;
					break;
				}
			}
			
			if (isDemoEmailAddress($authUser->email ?? null)) {
				$demoUsersRoutesRestricted = $this->demoUsersRoutesRestricted();
				foreach ($demoUsersRoutesRestricted as $route) {
					if (str_contains(currentRouteAction(), $route)) {
						$isRestricted = true;
						break;
					}
				}
			}
		}
		
		return $isRestricted;
	}
	
	/**
	 * @return string[]
	 */
	private function frontRoutesRestricted(): array
	{
		return [
			// api
			'Api\ContactController@sendForm',
			'Api\ContactController@sendReport',
			'Api\ContactController@submitForm',
			'Api\ContactController@submitReport',
			//'Api\ThreadController@store',
			
			// web
			'Web\Front\Page\ContactController@postForm',
			'Web\Front\Post\ReportController@sendReport',
			'Web\Front\Page\ContactController@submitForm',
			'Web\Front\Post\ReportController@submitReport',
			//'Web\Front\Account\MessagesController@store',
		];
	}
	
	/**
	 * @return string[]
	 */
	private function adminRoutesRestricted(): array
	{
		return [
			// admin
			'@store',
			'@update',
			'@destroy',
			'@saveReorder',
			'@reSendEmailVerification',
			'@reSendPhoneVerification',
			'Admin\RoleController@store',
			'Admin\RoleController@update',
			'Admin\RoleController@destroy',
			'Admin\PermissionController@store',
			'Admin\PermissionController@update',
			'Admin\PermissionController@destroy',
			'Admin\ActionController',
			'Admin\BackupController@create',
			'Admin\BackupController@download',
			'Admin\BackupController@delete',
			'Admin\BlacklistController@banUser',
			'Admin\SectionController@reset',
			'Admin\InlineRequestController',
			'Admin\LanguageController@syncFilesLines',
			'Admin\LanguageController@update',
			'Admin\LanguageController@updateTexts',
			'Admin\PluginController@install',
			'Admin\PluginController@installWithCode',
			'Admin\PluginController@installWithoutCode',
			'Admin\PluginController@uninstall',
			'Admin\PluginController@delete',
			
			// impersonate
			'Larapen\Impersonate\Controllers\ImpersonateController',
			
			// plugins:domainmapping
			'domainmapping\app\Http\Controllers\Web\Admin\DomainController@createBulkCountriesSubDomain',
			'domainmapping\app\Http\Controllers\Web\Admin\DomainSectionController@generate',
			'domainmapping\app\Http\Controllers\Web\Admin\DomainSectionController@reset',
			'domainmapping\app\Http\Controllers\Web\Admin\DomainMetaTagController@generate',
			'domainmapping\app\Http\Controllers\Web\Admin\DomainMetaTagController@reset',
			'domainmapping\app\Http\Controllers\Web\Admin\DomainSettingController@generate',
			'domainmapping\app\Http\Controllers\Web\Admin\DomainSettingController@reset',
		];
	}
	
	/**
	 * @return string[]
	 */
	private function demoUsersRoutesRestricted(): array
	{
		return [
			// api
			'Api\UserController@update',
			'Api\UserController@destroy',
			'Api\UserController@updateSettings',
			'Api\UserController@updatePhoto',
			'Api\UserController@removePhoto',
			'Api\UserController@setDarkMode',
			'Api\PostController@destroy',
			
			// web
			'Account\DashboardController@updateDetails',
			'Account\DashboardController@updateSettings',
			'Account\DashboardController@updatePhoto',
			'Account\DashboardController@deletePhoto',
			'Account\CloseController@submit',
			'Account\PostsController@destroy',
		];
	}
}
