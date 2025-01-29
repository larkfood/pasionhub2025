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

class LocaleOfLanguageRule implements ValidationRule
{
	public ?string $langCode = null;
	
	public function __construct(?string $langCode)
	{
		$this->langCode = $langCode;
	}
	
	/**
	 * Run the validation rule.
	 */
	public function validate(string $attribute, mixed $value, Closure $fail): void
	{
		$errorMessage = $this->passes($attribute, $value);
		if (!empty($errorMessage)) {
			$fail($errorMessage);
		}
	}
	
	/**
	 * Determine if the validation rule passes.
	 * Check the Locale related to the Language Code.
	 *
	 * @param string $attribute
	 * @param mixed $value
	 * @return string|null
	 */
	public function passes(string $attribute, mixed $value): ?string
	{
		$value = getAsString($value);
		
		$errorMessage = null;
		
		// Get locale list from "installed", if this cannot be got, then get list from "referrer"
		$locales = getLocalesWithName();
		
		// Check if the value exists in $locales
		if (!$this->doesLocaleExist($value, $locales)) {
			$errorMessage = trans('validation.locale_of_language_rule');
			
			// Get locale list from "installed"
			$installedLocales = getLocalesWithName('installed');
			
			// Check if the value exists in $installedLocales
			if (!$this->doesLocaleExist($value, $installedLocales)) {
				$localeLabel = '"<span class="fw-bold">' . $value . '</span>"';
				$localesCommand = '"<span class="fw-bold">locale -a</span>"';
				
				$message = 'The locale %s is required to enable support for this language,';
				$message .= ' as the PHP intl extension relies on the availability of the corresponding locale on your system.';
				$message .= ' Currently, %s is not installed. To check the installed locales, run the command %s in your terminal.';
				$message .= ' If %s is missing, you will need to install it to enable proper support for this language.';
				
				$errorMessage = sprintf($message, $localeLabel, $localeLabel, $localesCommand, $localeLabel);
			}
		}
		
		return getAsStringOrNull($errorMessage);
	}
	
	/**
	 * Check if a locale exists in list of locales
	 *
	 * @param mixed $value
	 * @param array $locales
	 * @return bool
	 */
	public function doesLocaleExist(string $value, array $locales): bool
	{
		$filtered = collect($locales)
			->filter(function ($name, $locale) {
				return str_starts_with($locale, $this->langCode);
			});
		
		if ($filtered->isNotEmpty()) {
			return str_starts_with($value, $this->langCode);
		}
		
		return isset($locales[$value]);
	}
}
