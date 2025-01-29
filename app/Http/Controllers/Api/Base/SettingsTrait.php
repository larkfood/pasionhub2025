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

namespace App\Http\Controllers\Api\Base;

use App\Helpers\Cookie;

trait SettingsTrait
{
	public int $cacheExpiration = 3600;     // In seconds (e.g.: 60 * 60 for 1h)
	public int $cookieExpiration = 3600;    // In seconds (e.g.: 60 * 60 for 1h)
	
	/**
	 * Set all the front-end settings
	 */
	public function applyFrontSettings(): void
	{
		// Cache Expiration Time
		$this->cacheExpiration = (int)config('settings.optimization.cache_expiration');
		
		// Cookie Expiration Time
		$this->cookieExpiration = (int)config('settings.other.cookie_expiration');
		
		// CSRF Control
		// CSRF - Some JavaScript frameworks, like Angular, do this automatically for you.
		// It is unlikely that you will need to use this value manually.
		Cookie::set('X-XSRF-TOKEN', csrf_token(), $this->cookieExpiration);
	}
}
