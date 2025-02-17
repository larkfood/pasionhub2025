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

use App\Http\Controllers\Web\Front\Auth\Traits\VerificationTrait;
use App\Http\Controllers\Web\Front\FrontController;
use App\Http\Requests\Front\UserRequest;
use Larapen\LaravelMetaTags\Facades\MetaTag;

class RegisterController extends FrontController
{
	use VerificationTrait;
	
	/**
	 * Where to redirect users after login / registration.
	 *
	 * @var string
	 */
	protected string $redirectTo = '/account';
	
	/**
	 * RegisterController constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->commonQueries();
	}
	
	/**
	 * Common Queries
	 */
	public function commonQueries()
	{
		$this->redirectTo = 'account';
	}
	
	/**
	 * Show the form the creation a new user account.
	 *
	 * @return \Illuminate\Contracts\View\View
	 */
	public function showRegistrationForm()
	{
		// Meta Tags
		[$title, $description, $keywords] = getMetaTag('register');
		MetaTag::set('title', $title);
		MetaTag::set('description', strip_tags($description));
		MetaTag::set('keywords', $keywords);
		
		return view('front.auth.register.index');
	}
	
	/**
	 * Register a new user account.
	 *
	 * @param \App\Http\Requests\Front\UserRequest $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function register(UserRequest $request)
	{
		// Call API endpoint
		$endpoint = '/users';
		$data = makeApiRequest(method: 'post', uri: $endpoint, data: $request->all());
		
		// Parsing the API response
		$message = data_get($data, 'message', t('unknown_error'));
		
		// HTTP Error Found
		if (!data_get($data, 'isSuccessful')) {
			return redirect()->back()->withErrors(['error' => $message])->withInput();
		}
		
		// Notification Message
		if (data_get($data, 'success')) {
			session()->put('message', $message);
		} else {
			flash($message)->error();
		}
		
		// Get User Resource
		$user = data_get($data, 'result');
		
		// Get the next URL
		$nextUrl = url('register/finish');
		
		if (
			data_get($data, 'extra.sendEmailVerification.emailVerificationSent')
			|| data_get($data, 'extra.sendPhoneVerification.phoneVerificationSent')
		) {
			session()->put('userNextUrl', $nextUrl);
			
			if (data_get($data, 'extra.sendEmailVerification.emailVerificationSent')) {
				session()->put('emailVerificationSent', true);
				
				// Show the Re-send link
				$this->showReSendVerificationEmailLink($user, 'users');
			}
			
			if (data_get($data, 'extra.sendPhoneVerification.phoneVerificationSent')) {
				session()->put('phoneVerificationSent', true);
				
				// Show the Re-send link
				$this->showReSendVerificationSmsLink($user, 'users');
				
				// Go to Phone Number verification
				$nextUrl = url('users/verify/phone/');
			}
		} else {
			if (!empty(data_get($data, 'extra.authToken')) && !empty(data_get($user, 'id'))) {
				// Auto logged in the User
				if (auth()->loginUsingId(data_get($data, 'result.id'))) {
					session()->put('authToken', data_get($data, 'extra.authToken'));
					$nextUrl = url('account');
				}
			}
		}
		
		return redirect()->to($nextUrl);
	}
	
	/**
	 * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
	 */
	public function finish()
	{
		if (!session()->has('message')) {
			return redirect()->to('/');
		}
		
		// Meta Tags
		MetaTag::set('title', session('message'));
		MetaTag::set('description', session('message'));
		
		return view('front.auth.register.finish');
	}
}
