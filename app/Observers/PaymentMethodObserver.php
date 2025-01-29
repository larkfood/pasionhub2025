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

namespace App\Observers;

use App\Models\Country;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\LocalizedScope;
use App\Models\Scopes\StrictActiveScope;

class PaymentMethodObserver
{
	/**
	 * Listen to the Entry deleting event.
	 *
	 * @param PaymentMethod $paymentMethod
	 * @return void
	 */
	public function deleting(PaymentMethod $paymentMethod)
	{
		/*
		// Delete the payments of this PaymentMethod
		$payments = Payment::query()
			->withoutGlobalScope(StrictActiveScope::class)
			->where('payment_method_id', $paymentMethod->id);
		
		if ($payments->count() > 0) {
			foreach ($payments->cursor() as $payment) {
				// NOTE: Take account the payment plugins install/uninstall
				$payment->delete();
			}
		}
		*/
	}
	
	/**
	 * Listen to the Entry saved event.
	 *
	 * @param PaymentMethod $paymentMethod
	 * @return void
	 */
	public function saved(PaymentMethod $paymentMethod)
	{
		// Removing Entries from the Cache
		$this->clearCache($paymentMethod);
	}
	
	/**
	 * Listen to the Entry deleted event.
	 *
	 * @param PaymentMethod $paymentMethod
	 * @return void
	 */
	public function deleted(PaymentMethod $paymentMethod)
	{
		// Removing Entries from the Cache
		$this->clearCache($paymentMethod);
	}
	
	/**
	 * Removing the Entity's Entries from the Cache
	 *
	 * @param $paymentMethod
	 * @return void
	 */
	private function clearCache($paymentMethod): void
	{
		try {
			// Collection
			$plugins = array_keys((array)config('plugins.installed'));
			$cachePluginsId = !empty($plugins) ? '.plugins.' . implode(',', $plugins) : '';
			
			// Need to be caught (Independently)
			$countries = Country::query()
				->withoutGlobalScopes([ActiveScope::class, LocalizedScope::class])
				->get(['code']);
			
			if ($countries->count() > 0) {
				foreach ($countries as $country) {
					$cacheId = $country->code . '.paymentMethods.all';
					if (cache()->has($cacheId)) {
						cache()->forget($cacheId);
					}
					
					$cacheId = $country->code . '.paymentMethods.all' . $cachePluginsId;
					if (cache()->has($cacheId)) {
						cache()->forget($cacheId);
					}
				}
			}
			
			// Object
			if (!empty($paymentMethod->id)) {
				$cacheId = 'paymentMethod.id.' . $paymentMethod->id;
				if (cache()->has($cacheId)) {
					cache()->forget($cacheId);
				}
			}
			
			if (!empty($paymentMethod->name)) {
				$cacheId = 'paymentMethod.name.' . $paymentMethod->name;
				if (cache()->has($cacheId)) {
					cache()->forget($cacheId);
				}
			}
		} catch (\Throwable $e) {
		}
	}
}
