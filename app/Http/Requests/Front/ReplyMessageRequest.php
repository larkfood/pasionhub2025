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
use App\Rules\BetweenRule;

class ReplyMessageRequest extends Request
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize(): bool
	{
		$guard = getAuthGuard();
		
		return auth($guard)->check();
	}
	
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
		$rules = [];
		
		if ($this->hasFile('file_path')) {
			$allowedFileFormats = collect(getAllowedFileFormats())->join(',');
			
			$rules['body'] = [new BetweenRule(1, 500)];
			$rules['file_path'] = [
				'mimes:' . $allowedFileFormats,
				'min:' . (int)config('settings.upload.min_file_size', 0),
				'max:' . (int)config('settings.upload.max_file_size', 1000),
			];
		} else {
			$rules['body'] = ['required', new BetweenRule(1, 500)];
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
		
		if ($this->file('file_path')) {
			$attributes['file_path'] = t('resume_file');
		}
		
		return $attributes;
	}
}
