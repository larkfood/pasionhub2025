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

use App\Http\Middleware\Install\CheckInstallation;
use Closure;
use Illuminate\Http\Request;

class Install
{
	use CheckInstallation;
	
	/**
	 * Handle an incoming request.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure $next
	 * @return \Illuminate\Http\RedirectResponse|mixed
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	public function handle(Request $request, Closure $next)
	{
		if ($this->isInstalled() && $this->installationIsNotInProgress()) {
			return redirect()->to('/');
		}
		
		return $next($request);
	}
}
