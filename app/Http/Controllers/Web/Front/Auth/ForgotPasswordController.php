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
use App\Http\Requests\Front\ForgotPasswordRequest;
use App\Http\Controllers\Web\Front\FrontController;
use Larapen\LaravelMetaTags\Facades\MetaTag;

class ForgotPasswordController extends FrontController
{
	use VerificationTrait;
	
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
	 * Display the form to request a password reset link.
	 *
	 * @return \Illuminate\Contracts\View\View
	 */
	public function showLinkRequestForm()
	{
		// Meta Tags
		[$title, $description, $keywords] = getMetaTag('password');
		MetaTag::set('title', $title);
		MetaTag::set('description', strip_tags($description));
		MetaTag::set('keywords', $keywords);
		
		return view('front.auth.passwords.email');
	}
	
	/**
	 * Send a reset link to the given user.
	 *
	 * @param ForgotPasswordRequest $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function sendResetLink(ForgotPasswordRequest $request)
	{
		// Call API endpoint
		$endpoint = '/auth/password/email';
		$data = makeApiRequest(method: 'post', uri: $endpoint, data: $request->all());
		
		// Parsing the API response
		$message = data_get($data, 'message', t('unknown_error'));
		
		// Error Found
		if (!data_get($data, 'isSuccessful') || !data_get($data, 'success')) {
			return redirect()->back()
				->withInput($request->only('email'))
				->withErrors(['email' => $message]);
		}
		
		// phone
		if (data_get($data, 'extra.codeSentTo') == 'phone') {
			// Save the password reset link (in session)
			$resetPwdUrl = url('password/reset/' . data_get($data, 'extra.code'));
			session()->put('passwordNextUrl', $resetPwdUrl);
			
			// Phone Number verification
			// Get the token|code verification form page URL
			// The user is supposed to have received this token|code by SMS
			$nextUrl = url('password/verify/phone/');
			
			// Go to the verification page
			return redirect()->to($nextUrl);
		}
		
		// email
		return redirect()->back()->with(['status' => $message]);
	}
}
