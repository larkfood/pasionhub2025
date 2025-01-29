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
use Illuminate\Support\Facades\Redis;

class RedisConnectionRule implements ValidationRule
{
	private string $errorMessage = 'The Redis configuration is invalid or the server is unreachable.';
	
	/**
	 * Run the validation rule.
	 */
	public function validate(string $attribute, mixed $value, Closure $fail): void
	{
		if (!$this->passes($attribute, $value)) {
			$fail($this->errorMessage);
		}
	}
	
	/**
	 * Determine if the Redis connection is valid.
	 *
	 * @param  string  $attribute
	 * @param  mixed  $value
	 * @return bool
	 */
	public function passes(string $attribute, mixed $value): bool
	{
		$errorFound = true;
		
		try {
			
			// Attempt to ping the Redis server
			Redis::ping();
			
		} catch (\Exception $e) {
			$message = $e->getMessage();
			if (!empty($message)) {
				$this->errorMessage .= ' ERROR: <span class="fw-bold">' . $message . '</span>';
			}
			
			logger()->error(strip_tags($this->errorMessage));
			
			$errorFound = false;
		}
		
		return $errorFound;
	}
}
