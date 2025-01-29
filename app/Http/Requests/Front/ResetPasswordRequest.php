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

use App\Http\Requests\Traits\HasEmailInput;
use App\Http\Requests\Traits\HasPasswordInput;
use App\Http\Requests\Traits\HasPhoneInput;

class ResetPasswordRequest extends AuthRequest
{
	use HasEmailInput, HasPhoneInput, HasPasswordInput;
	
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		$rules = parent::rules();
		
		// token
		$rules['token'] = ['required'];
		
		// email
		$rules = $this->emailRules($rules);
		
		// phone
		$rules = $this->phoneRules($rules);
		$rules['phone_country'] = ['required_with:phone'];
		
		// password
		$rules = $this->passwordRules($rules);
		$rules['password'] = ['required', 'confirmed'];
		
		return $rules;
	}
}
