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

class AlphaPlusRule implements ValidationRule
{
	protected string|array $additionalChars;
	protected string $additionalCharsFormatted;
	
	/**
	 * Constructor to accept additional characters.
	 *
	 * @param string|array $additionalChars
	 */
	public function __construct(string|array $additionalChars = '')
	{
		$this->additionalChars = is_array($additionalChars) ? implode('', $additionalChars) : $additionalChars;
		$this->additionalCharsFormatted = $this->formatAdditionalChars($this->additionalChars);
	}
	
	/**
	 * Run the validation rule.
	 */
	public function validate(string $attribute, mixed $value, Closure $fail): void
	{
		if (!$this->passes($attribute, $value)) {
			$message = $this->additionalChars
				? trans('validation.alphabetic_plus_rule', ['additionalChars' => $this->additionalCharsFormatted])
				: trans('validation.alphabetic_only_rule');
			$fail($message);
		}
	}
	
	/**
	 * Determine if the validation rule passes.
	 *
	 * @param  string  $attribute
	 * @param  mixed  $value
	 * @return bool
	 */
	public function passes(string $attribute, mixed $value): bool
	{
		$pattern = '/^[a-zA-Z' . preg_quote($this->additionalChars, '/') . ']+$/';
		return preg_match($pattern, $value);
	}
	
	/**
	 * Format additional characters as a comma-separated string.
	 *
	 * @param  string  $additionalChars
	 * @return string
	 */
	protected function formatAdditionalChars(string $additionalChars): string
	{
		return implode(', ', str_split($additionalChars));
	}
}
