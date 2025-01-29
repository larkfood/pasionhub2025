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
use App\Models\Scopes\VerifiedScope;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class BlacklistPhoneRule implements ValidationRule
{
	/**
	 * Run the validation rule.
	 */
	public function validate(string $attribute, mixed $value, Closure $fail): void
	{
		if (!$this->passes($attribute, $value)) {
			$fail(trans('validation.blacklist_phone_rule'));
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
		$value = trim(strtolower($value));
		$value = ltrim($value, '+');
		$valueWithPrefix = '+' . $value;
		
		// Banned phone number
		$blacklisted = Blacklist::ofType('phone')
			->where(function ($query) use ($value, $valueWithPrefix) {
				$query->where('entry', $value)->orWhere('entry', $valueWithPrefix);
			})->first();
		
		if (!empty($blacklisted)) {
			return false;
		}
		
		// Blocked user's phone number
		$user = User::query()
			->withoutGlobalScopes([VerifiedScope::class])
			->where(function ($query) use ($value, $valueWithPrefix) {
				$query->where('phone', $value)->orWhere('phone', $valueWithPrefix);
			})
			->where('blocked', 1)
			->first();
		
		return empty($user);
	}
}
