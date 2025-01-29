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

namespace App\Http\Controllers\Web\Front\Account;

use App\Http\Controllers\Web\Front\FrontController;
use Illuminate\Support\Collection;

abstract class AccountBaseController extends FrontController
{
	/**
	 * AccountBaseController constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		
		if (auth()->check()) {
			$this->leftMenuInfo();
		}
		
		// Get Page Current Path
		$pagePath = (request()->segment(1) == 'account') ? (request()->segment(3) ?? '') : '';
		view()->share('pagePath', $pagePath);
	}
	
	public function leftMenuInfo(): void
	{
		$authUser = auth()->user();
		if (empty($authUser)) return;
		
		// Get user's stats - Call API endpoint
		$endpoint = '/users/' . $authUser->getAuthIdentifier() . '/stats';
		$data = makeApiRequest(method: 'get', uri: $endpoint);
		
		// Retrieve the user's stats
		$userStats = data_get($data, 'result');
		
		// Format the account's sidebar menu
		$accountMenu = collect();
		if (isset($this->userMenu)) {
			$accountMenu = $this->userMenu->groupBy('group');
			$accountMenu = $accountMenu->map(function ($group, $k) use ($userStats) {
				return $group->map(function ($item, $key) use ($userStats) {
					$isActive = (isset($item['isActive']) && $item['isActive']);
					$countVar = isset($item['countVar']) ? data_get($userStats, $item['countVar']) : null;
					$cssClass = !empty($item['countCustomClass']) ? $item['countCustomClass'] . ' hide' : '';
					
					$item['isActive'] = $isActive;
					$item['countVar'] = $countVar;
					$item['cssClass'] = $cssClass;
					
					return $item;
				})->reject(function ($item, $key) {
					return (is_numeric($item['countVar']) && $item['countVar'] < 0);
				});
			})->reject(function ($group, $k) {
				return ($group instanceof Collection) ? $group->isEmpty() : empty($group);
			});
		}
		
		// Export data to views
		view()->share('userStats', $userStats);
		view()->share('accountMenu', $accountMenu);
	}
}
