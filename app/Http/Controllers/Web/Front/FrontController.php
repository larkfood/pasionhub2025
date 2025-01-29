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

namespace App\Http\Controllers\Web\Front;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Front\Traits\CommonTrait;
use App\Http\Controllers\Web\Front\Traits\EnvFileTrait;
use App\Http\Controllers\Web\Front\Traits\RobotsTxtTrait;
use App\Http\Controllers\Web\Front\Traits\SettingsTrait;
use App\Models\Permission;
use Illuminate\Support\Collection;

class FrontController extends Controller
{
	use SettingsTrait, EnvFileTrait, RobotsTxtTrait, CommonTrait;
	
	public $request;
	public $data = [];
	protected Collection $userMenu;
	
	/**
	 * FrontController constructor.
	 *
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	public function __construct()
	{
		// Set the storage disk
		$this->setStorageDisk();
		
		// Check & Change the App Key (If needed)
		$this->checkAndGenerateAppKey();
		
		// Check & Update the '/.env' file
		$this->checkDotEnvEntries();
		
		// Check & Update the '/public/robots.txt' file
		$this->checkRobotsTxtFile();
		
		// Load Localization Data first
		// Check out the SetCountryLocale Middleware
		$this->applyFrontSettings();
		
		// Get & Share Users Menu
		$this->userMenu = $this->getUserMenu();
		view()->share('userMenu', $this->userMenu);
	}
	
	/**
	 * Get the middleware that should be assigned to the controller.
	 */
	public static function middleware(): array
	{
		$array = [];
		
		// Add the 'Currency Exchange' plugin middleware
		if (config('plugins.currencyexchange.installed')) {
			$array[] = 'currencies';
			$array[] = 'currencyExchange';
		}
		
		// Add the 'Domain Mapping' plugin middleware
		if (config('plugins.domainmapping.installed')) {
			$array[] = 'domain.verification';
		}
		
		return array_merge(parent::middleware(), $array);
	}
	
	/*
	 * Handle HTTP error for GET requests
	 */
	protected function handleHttpError(?array $data = [])
	{
		// Parsing the API response
		$message = !empty(data_get($data, 'message')) ? data_get($data, 'message') : null;
		
		// HTTP Error Found
		if (!data_get($data, 'isSuccessful')) {
			$message = !empty($message) ? $message : t('unknown_error');
			$errorCode = (int)data_get($data, 'status');
			$errorCode = (strlen($errorCode) == 3) ? $errorCode : 400;
			
			abort($errorCode, $message);
		}
		
		return $message;
	}
	
	/*
	 * Handle HTTP error for non GET requests
	 * @todo: Check the redirect can be done externally
	 */
	protected function handleHttpErrorWithRedirect(?array $data = [], $withInput = [])
	{
		// Parsing the API response
		$message = data_get($data, 'message', t('unknown_error'));
		
		// HTTP Error Found
		if (!data_get($data, 'isSuccessful')) {
			flash($message)->error();
			
			if (!empty($withInput)) {
				return redirect()->back()->withInput($withInput);
			} else {
				return redirect()->back();
			}
		}
		
		return $message;
	}
	
