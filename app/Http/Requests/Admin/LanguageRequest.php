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

use App\Models\Language;
use App\Rules\BetweenRule;
use App\Rules\LocaleOfLanguageRule;

class LanguageRequest extends Request
{
	/**
	 * Prepare the data for validation.
	 *
	 * @return void
	 */
	protected function prepareForValidation(): void
	{
		$input = $this->all();
		
		if ($this->filled('code')) {
			$code = $this->input('code');
			
			// name
			$input['name'] = getRegionalLocaleName($code);
			
			// native
			if (!$this->filled('native')) {
				$input['native'] = $input['name'];
			}
			
			// locale
			if (!$this->filled('locale')) {
				$input['locale'] = getRegionalLocaleCode($code, false);
			}
		}
		
		// direction
		if (!$this->filled('direction')) {
			$input['direction'] = 'ltr';
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
		$code = $this->input('code');
		
		$rules = [
			'code'   => ['required', 'min:2', 'max:20'],
			'native' => ['required', new BetweenRule(2, 100)],
		];
		
		$nameRules = ['required', new BetweenRule(2, 100)];
		$localeRules = ['required', 'min:2', 'max:25', new LocaleOfLanguageRule($code)];
		
		// No possibility to fill the 'name' field,
		// It is obtained using the language code field
		if (!empty($code)) {
			$rules['name'] = $nameRules;
		}
		
		// The 'locale' field is presented in the form and can be filled
		if ($this->has('locale')) {
			$rules['locale'] = $localeRules;
		}
		
		if (in_array($this->method(), ['POST', 'CREATE'])) {
			$rules['code'][] = 'unique:languages,code';
			
			// No possibility to fill the 'locale' field,
			// It is obtained using the language code field
			if (!empty($code)) {
				$rules['locale'] = $localeRules;
			}
		}
		
		if (in_array($this->method(), ['PUT', 'PATCH', 'UPDATE'])) {
			if (!empty($code)) {
				$language = Language::query()->where('code', '=', $code)->first();
				
				$codeChanged = (!empty($language) && $code != $language->code);
				if ($codeChanged) {
					$rules['code'][] = 'unique:languages,code';
				}
			}
		}
		
		return $rules;
	}
	
	/**
	 * @return array
	 */
	public function messages(): array
	{
		$messages = [];
		
		// code
		if ($this->filled('code')) {
			if (in_array($this->method(), ['POST', 'CREATE'])) {
				$messages['code.unique'] = t('language_code_unique_store');
			}
			if (in_array($this->method(), ['PUT', 'PATCH', 'UPDATE'])) {
				$messages['code.unique'] = t('language_code_unique_update');
			}
		}
		
		return $messages;
	}
	
	/**
	 * Get custom attributes for validator errors.
	 *
	 * @return array<string, string>
	 */
	public function attributes(): array
	{
		return [
			'code'   => mb_strtolower(trans('admin.language')),
			'native' => mb_strtolower(trans('admin.native_name')),
		];
	}
}
