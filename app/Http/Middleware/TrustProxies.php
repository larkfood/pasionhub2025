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

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Middleware\TrustProxies as Middleware;

class TrustProxies extends Middleware
{
	/**
	 * The trusted proxies for this application.
	 *
	 * @var array<int, string>|string|null
	 */
	protected $proxies = '*';
	
	/**
	 * The headers that should be used to detect proxies.
	 *
	 * @var int
	 */
	protected $headers =
		Request::HEADER_X_FORWARDED_FOR |
		Request::HEADER_X_FORWARDED_HOST |
		Request::HEADER_X_FORWARDED_PORT |
		Request::HEADER_X_FORWARDED_PROTO |
		Request::HEADER_X_FORWARDED_AWS_ELB;
}
