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

use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class ValidateCsrfToken extends Middleware
{
	/**
	 * Indicates whether the XSRF-TOKEN cookie should be set on the response.
	 *
	 * @var bool
	 */
	protected $addHttpCookie = true;
	
	/**
	 * The URIs that should be excluded from CSRF verification.
	 *
	 * @var array
	 */
	protected $except = [];
	
	/**
	 * Handle an incoming request.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure $next
	 * @return mixed
	 *
	 * @throws \Illuminate\Session\TokenMismatchException
	 */
	public function handle($request, Closure $next)
	{
		// Exception for all requests when CSRF protection is disabled
		$isCsrfProtectionDisabled = (config('settings.security.csrf_protection') != '1');
		if ($isCsrfProtectionDisabled) {
			$this->except = ['*'];
			
			return parent::handle($request, $next);
		}
		
		// Exception for requests from API documentation
		if (request()->header('X-AppType') == 'docs') {
			$this->except = ['*'];
			
			return parent::handle($request, $next);
		}
		
		// Get the referrer URL
		$referrer = $request->headers->get('referer');
		if (!empty($referrer)) {
			// Extract the host from the referrer URL
			$referrerHost = parse_url($referrer, PHP_URL_HOST);
			
			// Extract the host from the app's URL
			$appUrl = config('app.url');
			$appHost = parse_url($appUrl, PHP_URL_HOST);
			
			// Is it request from an external referrer?
			$isRequestFromExternalReferrer = ($referrerHost !== $appHost);
			if ($isRequestFromExternalReferrer) {
				
				// Exception for requests from the installed payment gateways hosts
				if ($this->isRequestFromPaymentGateway($referrerHost)) {
					$this->except = ['*'];
					
					return parent::handle($request, $next);
				}
			}
		}
		
		// Exception for requests from Resend
		$this->except[] = 'resend/*';
		
		return parent::handle($request, $next);
	}
	
	/**
	 * Check if the current request is from an installed payment gateway host
	 * i.e. Check if the current referrer matches one of the installed payment gateways host
	 *
	 * @param string|null $referrerHost
	 * @return bool
	 */
	private function isRequestFromPaymentGateway(?string $referrerHost): bool
	{
		if (empty($referrerHost)) return false;
		
		// Get the payment plugins list
		$plugins = plugin_installed_list('payment');
		
		// Get the allowed hosts from the payment plugins referrers
		$paymentGatewaysHosts = collect($plugins)
			->map(function ($item, $key) {
				$referrersHosts = (array)config('payment.' . $key . '.referrersHosts');
				$referrersHosts[] = getAsString(config('payment.' . $key . '.baseUrl'));
				
				return array_unique($referrersHosts);
			})
			->flatten()
			->filter(fn ($item) => (!empty($item) && is_string($item)));
		
		// Check if the referrer host matches any host in the allowed hosts
		$requestOrigin = fn ($url) => str_contains($referrerHost, parse_url($url, PHP_URL_HOST));
		
		return $paymentGatewaysHosts->contains($requestOrigin);
	}
}
