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

use App\Http\Requests\Request;
use App\Http\Requests\Traits\HasCaptchaInput;
use App\Http\Requests\Traits\HasEmailInput;
use App\Rules\BetweenRule;

class ContactRequest extends Request
{
	use HasEmailInput, HasCaptchaInput;
	
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		$rules = [
			'first_name' => ['required', 'string', new BetweenRule(2, 100)],
			'last_name'  => ['required', 'string', new BetweenRule(2, 100)],
			'email'      => ['required'],
			'message'    => ['required', 'string', new BetweenRule(5, 500)],
		];
		
		$rules = $this->emailRules($rules);
		
		if (isFromApi()) {
			$rules['country_code'] = ['required'];
			$rules['country_name'] = ['required'];
		}
		
		return $this->captchaRules($rules);
	}
}
