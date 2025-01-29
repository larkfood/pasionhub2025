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

namespace App\Providers;

use App\Models\Sanctum\PersonalAccessToken;
use App\Providers\AppService\AclSystemTrait;
use App\Providers\AppService\ConfigTrait;
use App\Providers\AppService\SchemaStringLengthTrait;
use App\Providers\AppService\SymlinkTrait;
use App\Providers\AppService\TelescopeTrait;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
	use SchemaStringLengthTrait, TelescopeTrait, AclSystemTrait, ConfigTrait, SymlinkTrait;
	
	private int $cacheExpiration = 86400; // Cache for 1 day (60 * 60 * 24)
	
	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
		//
	}
	
	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->runInspection();
		
		// Set Bootstrap as default client assets
		Paginator::useBootstrap();
		
		// Set the default schema string length
		$this->setDefaultSchemaStringLength();
		
		// Setup Laravel Sanctum
		try {
			Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
		} catch (\Throwable $e) {
		}
		
		// Setup Storage Symlink
		$this->setupStorageSymlink();
		
		// Setup ACL system
		$this->setupAclSystem();
		
		// Setup Https
		$this->setupHttps();
		
		// Setup Configs
		$this->setupConfigs();
		
		// Rate Limiters
		$this->setupRateLimiting();
		
		// Send Mails Always To
		$this->setupMailsAlwaysTo();
		
		// Add theme views with higher priority
		$themePath = base_path('extras/themes/customized/views');
		if (is_dir($themePath)) {
			View::prependLocation($themePath);
		}
	}
	
	/**
	 * Setup Https
	 */
	private function setupHttps()
	{
		// Force HTTPS protocol
		if (config('larapen.core.forceHttps')) {
			URL::forceScheme('https');
		}
	}
	
	/**
	 * Configure the rate limiters for the application.
	 */
	private function setupRateLimiting(): void
	{
		// More Info: https://laravel.com/docs/10.x/routing#rate-limiting
		
		// API rate limit
		RateLimiter::for('api', function (Request $request) {
			// Exception for local and demo environments
			if (isLocalEnv() || isDemoEnv()) {
				return isLocalEnv()
					? Limit::none()
					: (
					$request->user()
						? Limit::perMinute(90)->by($request->user()->id)
						: Limit::perMinute(60)->by($request->ip())
					);
			}
			
			// Limits access to the routes associated with it to:
			// - (For logged users): 1200 requests per minute by user ID
			// - (For guests): 600 requests per minute by IP address
			return $request->user()
				? Limit::perMinute(1200)->by($request->user()->id)
				: Limit::perMinute(600)->by($request->ip());
		});
		
		// Global rate limit (Not used)
		RateLimiter::for('global', function (Request $request) {
			// Limits access to the routes associated with it to:
			// - 1000 requests per minute
			return Limit::perMinute(1000);
		});
	}
}
