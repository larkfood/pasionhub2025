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

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;

class StartingController extends BaseController
{
	/**
	 * STEP 0 - Starting installation
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function __invoke(): RedirectResponse
	{
		// Clear Cache and Config
		// Note: This step is required only once, at the beginning of the installation process.
		Artisan::call('cache:clear');
		Artisan::call('config:clear');
		
		// Get the next step URL
		$nextStep = $this->getStepByKey(get_class($this));
		$nextUrl = $this->getNextStepUrl($nextStep);
		
		return redirect()->to($nextUrl);
	}
}
