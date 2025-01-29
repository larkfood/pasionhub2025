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

use App\Http\Controllers\Web\Setup\Install\Traits\ApiTrait;
use App\Http\Requests\Setup\Install\SiteInfoRequest;
use App\Providers\AppService\ConfigTrait\MailConfig;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;

class SiteInfoController extends BaseController
{
	use ApiTrait, MailConfig;
	
	/**
	 * STEP 2.1 - Set Site Info
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
		
		// Remove the installed file (if it does exist)
		$installedFile = storage_path('installed');
		if (File::exists($installedFile)) {
			File::delete($installedFile);
		}
		
		// Unactivated all add-ons/plugins by removing their installed file
		$pluginsDir = storage_path('framework/plugins');
		$leaveFiles = ['.gitignore'];
		foreach (glob($pluginsDir . '/*') as $file) {
			if (!in_array(basename($file), $leaveFiles)) {
				@unlink($file);
			}
		}
		
		// Get country code by the user IP address
		// This method set its result in cookie (with the 'ipCountryCode' as key name)
		$defaultCountyCode = $this->getCountryCodeFromIPAddr();
		
		// Get mail drivers
		$mailDrivers = (array)config('larapen.options.mail');
		
		// Get the drivers selectors list as JS objects
		$mailDriversSelectorsJson = collect($mailDrivers)
			->keys()
			->mapWithKeys(fn ($item) => [$item => '.' . $item])
			->toJson();
		
		// Format the mail drivers list
		$mailDrivers = collect($mailDrivers)
			->mapWithKeys(fn ($item, $key) => [
				$key => [
					'value' => $key,
					'text'  => $item,
				],
			])->toArray();
		
		// Retrieve site info
		$siteInfo = request()->old();
		$siteInfo = !empty($siteInfo) ? $siteInfo : session('siteInfo');
		
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
		
		return view(
			'setup.install.site_info',
			compact('defaultCountyCode', 'siteInfo', 'mailDrivers', 'mailDriversSelectorsJson')
		);
	}
	
	/**
	 * STEP 2.2 - Set Site Info
	 *
	 * @param \App\Http\Requests\Setup\Install\SiteInfoRequest $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function postForm(SiteInfoRequest $request): RedirectResponse
	{
		// Return to the last unlocked step if the current step remains locked
		$currentStep = $this->getStepByKey(get_class($this));
		$lastUnlockedStepUrl = $this->getLastUnlockedStepUrlOnlyIfGivenStepIsLocked($currentStep);
		if (!empty($lastUnlockedStepUrl)) {
			return redirect()->to($lastUnlockedStepUrl);
		}
		
		// Clear old data from the session
		session()->forget('siteInfo');
		
		// Save the new data in session
		session()->put('siteInfo', $request->all());
		
		// Get the next step URL
		$nextUrl = $this->getNextStepUrl($currentStep);
		
		return redirect()->to($nextUrl);
	}
}
