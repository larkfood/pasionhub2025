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

use App\Http\Controllers\Web\Setup\Install\Traits\CheckerTrait;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;

class RequirementsController extends BaseController
{
	use CheckerTrait;
	
	/**
	 * STEP 1 - Check System Requirements
	 *
	 * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
	 */
	public function __invoke(): View|RedirectResponse
	{
		session()->forget('requirementsVerified');
		
		// Get the current step
		$currentStep = $this->getStepByKey(get_class($this));
		
		// Check Components & Permissions
		$checkComponents = $this->checkComponents();
		$checkPermissions = $this->checkPermissions();
		$areRequirementsMet = $checkComponents && $checkPermissions;
		
		// 1. Auto-Checking: Skip this step If the system is OK
		$areRequirementsMetWithAutoRedirect = $areRequirementsMet && !$this->isManualCheckingAllowed();
		if ($areRequirementsMetWithAutoRedirect) {
			session()->put('requirementsVerified', ($areRequirementsMet ? 1 : 0));
			
			// Get the DB info step URL
			$siteInfoStep = $this->getStepByKey(SiteInfoController::class);
			$siteInfoUrl = $this->getStepUrl($siteInfoStep);
			
			return redirect()->to($siteInfoUrl);
		}
		
		// 2. Check the compatibilities manually: Retry if something does not work yet
		try {
			if ($areRequirementsMet) {
				session()->put('requirementsVerified', 1);
			}
			
			$components = $this->getComponents();
			$permissions = $this->getPermissions();
			
			// Get steps URLs & labels
			$previousStepUrl = null;
			$previousStepLabel = null;
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
				'setup.install.requirements',
				compact('components', 'permissions', 'checkComponents', 'checkPermissions')
			);
		} catch (\Throwable $e) {
			// Installation starting failed
			// Clear cache and config again
			Artisan::call('cache:clear');
			Artisan::call('config:clear');
			
			// Get the requirements step URL
			$requirementsStep = $this->getStepByKey(get_class($this));
			$requirementsUrl = $this->getStepUrl($requirementsStep);
			$requirementsUrl = urlQuery($requirementsUrl)->setParameters(['mode' => 'manual'])->toString();
			
			return redirect()->to($requirementsUrl);
		}
	}
}
