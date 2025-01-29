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

class PageRequest extends Request
{
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		$rules = [
			'name'    => ['required', 'min:2', 'max:255'],
			'title'   => ['max:255'],
			'content' => ['max:16000000'],
		];
		
		if ($this->filled('external_link')) {
			$rules['external_link'] = ['url'];
		} else {
			$rules['title'][] = 'required';
			$rules['title'][] = 'min:2';
			$rules['content'][] = 'required';
		}
		
		return $rules;
	}
}
