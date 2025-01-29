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

use App\Models\Blacklist;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class BlacklistWordRule implements ValidationRule
{
	/**
	 * Run the validation rule.
	 */
	public function validate(string $attribute, mixed $value, Closure $fail): void
	{
		if (!$this->passes($attribute, $value)) {
			$fail(trans('validation.blacklist_word_rule'));
		}
	}
	
	/**
	 * Determine if the validation rule passes.
	 *
	 * @param string $attribute
	 * @param mixed $value
	 * @return bool
	 */
	public function passes(string $attribute, mixed $value): bool
	{
		$value = getAsString($value);
		
		$words = Blacklist::whereIn('type', ['word', 'domain', 'email'])->get();
		
		$value = trim(mb_strtolower($value));
		if ($this->doesBannedEntryIsContainedInString($words, $value)) {
			return false;
		}
		
		// Remove all HTML tags from the $value and check again
		$value = strip_tags($value);
		if ($this->doesBannedEntryIsContainedInString($words, $value)) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Does a banned entry is contained in the string
	 *
	 * @param $words
	 * @param $value
	 * @return bool
	 */
	private function doesBannedEntryIsContainedInString($words, $value): bool
	{
		if ($words->count() > 0) {
			foreach ($words as $word) {
				// Check if a ban's word is contained in the user entry
				$startPatten = '\s\-.,;:=/#\|_<>';
				$endPatten = $startPatten . 's';
				try {
					if (preg_match('|[' . $startPatten . '\\\]+' . $word->entry . '[' . $endPatten . '\\\]+|ui', ' ' . $value . ' ')) {
						return true;
					}
				} catch (\Throwable $e) {
					if (preg_match('|[' . $startPatten . ']+' . $word->entry . '[' . $endPatten . ']+|ui', ' ' . $value . ' ')) {
						return true;
					}
				}
			}
		}
		
		return false;
	}
}
