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

use App\Http\Controllers\Web\Setup\Install\CronController;
use App\Http\Controllers\Web\Setup\Install\DbImportController;
use App\Http\Controllers\Web\Setup\Install\DbInfoController;
use App\Http\Controllers\Web\Setup\Install\FinishController;
use App\Http\Controllers\Web\Setup\Install\SiteInfoController;
use App\Http\Controllers\Web\Setup\Install\StartingController;
use App\Http\Controllers\Web\Setup\Install\RequirementsController;
use App\Http\Controllers\Web\Setup\Update\UpdateController;
use Illuminate\Support\Facades\Route;

Route::middleware(['no.http.cache'])
	->group(function () {
		// upgrade
		Route::prefix('upgrade')
			->controller(UpdateController::class)
			->group(function () {
				Route::get('/', 'index');
				Route::post('run', 'run');
			});
		
		// install
		Route::middleware(['install'])
			->prefix('install')
			->group(function () {
				Route::get('/', StartingController::class);
				Route::get('system_requirements', RequirementsController::class);
				Route::controller(SiteInfoController::class)
					->group(function () {
						Route::get('site_info', 'getForm');
						Route::post('site_info', 'postForm');
					});
				Route::controller(DbInfoController::class)
					->group(function () {
						Route::get('database_info', 'getForm');
						Route::post('database_info', 'postForm');
					});
				Route::controller(DbImportController::class)
					->group(function () {
						Route::get('database_import', 'getForm');
						Route::post('database_import', 'postForm');
					});
				Route::get('cron_jobs', CronController::class);
				Route::get('finish', FinishController::class);
			});
	});