	/**
	 * @return \Illuminate\Support\Collection
	 */
	private function getUserMenu(): \Illuminate\Support\Collection
	{
		if (!auth()->check()) {
			return collect();
		}
		
		$authUser = auth()->user();
		
		$menuArray = [
			[
				'name'       => t('my_listings'),
				'url'        => url('account/posts/list'),
				'icon'       => 'fa-solid fa-list',
				'group'      => t('my_listings'),
				'countVar'   => 'posts.published',
				'inDropdown' => true,
				'isActive'   => (request()->segment(3) == 'list'),
			],
			[
				'name'       => t('pending_approval'),
				'url'        => url('account/posts/pending-approval'),
				'icon'       => 'fa-solid fa-hourglass-half',
				'group'      => t('my_listings'),
				'countVar'   => 'posts.pendingApproval',
				'inDropdown' => true,
				'isActive'   => (request()->segment(3) == 'pending-approval'),
			],
			[
				'name'       => t('archived_listings'),
				'url'        => url('account/posts/archived'),
				'icon'       => 'fa-solid fa-calendar-xmark',
				'group'      => t('my_listings'),
				'countVar'   => 'posts.archived',
				'inDropdown' => true,
				'isActive'   => (request()->segment(3) == 'archived'),
			],
			[
				'name'       => t('favourite_listings'),
				'url'        => url('account/posts/favourite'),
				'icon'       => 'fa-solid fa-bookmark',
				'group'      => t('my_listings'),
				'countVar'   => 'posts.favourite',
				'inDropdown' => true,
				'isActive'   => (request()->segment(3) == 'favourite'),
			],
			[
				'name'             => t('messenger'),
				'url'              => url('account/messages'),
				'icon'             => 'fa-regular fa-envelope',
				'group'            => t('my_listings'),
				'countVar'         => 0,
				'countCustomClass' => ' count-threads-with-new-messages',
				'inDropdown'       => true,
				'isActive'         => (request()->segment(2) == 'messages'),
			],
			[
				'name'       => t('Saved searches'),
				'url'        => url('account/saved-searches'),
				'icon'       => 'fa-solid fa-bell',
				'group'      => t('my_listings'),
				'countVar'   => 'savedSearch',
				'inDropdown' => true,
				'isActive'   => (request()->segment(2) == 'saved-searches'),
			],
			[
				'name'       => t('promotion'),
				'url'        => url('account/transactions/promotion'),
				'icon'       => 'fa-solid fa-coins',
				'group'      => t('Transactions'),
				'countVar'   => 'transactions.promotion',
				'inDropdown' => false,
				'isActive'   => (request()->segment(2) == 'transactions' && request()->segment(3) == 'promotion'),
			],
			[
				'name'       => t('subscription'),
				'url'        => url('account/transactions/subscription'),
				'icon'       => 'fa-solid fa-coins',
				'group'      => t('Transactions'),
				'countVar'   => 'transactions.subscription',
				'inDropdown' => false,
				'isActive'   => (request()->segment(2) == 'transactions' && request()->segment(3) == 'subscription'),
			],
			[
				'name'       => t('My Account'),
				'url'        => url('account'),
				'icon'       => 'fa-solid fa-gear',
				'group'      => t('My Account'),
				'countVar'   => null,
				'inDropdown' => true,
				'isActive'   => (request()->segment(1) == 'account' && request()->segment(2) == null),
			],
		];
		
		if (app('impersonate')->isImpersonating()) {
			$logOut = [
				'name'       => t('Leave'),
				'url'        => route('impersonate.leave'),
				'icon'       => 'fa-solid fa-right-from-bracket',
				'group'      => t('My Account'),
				'countVar'   => null,
				'inDropdown' => true,
				'isActive'   => false,
			];
		} else {
			$logOut = [
				'name'       => t('log_out'),
				'url'        => urlGen()->logout(),
				'icon'       => 'fa-solid fa-right-from-bracket',
				'group'      => t('My Account'),
				'countVar'   => null,
				'inDropdown' => true,
				'isActive'   => false,
			];
		}
		
		$closeAccount = [];
		if (isAccountClosureEnabled()) {
			$closeAccount = [
				'name'       => t('Close account'),
				'url'        => url('account/close'),
				'icon'       => 'fa-solid fa-circle-xmark',
				'group'      => t('My Account'),
				'countVar'   => null,
				'inDropdown' => false,
				'isActive'   => (request()->segment(2) == 'close'),
			];
		}
		
		$adminPanel = [];
		if (doesUserHavePermission($authUser, Permission::getStaffPermissions())) {
			$adminPanel = [
				'name'       => t('admin_panel'),
				'url'        => admin_url('/'),
				'icon'       => 'fa-solid fa-gears',
				'group'      => t('admin_panel'),
				'countVar'   => null,
				'inDropdown' => true,
				'isActive'   => false,
			];
		}
		
		// Merge all arrays
		array_push($menuArray, $logOut, $closeAccount, $adminPanel);
		
		// Set missed information
		return collect($menuArray)
			->reject(fn ($item) => empty($item))
			->map(function ($item) {
				// countCustomClass
				$item['countCustomClass'] = (isset($item['countCustomClass'])) ? $item['countCustomClass'] : '';
				
				// path
				$matches = [];
				preg_match('|(account.*)|ui', $item['url'], $matches);
				$item['path'] = $matches[1] ?? '-1';
				$item['path'] = str_replace(['account', '/'], '', $item['path']);
				
				return $item;
			});
	}
}
