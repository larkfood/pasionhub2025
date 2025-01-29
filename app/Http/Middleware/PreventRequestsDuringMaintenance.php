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
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PreventRequestsDuringMaintenance extends Middleware
{
	/**
	 * The URIs that should be reachable while maintenance mode is enabled.
	 *
	 * @var array
	 */
	protected $except = [];
	
	/**
	 * Create a new middleware instance.
	 *
	 * @param Application $app
	 * @return void
	 */
	public function __construct(Application $app)
	{
		parent::__construct($app);
		
		$this->except = [
			admin_uri() . '/*',
			admin_uri(),
			'upgrade',
			'upgrade/run',
			'captcha/*',
			'api/captcha/*',
			dynamicRoute('routes.login') . '/*',
			dynamicRoute('routes.login'),
			dynamicRoute('routes.logout') . '/*',
			dynamicRoute('routes.logout'),
			'api/auth/login',
			'api/auth/logout/*',
		];
	}
	
	/**
	 * Handle an incoming request.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure $next
	 * @return mixed
	 *
	 * @throws \Symfony\Component\HttpKernel\Exception\HttpException
	 */
	public function handle($request, Closure $next)
	{
		if ($this->app->isDownForMaintenance()) {
			$down = $this->app->storagePath() . '/framework/down';
			
			if (file_exists($down)) {
				$data = json_decode(file_get_contents($down), true);
				
				if (isset($data['secret']) && $request->path() === $data['secret']) {
					return $this->bypassResponse($data['secret']);
				}
				
				if ($this->hasValidBypassCookie($request, $data) || $this->inExceptArray($request)) {
					return $next($request);
				}
				
				if ($this->shouldPassThroughIp($request)) {
					return $next($request);
				}
				
				if (isset($data['redirect'])) {
					$path = $data['redirect'] === '/'
						? $data['redirect']
						: trim($data['redirect'], '/');
					
					if ($request->path() !== $path) {
						return redirect()->to($path);
					}
				}
				
				if (isset($data['template'])) {
					return response(
						$data['template'],
						$data['status'] ?? 503,
						$this->getHeaders($data)
					);
				}
				
				throw new HttpException(
					$data['status'] ?? 503,
					'Service Unavailable',
					null,
					$this->getHeaders($data)
				);
			}
		}
		
		return $next($request);
	}
	
	/**
	 * @param \Illuminate\Http\Request $request
	 * @return bool
	 */
	protected function shouldPassThroughIp(Request $request): bool
	{
		$maintenanceIpAddresses = config('larapen.core.maintenanceIpAddresses');
		if (is_array($maintenanceIpAddresses)) {
			if (in_array($request->ip(), $maintenanceIpAddresses)) {
				return true;
			}
		}
		
		return false;
	}
}
