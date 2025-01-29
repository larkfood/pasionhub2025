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

namespace App\Http\Controllers\Api\Payment\Promotion;

use App\Services\Payment as PaymentHelper;
use App\Http\Controllers\Api\Payment\RetrievePackageFeatures;
use App\Models\Package;

trait SingleStepPayment
{
	use RetrievePackageFeatures;
	
	public array $apiMsg = [];
	public array $apiUri = [];
	public ?Package $selectedPackage = null;
	public bool $noPackageOrPremiumOneSelected = true;
	public array $payment = [];
	
	/**
	 * Set payment settings for promotion packages (Single-Step Form)
	 *
	 * @return void
	 */
	protected function setPaymentSettingsForPromotion(): void
	{
		$isNewEntry = isPostCreationRequest();
		
		// Messages
		$this->apiMsg['payable']['success'] = $isNewEntry ? t('your_listing_is_created') : t('your_listing_is_updated');
		$this->apiMsg['checkout']['success'] = t('payment_received');
		$this->apiMsg['checkout']['cancel'] = t('payment_cancelled_text');
		$this->apiMsg['checkout']['error'] = t('payment_error_text');
		
		// Set URLs
		if ($isNewEntry) {
			
			$this->apiUri['previousUrl'] = isMultipleStepsFormEnabled() ? urlGen()->addPostPayment() : urlGen()->addPost();
			$this->apiUri['nextUrl'] = urlGen()->addPostFinished();
			$this->apiUri['paymentCancelUrl'] = urlGen()->addPostPaymentCancel();
			$this->apiUri['paymentReturnUrl'] = urlGen()->addPostPaymentSuccess();
			
		} else {
			
			$this->apiUri['previousUrl'] = isMultipleStepsFormEnabled() ? urlGen()->editPostPayment('#entryId') : urlGen()->editPost('#entryId');
			$this->apiUri['nextUrl'] = urlGen()->postPattern(id: '#entryId', slug: '#entrySlug');
			$this->apiUri['paymentCancelUrl'] = urlGen()->editPostPaymentCancel('#entryId');
			$this->apiUri['paymentReturnUrl'] = urlGen()->editPostPaymentSuccess('#entryId');
			
		}
		
		// Payment Helper init.
		PaymentHelper::$country = collect(config('country'));
		PaymentHelper::$lang = collect(config('lang'));
		PaymentHelper::$msg = $this->apiMsg;
		PaymentHelper::$uri = $this->apiUri;
		
		if ($isNewEntry) {
			/*
			 * Get the post's current active payment info (If exists)
			 * ---
			 * Share the Post's current payment info variables without passing a Listing in argument
			 * That is to get required variables for views (Web) or windows (Mobile)
			 */
			$this->payment = $this->getCurrentActivePaymentInfo();
		}
		
		// Selected Package (from Form)
		$this->selectedPackage = $this->getSelectedPackage();
		$this->noPackageOrPremiumOneSelected = doesNoPackageOrPremiumOneSelected($this->selectedPackage);
		
		if (!isFromApi()) {
			view()->share('selectedPackage', $this->selectedPackage);
			view()->share('noPackageOrPremiumOneSelected', $this->noPackageOrPremiumOneSelected);
		}
	}
}
