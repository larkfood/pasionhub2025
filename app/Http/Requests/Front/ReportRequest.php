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
use App\Rules\BetweenRule;
use App\Rules\EmailRule;

class ReportRequest extends Request
{
	use HasCaptchaInput;
	
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		$rules = [
			'report_type_id' => ['required', 'not_in:0'],
			'email'          => ['required', 'email', new EmailRule(), 'max:100'],
			'message'        => ['required', new BetweenRule(20, 1000)],
		];
		
		return $this->captchaRules($rules);
	}
}
