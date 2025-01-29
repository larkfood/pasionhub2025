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

use App\Rules\CustomFieldUniqueRule;
use App\Rules\CustomFieldUniqueChildrenRule;
use App\Rules\CustomFieldUniqueParentRule;

class CategoryFieldRequest extends Request
{
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		$rules = [];
		
		if ($this->segment(2) == 'categories') {
			$categoryId = $this->input('category_id');
			
			$rules['field_id'] = [
				'required',
				'not_in:0',
				new CustomFieldUniqueRule(['category_id', $categoryId]),
				new CustomFieldUniqueParentRule(['category_id', $categoryId]),
				new CustomFieldUniqueChildrenRule(['category_id', $categoryId]),
			];
		}
		
		if ($this->segment(2) == 'custom_fields') {
			$fieldId = $this->input('field_id');
			
			$rules['category_id'] = [
				'required',
				'not_in:0',
				new CustomFieldUniqueRule(['field_id', $fieldId]),
				new CustomFieldUniqueParentRule(['field_id', $fieldId]),
				new CustomFieldUniqueChildrenRule(['field_id', $fieldId]),
			];
		}
		
		return $rules;
	}
	
	/**
	 * @return array
	 */
	public function messages(): array
	{
		return [
			'category_id.required' => trans('admin.The field is required', ['field' => trans('admin.category')]),
			'category_id.not_in'   => trans('admin.The field is required And cannot be 0.', ['field' => trans('admin.category')]),
			'field_id.required'    => trans('admin.The field is required', ['field' => trans('admin.custom field')]),
			'field_id.not_in'      => trans('admin.The field is required And cannot be 0.', ['field' => trans('admin.custom field')]),
		];
	}
}
