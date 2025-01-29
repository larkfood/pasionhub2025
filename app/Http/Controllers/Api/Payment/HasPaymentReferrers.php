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

namespace App\Http\Controllers\Api\Payment;

use App\Http\Requests\Front\PackageRequest;
use App\Http\Requests\Front\PostRequest;
use App\Models\Package;
use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Collection;

trait HasPaymentReferrers
{
	public Collection $paymentMethods;
	public int $countPaymentMethods = 0;
	
	public Collection $packages;
	public int $countPackages = 0;
	
	/**
	 * Set the payment global settings
	 * i.e.: Package list, Payment method list, etc.
	 *
	 * @return void
	 */
	protected function getPaymentReferrersData(): void
	{
		$plugins = array_keys((array)config('plugins.installed'));
		$countryCode = config('country.code');
		
		// Get Payment Methods
		$cachePluginsId = !empty($plugins) ? '.plugins.' . implode(',', $plugins) : '';
		$cacheId = $countryCode . '.paymentMethods.all' . $cachePluginsId;
		$this->paymentMethods = cache()->remember($cacheId, $this->cacheExpiration, function () use ($plugins, $countryCode) {
			$paymentMethods = PaymentMethod::query()->whereIn('name', $plugins);
			
			if (!empty($countryCode)) {
				$countryCode = strtolower($countryCode);
				$findInSet = 'FIND_IN_SET("' . strtolower($countryCode) . '", LOWER(countries)) > 0';
				
				$paymentMethods = $paymentMethods->where(function ($query) use ($findInSet) {
					$query->whereRaw($findInSet)->orWhere(fn ($query) => $query->columnIsEmpty('countries'));
				});
			}
			
			$paymentMethods = $paymentMethods->orderBy('lft');
			
			return $paymentMethods->get();
		});
		$this->countPaymentMethods = $this->paymentMethods->count();
		
		// Get the package type relating to the current request
		$packageType = getRequestPackageType();
		$isPromoting = ($packageType === 'promotion');
		$isSubscripting = ($packageType === 'subscription');
		
		if (!$isPromoting && !$isSubscripting) {
			abort(403, 'Error: Unable to retrieve the package type.');
		}
		
		// Get Packages
		$this->packages = Package::with('currency')
			->when($isPromoting, fn ($query) => $query->promotion())
			->when($isSubscripting, fn ($query) => $query->subscription())
			->applyCurrency()
			->orderBy('lft')
			->get();
		$this->countPackages = $this->packages->count();
		
		// Sharing info Requests for Web & API calls
		// promotion
		if ($isPromoting) {
			if (isSingleStepFormEnabled()) {
				// Single-Step Form
				PostRequest::$packages = $this->packages;
				PostRequest::$paymentMethods = $this->paymentMethods;
			} else {
				// Multi-Steps Form
				PackageRequest::$packages = $this->packages;
				PackageRequest::$paymentMethods = $this->paymentMethods;
			}
		}
		// subscription
		if ($isSubscripting) {
			PackageRequest::$packages = $this->packages;
			PackageRequest::$paymentMethods = $this->paymentMethods;
		}
		
		// Sharing into Views for Web devices only
		if (!isFromApi()) {
			view()->share('paymentMethods', $this->paymentMethods);
			view()->share('countPaymentMethods', $this->countPaymentMethods);
			
			view()->share('packages', $this->packages);
			view()->share('countPackages', $this->countPackages);
		}
	}
}
