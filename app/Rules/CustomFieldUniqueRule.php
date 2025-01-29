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

namespace App\Rules;

use App\Models\CategoryField;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CustomFieldUniqueRule implements ValidationRule
{
	public array $parameters = [];
	
	public function __construct($parameters)
	{
		$this->parameters = $parameters;
	}
	
	/**
	 * Run the validation rule.
	 */
	public function validate(string $attribute, mixed $value, Closure $fail): void
	{
		if (!$this->passes($attribute, $value)) {
			if ($attribute == 'category_id') {
				$message = trans('validation.custom_field_unique_rule', [
					'field_1' => trans('admin.category'),
					'field_2' => trans('admin.custom field'),
				]);
			} else {
				$message = trans('validation.custom_field_unique_rule_field', [
					'field_1' => trans('admin.custom field'),
					'field_2' => trans('admin.category'),
				]);
			}
			
			$fail($message);
		}
	}
	
	/**
	 * Determine if the validation rule passes.
	 * Prevent duplicate content (Category & Custom Field) in 'category_field' table
	 *
	 * @param string $attribute
	 * @param mixed $value
	 * @return bool
	 */
	public function passes(string $attribute, mixed $value): bool
	{
		$value = getAsString($value);
		
		if (!isset($this->parameters[0]) || !isset($this->parameters[1])) {
			return false;
		}
		
		$categoryFields = CategoryField::query()
			->where($this->parameters[0], $this->parameters[1])
			->where($attribute, $value);
		
		return ($categoryFields->count() <= 0);
	}
}
