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

use App\Helpers\Cookie;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class FinishController extends BaseController
{
	/**
	 * STEP 6 - Finish
	 *
	 * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	public function __invoke(): View|RedirectResponse
	{
		// Return to the last unlocked step if the current step remains locked
		$currentStep = $this->getStepByKey(get_class($this));
		$lastUnlockedStepUrl = $this->getLastUnlockedStepUrlOnlyIfGivenStepIsLocked($currentStep);
		if (!empty($lastUnlockedStepUrl)) {
			return redirect()->to($lastUnlockedStepUrl);
		}
		
		// Check if the database connection is ok
		try {
			$this->getPdoConnectionWithEnvCheck();
		} catch (\Throwable $e) {
			flash($e->getMessage())->error();
			
			// Get the DB info step URL
			$dbInfoStep = $this->getStepByKey(DbInfoController::class);
			$dbInfoUrl = $this->getStepUrl($dbInfoStep);
			
			return redirect()->to($dbInfoUrl);
		}
		
		// Create the "installed" file
		createTheInstalledFile(true);
		
		// Declare the installation as complete
		session()->put('installationCompleted', 1);
		/*
		 * Ensure this session is saved before continuing
		 * i.e. Don't wait until the end of the request to let it be saved
		 */
		session()->save();
		
		// Delete all front & back office cookies
		Cookie::forget('ipCountryCode');
		
		// Clear all the cache
		Artisan::call('cache:clear');
		sleep(2);
		Artisan::call('view:clear');
		sleep(1);
		File::delete(File::glob(storage_path('logs') . DIRECTORY_SEPARATOR . '*.log'));
		
		// Rendering final Info
		return view('setup.install.finish');
	}
}
