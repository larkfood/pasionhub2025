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

namespace App\Http\Controllers\Web\Admin\Traits\InlineRequest;

use App\Services\Payment as PaymentHelper;
use App\Models\Payment;

trait PaymentTrait
{
	/**
	 * Update the 'active' column of the payment table
	 *
	 * @param $payment
	 * @param $column
	 * @return \Illuminate\Http\JsonResponse
	 */
	protected function updatePaymentData($payment, $column): \Illuminate\Http\JsonResponse
	{
		$isValidCondition = ($this->table == 'payments' && $column == 'active' && !empty($payment));
		if (!$isValidCondition) {
			$error = trans('admin.inline_req_condition', ['table' => $this->table, 'column' => $column]);
			
			return $this->responseError($error, 400);
		}
		
		$payableModel = '\\' . $payment->payable_type;
		$isPromoting = (str_ends_with($payment->payable_type, 'Post'));
		$isSubscripting = (str_ends_with($payment->payable_type, 'User'));
		
		if (!$isPromoting && !$isSubscripting) {
			return $this->responseError(t('payable_type_not_found'), 400);
		}
		
		$payable = $payableModel::find($payment->payable_id);
		if (empty($payable)) {
			$error = $isPromoting ? t('post_not_found') : t('user_not_found');
			
			return $this->responseError($error);
		}
		
		// Save data
		if ($payment->{$column} != 1) {
			if ($payment->id == $payable->paymentEndingLater?->id) {
				$periodStart = now();
				$periodEnd = ($payment->interval > 0) ? now()->addDays((int)$payment->interval) : now();
			} else {
				$daysLeft = PaymentHelper::getDaysLeftBeforePayablePaymentsExpire($payable, $payment->period_start);
				$periodStart = PaymentHelper::periodDate($payment->period_start, $daysLeft);
				$periodEnd = PaymentHelper::periodDate($payment->period_end, $daysLeft);
			}
			
			$payment->period_start = $periodStart->startOfDay();
			$payment->period_end = $periodEnd->endOfDay();
			$payment->{$column} = 1;
		} else {
			$payment->{$column} = 0;
		}
		$payment->save();
		
		/*
		 * Used by the OfflinePayment plugin
		 * Update the 'featured' fields of the related payable (Post|User)
		 * And update the 'reviewed' fields of the related payable (Post)
		 */
		if ($payment->{$column} == 1) {
			if ($isPromoting) {
				$payable->reviewed_at = now();
			}
			$payable->featured = 1;
			$payable->save();
		} else {
			$payableActivePayments = Payment::query()
				->where('payable_type', $payment->payable_type)
				->where('payable_id', $payment->payable_id)
				->where('id', '!=', $payment->id)
				->valid()
				->active();
			
			if ($payableActivePayments->count() <= 0) {
				if ($isPromoting) {
					$payable->reviewed_at = null;
				}
				$payable->featured = 0;
				$payable->save();
			}
		}
		
		return $this->responseSuccess($payment, $column);
	}
}
