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

namespace App\Http\Controllers\Web\Front\Auth;

use App\Http\Controllers\Web\Front\Auth\Helpers\RedirectsUsers;
use App\Http\Controllers\Web\Front\FrontController;
use App\Http\Requests\Front\ResetPasswordRequest;
use Illuminate\Http\Request;
use Larapen\LaravelMetaTags\Facades\MetaTag;

class ResetPasswordController extends FrontController
{
	use RedirectsUsers;
	
	/**
	 * Where to redirect users after resetting their password.
	 *
	 * @var string
	 */
	protected $redirectTo = '/account';
	
	/**
	 * Get the middleware that should be assigned to the controller.
	 */
	public static function middleware(): array
	{
		$array = ['guest'];
		
		return array_merge(parent::middleware(), $array);
	}
	
	// -------------------------------------------------------
	// Laravel overwrites for loading LaraClassifier views
	// -------------------------------------------------------
	
	/**
	 * Display the password reset view for the given token.
	 *
	 * If no token is present, display the link request form.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param $token
	 * @return \Illuminate\Contracts\View\View
	 */
	public function showResetForm(Request $request, $token = null)
	{
		// Meta Tags
		MetaTag::set('title', t('reset_password'));
		MetaTag::set('description', t('reset_your_password'));
		
		return view('front.auth.passwords.reset')->with([
			'token' => $token,
			'email' => $request->input('email'),
			'phone' => $request->input('phone'),
		]);
	}
	
	/**
	 * URL: Token Form
	 *
	 * @return \Illuminate\Contracts\View\View
	 */
	public function showTokenRequestForm()
	{
		return view('front.token');
	}
	
	/**
	 * Reset the given user's password.
	 *
	 * @param \App\Http\Requests\Front\ResetPasswordRequest $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function reset(ResetPasswordRequest $request)
	{
		// If the password was successfully reset,
		// we will redirect the user back to the application's home authenticated view.
		// If there is an error, we can redirect them back to where they came from with their error message.
		
		// Call API endpoint
		$endpoint = '/auth/password/reset';
		$data = makeApiRequest(method: 'post', uri: $endpoint, data: $request->all());
		
		// Parsing the API response
		$message = data_get($data, 'message', t('unknown_error'));
		
		if (data_get($data, 'isSuccessful') && data_get($data, 'success')) {
			if (
				!empty(data_get($data, 'extra.authToken'))
				&& !empty(data_get($data, 'result.id'))
			) {
				auth()->loginUsingId(data_get($data, 'result.id'));
				session()->put('authToken', data_get($data, 'extra.authToken'));
			}
			
			flash($message)->success();
			
			return redirect()->to($this->redirectPath())->with('status', $message);
		}
		
		return redirect()->back()
			->withInput($request->only('email'))
			->withErrors(['email' => $message]);
	}
}
