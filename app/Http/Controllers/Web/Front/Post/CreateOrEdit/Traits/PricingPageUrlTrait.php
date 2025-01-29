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

namespace App\Http\Controllers\Web\Front\Post\CreateOrEdit\Traits;

trait PricingPageUrlTrait
{
	/**
	 * Check if the Package selection is required and Get the Pricing Page URL
	 *
	 * @param $package
	 * @return string|null
	 */
	public function getPricingPage($package): ?string
	{
		$pricingUrl = null;
		
		// Check if the 'Pricing Page' must be started first, and make redirection to it.
		if (config('settings.listing_form.pricing_page_enabled') == '1') {
			if (empty($package)) {
				$fromParam = ['from' => request()->path()];
				
				$authUser = auth()->check() ? auth()->user() : null;
				if (!empty($authUser)) {
					/*
					 * If the user doesn't have any valid subscription,
					 * Force the user to select a package (on the pricing page) to allow him to create new listing
					 *
					 * IMPORTANT:
					 * To avoid excessive memory consumption that could degrade the application performance,
					 * checking the limitation of the number of listings linked to the users' subscription
					 * will be done downstream (when trying to publish new listings).
					 */
					$authUser->loadMissing('payment');
					if (empty($authUser->payment)) {
						$pricingUrl = urlGen()->pricing();
						$pricingUrl = urlQuery($pricingUrl)->setParameters($fromParam)->toString();
					}
				} else {
					// Force the guest to select a package (on the pricing page) to allow him to create new listing
					$pricingUrl = urlGen()->pricing();
					$pricingUrl = urlQuery($pricingUrl)->setParameters($fromParam)->toString();
				}
			}
		}
		
		return $pricingUrl;
	}
}
