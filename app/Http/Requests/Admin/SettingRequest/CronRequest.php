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

class CronRequest extends Request
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
			'unactivated_listings_expiration'       => ['required', 'integer', 'min:1'],
			'activated_listings_expiration'         => ['required', 'integer', 'min:1'],
			'archived_listings_expiration'          => ['required', 'integer', 'min:1'],
			'manually_archived_listings_expiration' => ['required', 'integer', 'min:1'],
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
			'unactivated_listings_expiration'       => trans('admin.unactivated_listings_expiration_label'),
			'activated_listings_expiration'         => trans('admin.activated_listings_expiration_label'),
			'archived_listings_expiration'          => trans('admin.archived_listings_expiration_label'),
			'manually_archived_listings_expiration' => trans('admin.manually_archived_listings_expiration_label'),
		];
		
		return array_merge(parent::attributes(), $attributes);
	}
}
