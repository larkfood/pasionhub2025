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

namespace App\Http\Requests\Admin\SettingRequest;

use App\Http\Requests\Admin\Request;

/*
 * Use request() instead of $this since this form request can be called from another
 */

class ListingsListRequest extends Request
{
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		// $request = request();
		
		return [
			'min_price'         => ['required', 'integer', 'min:0', 'lte:max_price'],
			'max_price'         => ['required', 'integer', 'max:1000000', 'gte:min_price'],
			'price_slider_step' => ['required', 'integer', 'min:1', 'max:10000'],
		];
	}
	
	/**
	 * @return array
	 */
	public function messages(): array
	{
		$messages = [];
		
		return array_merge(parent::messages(), $messages);
	}
	
	/**
	 * @return array
	 */
	public function attributes(): array
	{
		$attributes = [
			'min_price'         => trans('admin.min_price_label'),
			'max_price'         => trans('admin.max_price_label'),
			'price_slider_step' => trans('admin.price_slider_step_label'),
		];
		
		return array_merge(parent::attributes(), $attributes);
	}
}
