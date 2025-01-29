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

namespace App\Http\Controllers\Web\Setup\Install;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Setup\Install\Traits\DbTrait;
use App\Http\Controllers\Web\Setup\Install\Traits\WizardTrait;
use Illuminate\Support\Facades\File;

class BaseController extends Controller
{
	use WizardTrait, DbTrait;
	
	protected string $baseUrl;
	protected array $rawNavItems = [];
	protected array $navItems = [];
	protected int $stepsSegment = 2;
	protected array $allowedQueries = ['mode'];
	
	public function __construct()
	{
		$this->commonQueries();
		
		// Create SQL destination path if not exists
		$countriesDataDir = storage_path('app/database/geonames/countries');
		if (!File::exists($countriesDataDir)) {
			File::makeDirectory($countriesDataDir, 0755, true);
		}
		
		// Base URL
		$this->baseUrl = getRawBaseUrl();
		config()->set('app.url', $this->baseUrl);
		
		// Get the installation navigation links
		$this->navItems = $this->getNavItems();
		view()->share('navItems', $this->navItems);
	}
	
	/**
	 * Common Queries
	 *
	 * @return void
	 */
	protected function commonQueries(): void
	{
		// Delete all front&back office sessions
		session()->forget('countryCode');
		session()->forget('timeZone');
		session()->forget('langCode');
	}
}
