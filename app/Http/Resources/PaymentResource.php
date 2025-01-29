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

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return array
	 */
	public function toArray(Request $request): array
	{
		if (!isset($this->id)) return [];
		
		$entity = [
			'id' => $this->id,
		];
		
		$columns = $this->getFillable();
		foreach ($columns as $column) {
			$entity[$column] = $this->{$column} ?? null;
		}
		
		$entity['interval'] = $this->interval ?? 0;
		$entity['started'] = $this->started ?? 0;
		$entity['expired'] = $this->expired ?? 0;
		$entity['status'] = $this->status ?? null;
		$entity['period_start_formatted'] = $this->period_start_formatted ?? null;
		$entity['period_end_formatted'] = $this->period_end_formatted ?? null;
		if (isset($this->canceled_at_formatted)) {
			$entity['canceled_at_formatted'] = $this->canceled_at_formatted;
		}
		if (isset($this->refunded_at_formatted)) {
			$entity['refunded_at_formatted'] = $this->refunded_at_formatted;
		}
		$entity['created_at_formatted'] = $this->created_at_formatted ?? null;
		$entity['status_info'] = $this->status_info ?? null;
		$entity['starting_info'] = $this->starting_info ?? null;
		$entity['expiry_info'] = $this->expiry_info ?? null;
		$entity['css_class_variant'] = $this->css_class_variant ?? null;
		if (isset($this->remaining_posts)) {
			$entity['remaining_posts'] = $this->remaining_posts;
		}
		
		$embed = explode(',', request()->input('embed'));
		
		$payableType = $this->payable_type ?? '';
		$isPromoting = (str_ends_with($payableType, 'Post'));
		$isSubscripting = (str_ends_with($payableType, 'User'));
		
		if (in_array('payable', $embed)) {
			if ($isPromoting) {
				$entity['payable'] = new PostResource($this->whenLoaded('payable'));
			}
			if ($isSubscripting) {
				$entity['payable'] = new UserResource($this->whenLoaded('payable'));
			}
		}
		if (in_array('package', $embed)) {
			$entity['package'] = new PackageResource($this->whenLoaded('package'));
		}
		if (in_array('paymentMethod', $embed)) {
			$entity['paymentMethod'] = new PaymentMethodResource($this->whenLoaded('paymentMethod'));
		}
		
		return $entity;
	}
}
