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

namespace App\Http\Requests\Traits;

use App\Rules\BlacklistDomainRule;
use App\Rules\BlacklistEmailRule;
use App\Rules\EmailRule;

trait HasEmailInput
{
	/**
	 * Valid Email Address Rules
	 *
	 * @param array $rules
	 * @param string $field
	 * @return array
	 */
	protected function emailRules(array $rules = [], string $field = 'email'): array
	{
		if ($this->filled($field)) {
			if (isDemoEnv()) {
				if (isDemoEmailAddress($this->input($field))) {
					return $rules;
				}
			}
			
			$rules[$field][] = new EmailRule();
			$rules[$field][] = 'max:100';
			$rules[$field][] = new BlacklistEmailRule();
			$rules[$field][] = new BlacklistDomainRule();
			
			$params = [];
			if (config('settings.security.email_validator_rfc')) {
				$params[] = 'rfc';
			}
			if (config('settings.security.email_validator_strict')) {
				$params[] = 'strict';
			}
			if (extension_loaded('intl')) {
				if (config('settings.security.email_validator_dns')) {
					$params[] = 'dns';
				}
				if (config('settings.security.email_validator_spoof')) {
					$params[] = 'spoof';
				}
			}
			if (config('settings.security.email_validator_filter')) {
				$params[] = 'filter';
			}
			
			if (!empty($params)) {
				$rules[$field][] = 'email:' . implode(',', $params);
			}
		}
		
		return $rules;
	}
}
