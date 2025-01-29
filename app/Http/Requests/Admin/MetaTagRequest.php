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

use Illuminate\Validation\Rule;

class MetaTagRequest extends Request
{
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		$rules = [
			'title'       => ['required', 'max:255'],
			'description' => ['required', 'max:10000'],
			'keywords'    => ['max:10000'],
		];
		
		if (in_array($this->method(), ['POST', 'CREATE'])) {
			$rules['page'][] = 'required';
			
			// Unique with additional Where Clauses
			$uniquePage = Rule::unique('meta_tags')->where(function ($query) {
				return $query->where('page', $this->page);
			});
			
			$rules['page'][] = $uniquePage;
		}
		
		return $rules;
	}
	
	/**
	 * @return array
	 */
	public function messages(): array
	{
		$messages = [];
		
		$messages['page.unique'] = trans('admin.A meta-tag entry already exists for this page');
		
		return $messages;
	}
}
