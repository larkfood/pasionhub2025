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

namespace App\Http\Controllers\Web\Setup\Install\Traits\Db;

use Database\Seeders\SiteInfoSeeder;
use Illuminate\Support\Facades\Artisan;

trait MigrationsTrait
{
	/**
	 * Import from Laravel Migrations
	 * php artisan migrate --path=/database/migrations --force
	 * php artisan migrate --path=/vendor/laravel/sanctum/database/migrations --force
	 *
	 * NOTE:
	 * From Laravel 11.x the Sanctun migration is available in: /database/migrations
	 *
	 * Rollback & Re-runs all the Migrations
	 * php artisan migrate:refresh --path=/database/migrations --force
	 *
	 * Drop All Tables & Migrate
	 * php artisan migrate:fresh --path=/database/migrations --force
	 */
	protected function runMigrations(): void
	{
		Artisan::call('migrate', [
			'--path'  => '/database/migrations',
			'--force' => true,
		]);
		
		// sleep(2);
	}
	
	/**
	 * Import from Laravel Seeders
	 * php artisan db:seed --force
	 */
	protected function runSeeders(): void
	{
		Artisan::call('db:seed', ['--force' => true]);
		
		// sleep(2);
	}
	
	/**
	 * Insert site info & related data
	 *
	 * @param array $siteInfo
	 * @return void
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	protected function runSiteInfoSeeder(array $siteInfo = []): void
	{
		$siteInfoSeeder = new SiteInfoSeeder();
		$siteInfoSeeder->run($siteInfo);
		
		// sleep(2);
	}
}
