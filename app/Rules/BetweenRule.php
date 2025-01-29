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

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class BetweenRule implements ValidationRule
{
	public int $min = 0;
	public int $max = 999999;
	
	public function __construct($min, $max)
	{
		$this->min = $min;
		$this->max = $max;
	}
	
	/**
	 * Run the validation rule.
	 */
	public function validate(string $attribute, mixed $value, Closure $fail): void
	{
		if (!$this->passes($attribute, $value)) {
			$fail(trans('validation.between_rule', ['min' => $this->min, 'max' => $this->max]));
		}
	}
	
	/**
	 * Determine if the validation rule passes.
	 * Multi-bytes version of the Laravel "between" rule.
	 *
	 * @param string $attribute
	 * @param mixed $value
	 * @return bool
	 */
	public function passes(string $attribute, mixed $value): bool
	{
		$value = getAsString($value);
		$value = strip_tags($value);
		
		if (mb_strlen($value) < $this->min) {
			return false;
		} else {
			if (mb_strlen($value) > $this->max) {
				return false;
			}
		}
		
		return true;
	}
}
