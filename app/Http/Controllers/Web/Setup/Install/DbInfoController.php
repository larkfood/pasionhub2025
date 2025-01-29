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

use App\Http\Controllers\Web\Setup\Install\Traits\EnvTrait;
use App\Http\Requests\Setup\Install\DatabaseInfoRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class DbInfoController extends BaseController
{
	use EnvTrait;
	
	/**
	 * STEP 3.1 - Database Configuration
	 *
	 * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
	 */
	public function getForm(): View|RedirectResponse
	{
		// Return to the last unlocked step if the current step remains locked
		$currentStep = $this->getStepByKey(get_class($this));
		$lastUnlockedStepUrl = $this->getLastUnlockedStepUrlOnlyIfGivenStepIsLocked($currentStep);
		if (!empty($lastUnlockedStepUrl)) {
			return redirect()->to($lastUnlockedStepUrl);
		}
		
		$databaseInfo = request()->old();
		$databaseInfo = !empty($databaseInfo) ? $databaseInfo : session('databaseInfo');
		
		// Get steps URLs & labels
		$previousStepUrl = $this->getPrevStepUrl($currentStep);
		$previousStepLabel = trans('messages.back');
		$formActionUrl = url()->current();
		$nextStepUrl = $this->getNextStepUrl($currentStep);
		$nextStepLabel = trans('messages.database_connect_btn_label');
		
		// Share steps URLs & label variables
		view()->share('previousStepUrl', $previousStepUrl);
		view()->share('previousStepLabel', $previousStepLabel);
		view()->share('formActionUrl', $formActionUrl);
		view()->share('nextStepUrl', $nextStepUrl);
		view()->share('nextStepLabel', $nextStepLabel);
		
		return view('setup.install.database_info', compact('databaseInfo'));
	}
	
	/**
	 * STEP 3.2 - Submit Database Configuration
	 *
	 * @param \App\Http\Requests\Setup\Install\DatabaseInfoRequest $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function postForm(DatabaseInfoRequest $request): RedirectResponse
	{
		// Return to the last unlocked step if the current step remains locked
		$currentStep = $this->getStepByKey(get_class($this));
		$lastUnlockedStepUrl = $this->getLastUnlockedStepUrlOnlyIfGivenStepIsLocked($currentStep);
		if (!empty($lastUnlockedStepUrl)) {
			return redirect()->to($lastUnlockedStepUrl);
		}
		
		// Clear old data from the session
		session()->forget('databaseInfo');
		
		// Get database info & site info
		$siteInfo = (array)session('siteInfo');
		$databaseInfo = $request->all();
		
		// Save the new data in session
		session()->put('databaseInfo', $databaseInfo);
		/*
		 * Ensure this session is saved before continuing
		 * i.e. Don't wait until the end of the request to let it be saved
		 */
		session()->save();
		
		// Write config file
		$this->writeEnv($siteInfo, $databaseInfo);
		
		// Notification message
		$message = trans('messages.database_connection_success');
		flash($message)->success();
		
		// Get the next step URL
		$nextUrl = $this->getNextStepUrl($currentStep);
		
		// Return to import database page
		return redirect()->to($nextUrl)->withHeaders(config('larapen.core.noCacheHeaders'));
	}
}
