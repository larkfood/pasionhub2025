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

namespace App\Http\Controllers\Web\Admin\Auth;

use App\Helpers\Cookie;
use App\Http\Controllers\Web\Admin\Controller;
use App\Http\Requests\Front\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;

class LoginController extends Controller
{
	// If not logged in redirect to
	protected mixed $loginPath;
	
	// After you've logged in redirect to
	protected $redirectTo;
	
	// After you've logged out redirect to
	protected $redirectAfterLogout;
	
	// The maximum number of attempts to allow
	protected $maxAttempts = 5;
	
	// The number of minutes to throttle for
	protected $decayMinutes = 15;
	
	/**
	 * LoginController constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		
		// Set default URLs
		$this->loginPath = admin_uri('login');
		$this->redirectTo = admin_uri();
		$this->redirectAfterLogout = admin_uri('login');
		
		// Get values from Config
		$this->maxAttempts = (int)config('settings.security.login_max_attempts', $this->maxAttempts);
		$this->decayMinutes = (int)config('settings.security.login_decay_minutes', $this->decayMinutes);
	}
	
	/**
	 * Get the middleware that should be assigned to the controller.
	 */
	public static function middleware(): array
	{
		$array = [
			new Middleware('guest', except: ['logout']),
		];
		
		return array_merge(parent::middleware(), $array);
	}
	
	// -------------------------------------------------------
	// Laravel overwrites for loading admin views
	// -------------------------------------------------------
	
	/**
	 * Show the application login form.
	 *
	 * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
	 */
	public function showLoginForm()
	{
		// Remembering Login
		if (auth()->viaRemember()) {
			return redirect()->intended($this->redirectTo);
		}
		
		return view('admin.auth.login', ['title' => trans('admin.login')]);
	}
	
	/**
	 * @param \App\Http\Requests\Front\LoginRequest $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function login(LoginRequest $request)
	{
		// Call API endpoint
		$endpoint = '/auth/login';
		$data = makeApiRequest(method: 'post', uri: $endpoint, data: $request->all());
		
		// Parsing the API response
		if (
			data_get($data, 'isSuccessful')
			&& data_get($data, 'success')
			&& !empty(data_get($data, 'extra.authToken'))
			&& !empty(data_get($data, 'result.id'))
		) {
			auth()->loginUsingId(data_get($data, 'result.id'));
			session()->put('authToken', data_get($data, 'extra.authToken'));
			
			// Check & Enable Dark Mode
			if (isDarkModeEnabledForCurrentUser()) {
				Cookie::set('darkTheme', 'dark');
			}
			
			if (data_get($data, 'extra.isAdmin')) {
				return redirect()->to(admin_uri());
			}
			
			return redirect()->intended($this->redirectTo);
		}
		
		// Check and retrieve the previous URL to show the login error on it.
		if (session()->has('url.intended')) {
			$this->loginPath = session('url.intended');
		}
		
		$message = data_get($data, 'message', trans('auth.failed'));
		
		return redirect()->to($this->loginPath)->withErrors(['error' => $message])->withInput();
	}
	
	/**
	 * @param Request $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function logout(Request $request)
	{
		$userId = auth()->check() ? auth()->user()->id : null;
		
		// Call API endpoint
		$endpoint = '/auth/logout/' . $userId;
		$data = makeApiRequest(method: 'get', uri: $endpoint);
		
		// Parsing the API response
		$message = data_get($data, 'message', t('unknown_error'));
		
		if (data_get($data, 'isSuccessful')) {
			// Log out the user on a web client (Browser)
			logoutOnClient($message);
			
			// Reset Dark Mode
			Cookie::forget('darkTheme');
		} else {
			notification($message, 'error');
		}
		
		return redirect()->to(property_exists($this, 'redirectAfterLogout') ? $this->redirectAfterLogout : '/');
	}
}
