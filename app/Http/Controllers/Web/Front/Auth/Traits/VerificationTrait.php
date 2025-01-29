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

namespace App\Http\Controllers\Web\Front\Auth\Traits;

use Illuminate\Support\Facades\Validator;

trait VerificationTrait
{
	use EmailVerificationTrait, PhoneVerificationTrait;
	
	/**
	 * URL: Verify User's Email Address or Phone Number
	 *
	 * @param $field
	 * @param string|null $token
	 * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
	 */
	public function verification($field, string $token = null)
	{
		$entitySlug = request()->segment(1);
		
		// Token doesn't exist
		if (empty($token)) {
			return $this->getVerificationForm($entitySlug, $field);
		}
		
		// Add required data in the request for API
		request()->merge(['entitySlug' => $entitySlug]);
		
		// Token exists
		// Call API endpoint
		$endpoint = '/' . $entitySlug . '/verify/' . $field . '/' . $token;
		$data = makeApiRequest(method: 'get', uri: $endpoint, data: request()->all());
		
		// Parsing the API response
		$message = data_get($data, 'message', t('unknown_error'));
		
		// HTTP Error Found
		if (!data_get($data, 'isSuccessful')) {
			flash($message)->error();
			
			return $this->getVerificationForm($entitySlug, $field);
		}
		
		// Get the Entity Object (User or Post model's entry)
		$entityObject = data_get($data, 'result');
		
		// Check the request status
		if (data_get($data, 'success')) {
			flash($message)->success();
		} else {
			flash($message)->error();
			
			if (empty($entityObject)) {
				return $this->getVerificationForm($entitySlug, $field);
			}
		}
		
		$nextUrl = url('/?from=verification');
		
		// Remove Notification Trigger
		if (session()->has('emailOrPhoneChanged')) {
			session()->forget('emailOrPhoneChanged');
		}
		if (session()->has('emailVerificationSent')) {
			session()->forget('emailVerificationSent');
		}
		if (session()->has('phoneVerificationSent')) {
			session()->forget('phoneVerificationSent');
		}
		
		// users
		if ($entitySlug == 'users') {
			$user = $entityObject;
			
			if (data_get($data, 'extra.authToken') && data_get($user, 'id')) {
				// Auto logged in the User
				if (auth()->loginUsingId(data_get($user, 'id'))) {
					session()->put('authToken', data_get($data, 'extra.authToken'));
					$nextUrl = url('account');
				} else {
					if (session()->has('userNextUrl')) {
						$nextUrl = session('userNextUrl');
					} else {
						$nextUrl = urlGen()->login();
					}
				}
			}
			
			// Remove Next URL session
			if (session()->has('userNextUrl')) {
				session()->forget('userNextUrl');
			}
		}
		
		// posts
		if ($entitySlug == 'posts') {
			$post = $entityObject;
			
			// Get Listing creation next URL
			if (session()->has('itemNextUrl')) {
				$nextUrl = session('itemNextUrl');
				if (str_contains($nextUrl, 'create') && !session()->has('postId')) {
					$nextUrl = urlGen()->postUri($post);
				}
			} else {
				$nextUrl = urlGen()->postUri($post);
			}
			
			// Remove Next URL session
			if (session()->has('itemNextUrl')) {
				session()->forget('itemNextUrl');
			}
		}
		
		// password (Forgot Password)
		if ($entitySlug == 'password') {
			$nextUrl = url()->previous();
			if (session()->has('passwordNextUrl')) {
				$nextUrl = session('passwordNextUrl');
				
				// Remove Next URL session
				session()->forget('passwordNextUrl');
			}
		}
		
		return redirect()->to($nextUrl);
	}
	
	/**
	 * Form to fill the token value in the verification URL
	 *
	 * @param $entitySlug
	 * @param $field
	 * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
	 */
	private function getVerificationForm($entitySlug, $field)
	{
		// If the token's form is submitted...
		if (request()->filled('_token')) {
			// If the token field is not filled, back to the token form
			$validator = Validator::make(request()->all(), ['code' => 'required']);
			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();
			}
			
			// If the token is submitted, then add it in the URL and redirect users to that URL
			$token = request()->input('code');
			if (!empty($token) && is_string($token)) {
				$nextUrl = $entitySlug . '/verify/' . $field . '/' . $token;
				
				return redirect()->to($nextUrl);
			}
		}
		
		// If token doesn't exist and token form is not submitted,
		// Show token form
		return view('front.token');
	}
}
