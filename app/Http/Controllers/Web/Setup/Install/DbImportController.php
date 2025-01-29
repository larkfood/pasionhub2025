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

// Increase the server resources
$iniConfigFile = __DIR__ . '/../../../../../Helpers/Functions/ini.php';
if (file_exists($iniConfigFile)) {
	include_once $iniConfigFile;
}

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DbImportController extends BaseController
{
	/**
	 * STEP 4.1 - Import Database
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
		
		// Get the previous URL
		$previousStepUrl = $this->getPrevStepUrl($currentStep);
		
		// Get the database info
		$databaseInfo = (array)session('databaseInfo');
		
		// Check if the database connection is ok
		try {
			$this->getPdoConnectionWithEnvCheck($databaseInfo);
		} catch (\Throwable $e) {
			flash($e->getMessage())->error();
			
			return redirect()->to($previousStepUrl);
		}
		
		// Get steps URLs & labels
		// The $previousStepUrl is defined above
		$previousStepLabel = trans('messages.back');
		$formActionUrl = url()->current();
		$nextStepUrl = $this->getNextStepUrl($currentStep);
		$nextStepLabel = trans('messages.database_import_btn_label');
		
		// Share steps URLs & label variables
		view()->share('previousStepUrl', $previousStepUrl);
		view()->share('previousStepLabel', $previousStepLabel);
		view()->share('formActionUrl', $formActionUrl);
		view()->share('nextStepUrl', $nextStepUrl);
		view()->share('nextStepLabel', $nextStepLabel);
		
		return view('setup.install.database_import', compact('databaseInfo'));
	}
	
	/**
	 * STEP 4.2 - Submit Database Import
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function postForm(Request $request): RedirectResponse
	{
		// Return to the last unlocked step if the current step remains locked
		$currentStep = $this->getStepByKey(get_class($this));
		$lastUnlockedStepUrl = $this->getLastUnlockedStepUrlOnlyIfGivenStepIsLocked($currentStep);
		if (!empty($lastUnlockedStepUrl)) {
			return redirect()->to($lastUnlockedStepUrl);
		}
		
		// Get database info & site info
		$siteInfo = (array)session('siteInfo');
		$databaseInfo = (array)session('databaseInfo');
		
		// Update the database info
		$databaseInfo['overwrite_tables'] = $request->input('overwrite_tables', '0');
		session()->put('databaseInfo', $databaseInfo);
		/*
		 * Ensure this session is saved before continuing
		 * i.e. Don't wait until the end of the request to let it be saved
		 */
		session()->save();
		
		// Clear old notification from the session
		session()->forget('databaseImported');
		
		try {
			
			// Import the required data
			$this->submitDatabaseImport($siteInfo, $databaseInfo);
			
		} catch (\Throwable $e) {
			flash($e->getMessage())->error();
			
			// Get the DB import step URL
			$dbImportStep = $this->getStepByKey(get_class($this));
			$dbImportUrl = $this->getStepUrl($dbImportStep);
			
			return redirect()->to($dbImportUrl)->withInput($databaseInfo);
		}
		
		// The database is now imported!
		// Save the new notification in session
		session()->put('databaseImported', 1);
		
		// Notification message
		$message = trans('messages.database_tables_configuration_success');
		flash($message)->success();
		
		// Get the next step URL
		$nextUrl = $this->getNextStepUrl($currentStep);
		
		return redirect()->to($nextUrl);
	}
}
