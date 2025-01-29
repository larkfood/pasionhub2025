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

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Web\Admin\Traits\Charts\ChartjsTrait;
use App\Http\Controllers\Web\Admin\Traits\Charts\MorrisTrait;
use App\Models\Post;
use App\Models\Country;
use App\Models\User;
use App\Http\Controllers\Web\Admin\Panel\PanelController;

class DashboardController extends PanelController
{
	use MorrisTrait, ChartjsTrait;
	
	public $data = [];
	
	protected int $countCountries = 0;
	
	/**
	 * Create a new controller instance.
	 */
	public function __construct()
	{
		parent::__construct();
		
		// Get the Mini Stats data
		try {
			$countActivatedPosts = Post::verified()->withoutAppends()->count();
			$countUnactivatedPosts = Post::unverified()->withoutAppends()->count();
			$countActivatedUsers = User::doesntHave('permissions')->verified()->withoutAppends()->count();
			$countUnactivatedUsers = User::doesntHave('permissions')->unverified()->withoutAppends()->count();
			$countUsers = User::doesntHave('permissions')->withoutAppends()->count();
			$this->countCountries = Country::where('active', 1)->withoutAppends()->count();
		} catch (\Throwable $e) {
		}
		
		view()->share('countActivatedPosts', $countActivatedPosts ?? 0);
		view()->share('countUnactivatedPosts', $countUnactivatedPosts ?? 0);
		view()->share('countActivatedUsers', $countActivatedUsers ?? 0);
		view()->share('countUnactivatedUsers', $countUnactivatedUsers ?? 0);
		view()->share('countUsers', $countUsers ?? 0);
		view()->share('countCountries', $this->countCountries ?? 0);
	}
	
	/**
	 * Get the middleware that should be assigned to the controller.
	 */
	public static function middleware(): array
	{
		$array = ['admin'];
		
		return array_merge(parent::middleware(), $array);
	}
	
	/**
	 * Show the admin dashboard.
	 *
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function dashboard()
	{
		// Dashboard's Latest Entries Chart: 'bar' or 'line'
		$tmp = @explode('_', config('settings.app.vector_charts_type'));
		
		// Get the chart provider
		$chartProvider = data_get($tmp, '0');
		$chartProvider = !empty($chartProvider) ? $chartProvider : 'morris';
		
		// Get the chart type
		$chartType = data_get($tmp, '1');
		$chartType = !empty($chartType) ? $chartType : 'bar';
		
		// Set chart infos
		$this->data['chartsType'] = [
			'provider' => $chartProvider,
			'type'     => $chartType,
		];
		
		// ---------------------------------
		// Line or Bar Charts
		// ---------------------------------
		
		// Get latest entries charts
		$statsDaysNumber = (int)config('settings.app.vector_charts_limit', 7);
		
		$getLatestListingsChartMethod = 'getLatestListingsFor' . ucfirst($chartProvider);
		if (method_exists($this, $getLatestListingsChartMethod)) {
			$this->data['latestPostsChart'] = $this->$getLatestListingsChartMethod($statsDaysNumber);
		}
		
		$getLatestUsersChartMethod = 'getLatestUsersFor' . ucfirst($chartProvider);
		if (method_exists($this, $getLatestUsersChartMethod)) {
			$this->data['latestUsersChart'] = $this->$getLatestUsersChartMethod($statsDaysNumber);
		}
		
		// ---------------------------------
		// Circle Charts
		// ---------------------------------
		
		// Get entries per country charts
		if (config('settings.app.show_countries_charts')) {
			$countriesLimit = (int)config('settings.app.countries_charts_limit', 5);
			$this->data['postsPerCountry'] = $this->getPostsPerCountryForChartjs($countriesLimit);
			$this->data['usersPerCountry'] = $this->getUsersPerCountryForChartjs($countriesLimit);
		}
		
		// ---------------------------------
		// Latest entries for Posts & Users
		// ---------------------------------
		
		// Limit latest entries
		$latestEntriesLimit = (int)config('settings.app.latest_entries_limit', 5);
		
		// Get latest Ads
		$this->data['latestPosts'] = Post::with([
			'payment',
			'payment.package',
			'user',
			'country',
		])->take($latestEntriesLimit)->orderByDesc('created_at')->get();
		
		// Get latest Users
		$this->data['latestUsers'] = User::with(['country'])
			->take($latestEntriesLimit)
			->orderByDesc('created_at')->get();
		
		// ---------------------------------
		
		// Page Title
		$this->data['title'] = trans('admin.dashboard');
		
		return view('admin.dashboard', $this->data);
	}
	
	/**
	 * Redirect to the dashboard.
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function redirect(): \Illuminate\Http\RedirectResponse
	{
		// The '/admin' route is not to be used as a page, because it breaks the menu's active state.
		return redirect()->to(admin_uri('dashboard'));
	}
}
