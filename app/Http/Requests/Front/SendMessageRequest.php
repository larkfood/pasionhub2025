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
use App\Http\Requests\Traits\HasPhoneInput;
use App\Rules\BetweenRule;
use Illuminate\Validation\Rule;

class SendMessageRequest extends Request
{
	use HasEmailInput, HasPhoneInput, HasCaptchaInput;
	
	/**
	 * Prepare the data for validation.
	 *
	 * @return void
	 */
	protected function prepareForValidation(): void
	{
		$input = $this->all();
		
		// auth_field
		$input['auth_field'] = getAuthField();
		
		// phone
		$input = $this->preparePhoneForValidation($this, $input);
		
		// body
		if ($this->filled('body')) {
			$body = $this->input('body');
			
			$body = strip_tags($body);
			$body = html_entity_decode($body);
			$body = strip_tags($body);
			
			$input['body'] = $body;
		}
		
		request()->merge($input); // Required!
		$this->merge($input);
	}
	
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		$authFields = array_keys(getAuthFields());
		
		$rules = [
			'name'          => ['required', new BetweenRule(2, 200)],
			'auth_field'    => ['required', Rule::in($authFields)],
			'email'         => ['max:100'],
			'phone'         => ['max:30'],
			'phone_country' => ['required_with:phone'],
			'body'          => ['required', new BetweenRule(20, 500)],
			'post_id'       => ['required', 'numeric'],
		];
		
		// Check 'resume' is required
		if ($this->filled('catType') && $this->input('catType') == 'job-offer') {
			$allowedFileFormats = collect(getAllowedFileFormats())->join(',');
			$rules['file_path'] = [
				'required',
				'mimes:' . $allowedFileFormats,
				'min:' . (int)config('settings.upload.min_file_size', 0),
				'max:' . (int)config('settings.upload.max_file_size', 1000),
			];
		}
		
		// email
		$emailIsRequired = ($this->input('auth_field') == 'email');
		if ($emailIsRequired) {
			$rules['email'][] = 'required';
		}
		$rules = $this->emailRules($rules);
		
		// phone
		$phoneNumberIsRequired = ($this->input('auth_field') == 'phone');
		if ($phoneNumberIsRequired) {
			$rules['phone'][] = 'required';
		}
		$rules = $this->phoneRules($rules);
		
		return $this->captchaRules($rules);
	}
}
