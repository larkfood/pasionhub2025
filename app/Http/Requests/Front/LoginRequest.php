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

namespace App\Http\Requests\Front;

class LoginRequest extends AuthRequest
{
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		if (!isFromApi()) {
			// If previous page is not the Login page...
			if (!str_contains(url()->previous(), config('routes.login', 'login'))) {
				// Save the previous URL to retrieve it after success or failed login.
				session()->put('url.intended', url()->previous());
			}
		}
		
		$rules = parent::rules();
		
		$rules['password'] = ['required'];
		
		return $rules;
	}
}
