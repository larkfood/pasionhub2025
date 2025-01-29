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

namespace App\Http\Requests\Admin;

use App\Helpers\Num;
use App\Http\Requests\Traits\HasEmailInput;
use App\Http\Requests\Traits\HasPhoneInput;
use App\Rules\BetweenRule;
use Illuminate\Validation\Rule;

class PostRequest extends Request
{
	use HasEmailInput, HasPhoneInput;
	
	/**
	 * Prepare the data for validation.
	 *
	 * @return void
	 */
	protected function prepareForValidation(): void
	{
		$input = $this->all();
		
		// price
		if ($this->has('price')) {
			if ($this->filled('price')) {
				$input['price'] = $this->input('price');
				// If field's value contains only numbers and dot,
				// Then decimal separator is set as dot.
				if (preg_match('/^[0-9.]*$/', $input['price'])) {
					$input['price'] = Num::formatForDb($input['price'], '.');
				} else {
					if ($this->filled('currency_decimal_separator')) {
						$input['price'] = Num::formatForDb($input['price'], $this->input('currency_decimal_separator'));
					}
				}
			} else {
				$input['price'] = null;
			}
		}
		
		// currency_code (Not implemented)
		if ($this->filled('currency_code')) {
			$input['currency_code'] = $this->input('currency_code');
		}
		
		// auth_field
		$input['auth_field'] = getAuthField();
		
		// phone
		$input = $this->preparePhoneForValidation($this, $input);
		
		// tags
		if ($this->filled('tags')) {
			$input['tags'] = tagCleaner($this->input('tags'));
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
		$rules = [];
		
		$authFields = array_keys(getAuthFields());
		
		$rules['category_id'] = ['required', 'not_in:0'];
		if (config('settings.listing_form.show_listing_type')) {
			$rules['post_type_id'] = ['required', 'not_in:0'];
		}
		$rules['title'] = [
			'required',
			new BetweenRule(
				(int)config('settings.listing_form.title_min_length', 2),
				(int)config('settings.listing_form.title_max_length', 150)
			),
		];
		$rules['description'] = [
			'required',
			new BetweenRule(
				(int)config('settings.listing_form.description_min_length', 5),
				(int)config('settings.listing_form.description_max_length', 6000)
			),
		];
		$rules['contact_name'] = ['required', new BetweenRule(2, 200)];
		$rules['auth_field'] = ['required', Rule::in($authFields)];
		$rules['phone'] = ['max:30'];
		$rules['phone_country'] = ['required_with:phone'];
		
		$phoneIsEnabledAsAuthField = (config('settings.sms.enable_phone_as_auth_field') == '1');
		$phoneNumberIsRequired = ($phoneIsEnabledAsAuthField && $this->input('auth_field') == 'phone');
		
		// email
		$emailIsRequired = (!$phoneNumberIsRequired);
		if ($emailIsRequired) {
			$rules['email'][] = 'required';
		}
		$rules = $this->emailRules($rules);
		
		// phone
		if ($phoneNumberIsRequired) {
			$rules['phone'][] = 'required';
		}
		$rules = $this->phoneRules($rules);
		
		// Tags
		if ($this->filled('tags')) {
			$rules['tags.*'] = ['regex:' . tagRegexPattern()];
		}
		
		return $rules;
	}
	
	/**
	 * Get custom attributes for validator errors.
	 *
	 * @return array
	 */
	public function attributes(): array
	{
		$attributes = [];
		
		if ($this->filled('tags')) {
			foreach ($this->input('tags') as $key => $tag) {
				$attributes['tags.' . $key] = t('tag_x', ['key' => ($key + 1)]);
			}
		}
		
		return $attributes;
	}
}
