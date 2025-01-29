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

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CronController extends BaseController
{
	/**
	 * STEP 5 - Set Cron Jobs
	 *
	 * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
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
		
		// The cron jobs config info is seen
		// Save the notification in session
		session()->put('cronJobsInfoSeen', 1);
		
		// Get steps URLs & labels
		$previousStepUrl = $this->getPrevStepUrl($currentStep);
		$previousStepLabel = trans('messages.back');
		$formActionUrl = url()->current();
		$nextStepUrl = $this->getNextStepUrl($currentStep);
		$nextStepLabel = trans('messages.next');
		
		// Share steps URLs & label variables
		view()->share('previousStepUrl', $previousStepUrl);
		view()->share('previousStepLabel', $previousStepLabel);
		view()->share('formActionUrl', $formActionUrl);
		view()->share('nextStepUrl', $nextStepUrl);
		view()->share('nextStepLabel', $nextStepLabel);
		
		return view('setup.install.cron_jobs');
	}
}
