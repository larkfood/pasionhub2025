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

namespace App\Http\Requests\Traits;

use App\Rules\BlacklistPhoneRule;
use App\Rules\PhoneRule;
use Illuminate\Foundation\Http\FormRequest;

trait HasPhoneInput
{
	/**
	 * Prepare the phone data for validation
	 *
	 * @param \Illuminate\Foundation\Http\FormRequest $request
	 * @param array $input
	 * @param string $field
	 * @param bool $isForAuth
	 * @return array
	 */
	function preparePhoneForValidation(FormRequest $request, array $input= [], string $field = 'phone', bool $isForAuth = false): array
	{
		$phoneNationalField = $field . '_national';
		
		if (!$request->filled($field)) {
			$input[$field] = null;
			if (!$isForAuth) {
				$input[$phoneNationalField] = null;
			}
			
			return $input;
		}
		
		// Get original value
		$phone = $request->input($field);
		
		// Format the phone number and save it in the form request
		$input[$field] = phoneE164($phone, getPhoneCountry());
		
		// If the request is not to authenticate a user,
		// Add the phone national format in the form request, to allow it to be saved in the database
		// Note: That prevents the field from being taken into account during the authentication process
		if (!$isForAuth) {
			$input[$phoneNationalField] = phoneNational($phone, getPhoneCountry());
		}
		
		return $input;
	}
	
	/**
	 * Valid Phone Number Rules
	 *
	 * @param array $rules
	 * @param string $field
	 * @return array
	 */
	protected function phoneRules(array $rules = [], string $field = 'phone'): array
	{
		if ($this->filled($field)) {
			$rules[$field][] = new BlacklistPhoneRule();
			$rules[$field][] = new PhoneRule(getPhoneCountry());
		}
		
		return $rules;
	}
}
