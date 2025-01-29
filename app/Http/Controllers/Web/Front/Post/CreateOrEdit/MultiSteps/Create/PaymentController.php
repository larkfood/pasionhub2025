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

namespace App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Create;

use App\Http\Requests\Front\PackageRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PaymentController extends BaseController
{
	/**
	 * Listing payment's step
	 *
	 * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
	 */
	public function getForm(): View|RedirectResponse
	{
		// Return to the last unlocked step if the current step remains locked
		$currentStep = $this->getStepByKey(get_class($this));
		$lastUnlockedStepUrl = $this->getLastUnlockedStepUrlOnlyIfGivenStepIsLocked($currentStep);
		if (!empty($lastUnlockedStepUrl)) {
			return redirect()->to($lastUnlockedStepUrl)->withHeaders(config('larapen.core.noCacheHeaders'));
		}
		
		// Get the previous step URL
		$previousStepUrl = $this->getPrevStepUrl($currentStep);
		
		// Get selected package
		$selectedPackage = $this->getSelectedPackage();
		
		// Check if the 'Pricing Page' must be started first, and make redirection to it.
		$pricingUrl = $this->getPricingPage($selectedPackage);
		if (!empty($pricingUrl)) {
			return redirect()->to($pricingUrl)->withHeaders(config('larapen.core.noCacheHeaders'));
		}
		
		$noPackageOrPremiumOneSelected = doesNoPackageOrPremiumOneSelected($selectedPackage);
		if (!$noPackageOrPremiumOneSelected) {
			return redirect()->to($previousStepUrl);
		}
		
		$payment = session('paymentInput');
		
		// Get steps URLs & labels
		// The $previousStepUrl is defined above
		$previousStepLabel = t('Previous');
		$formActionUrl = url()->current();
		$nextStepUrl = null;
		$nextStepLabel = t('Pay');
		
		// Share steps URLs & label variables
		view()->share('previousStepUrl', $previousStepUrl);
		view()->share('previousStepLabel', $previousStepLabel);
		view()->share('formActionUrl', $formActionUrl);
		view()->share('nextStepUrl', $nextStepUrl);
		view()->share('nextStepLabel', $nextStepLabel);
		
		return view('front.post.createOrEdit.multiSteps.create.packages', compact('payment'));
	}
	
	/**
	 * Listing payment's step (POST)
	 *
	 * @param \App\Http\Requests\Front\PackageRequest $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function postForm(PackageRequest $request): RedirectResponse
	{
		$isPictureMandatoryEnabled = (config('settings.listing_form.picture_mandatory') == '1');
		if ($isPictureMandatoryEnabled) {
			// Return to the last unlocked step if the current step remains locked
			$currentStep = $this->getStepByKey(get_class($this));
			$lastUnlockedStepUrl = $this->getLastUnlockedStepUrlOnlyIfGivenStepIsLocked($currentStep);
			if (!empty($lastUnlockedStepUrl)) {
				return redirect()->to($lastUnlockedStepUrl)->withHeaders(config('larapen.core.noCacheHeaders'));
			}
		}
		
		session()->put('paymentInput', $request->validated());
		
		return $this->storeInputDataInDatabase($request);
	}
}
