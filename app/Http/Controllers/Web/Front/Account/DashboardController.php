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

namespace App\Http\Controllers\Web\Front\Account;

use App\Enums\Gender;
use App\Http\Controllers\Web\Front\Auth\Traits\VerificationTrait;
use App\Http\Requests\Front\AvatarRequest;
use App\Http\Requests\Front\UserRequest;
use App\Http\Requests\Front\UserSettingsRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Larapen\LaravelMetaTags\Facades\MetaTag;

class DashboardController extends AccountBaseController
{
	use VerificationTrait;
	
	/**
	 * @return \Illuminate\Contracts\View\View
	 */
	public function index()
	{
		$genders = Gender::all('title');
		
		$appName = config('settings.app.name', 'Site Name');
		$title = t('my_account') . ' - ' . $appName;
		$description = t('my_account_on', ['appName' => config('settings.app.name')]);
		
		// Meta Tags
		MetaTag::set('title', $title);
		MetaTag::set('description', $description);
		
		return view('front.account.dashboard', compact('genders'));
	}
	
	/**
	 * Update the user's details
	 *
	 * @param \App\Http\Requests\Front\UserRequest $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function updateDetails(UserRequest $request): RedirectResponse
	{
		$authUser = auth()->user();
		
		// Call API endpoint
		$endpoint = '/users/' . $authUser->getAuthIdentifier();
		$data = makeApiRequest(method: 'put', uri: $endpoint, data: $request->all());
		
		// Parsing the API response
		$message = data_get($data, 'message', t('unknown_error'));
		
		// HTTP Error Found
		if (!data_get($data, 'isSuccessful')) {
			flash($message)->error();
			
			return redirect()->back()->withInput($request->except(['photo_path']));
		}
		
		// Notification Message
		if (data_get($data, 'success')) {
			flash($message)->success();
		} else {
			flash($message)->error();
		}
		
		// Get User Resource
		$user = data_get($data, 'result');
		
		// Don't log out the User (See the User model's file)
		if (data_get($data, 'extra.emailOrPhoneChanged')) {
			session()->put('emailOrPhoneChanged', true);
		}
		
		// Get Query String
		$params = [];
		if ($request->filled('panel')) {
			$params['panel'] = $request->input('panel');
		}
		
		// Get the next URL
		$nextUrl = urlQuery(url('account'))->setParameters($params)->toString();
		
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
		}
		
		// Mail Notification Message
		if (data_get($data, 'extra.mail.message')) {
			$mailMessage = data_get($data, 'extra.mail.message');
			if (data_get($data, 'extra.mail.success')) {
				flash($mailMessage)->success();
			} else {
				flash($mailMessage)->error();
			}
		}
		
		return redirect()->to($nextUrl);
	}
	
	/**
	 * Update the user's settings
	 *
	 * @param \App\Http\Requests\Front\UserSettingsRequest $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function updateSettings(UserSettingsRequest $request): RedirectResponse
	{
		$authUser = auth()->user();
		
		// Call API endpoint
		$endpoint = '/users/' . $authUser->getAuthIdentifier() . '/settings';
		$data = makeApiRequest(method: 'put', uri: $endpoint, data: $request->all());
		
		// Parsing the API response
		$message = data_get($data, 'message', t('unknown_error'));
		
		// HTTP Error Found
		if (!data_get($data, 'isSuccessful')) {
			flash($message)->error();
			
			return redirect()->back()->withInput($request->except(['photo_path']));
		}
		
		// Notification Message
		if (data_get($data, 'success')) {
			flash($message)->success();
		} else {
			flash($message)->error();
		}
		
		// Get User Resource
		// $user = data_get($data, 'result');
		
		// Get Query String
		$params = [];
		if ($request->filled('panel')) {
			$params['panel'] = $request->input('panel');
		}
		
		// Get the next URL
		$nextUrl = urlQuery(url('account'))->setParameters($params)->toString();
		
		return redirect()->to($nextUrl);
	}
	
	/**
	 * Update the user's photo
	 *
	 * @param \App\Http\Requests\Front\AvatarRequest $request
	 * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
	 */
	public function updatePhoto(AvatarRequest $request): JsonResponse|RedirectResponse
	{
		$authUser = auth()->user();
		
		// Call API endpoint
		$endpoint = '/users/' . $authUser->getAuthIdentifier() . '/photo';
		$data = makeApiRequest(method: 'put', uri: $endpoint, data: $request->all(), files: $request->allFiles());
		
		// Parsing the API response
		return $this->handlePhotoApiData($data);
	}
	
	/**
	 * Delete the user's photo
	 *
	 * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
	 */
	public function deletePhoto(): JsonResponse|RedirectResponse
	{
		$authUser = auth()->user();
		
		// Call API endpoint
		$endpoint = '/users/' . $authUser->getAuthIdentifier() . '/photo/delete';
		$data = makeApiRequest(method: 'get', uri: $endpoint);
		
		// Parsing the API response
		return $this->handlePhotoApiData($data);
	}
	
	/**
	 * @param $data
	 * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
	 */
	private function handlePhotoApiData($data): JsonResponse|RedirectResponse
	{
		// Parsing the API response
		$status = (int)data_get($data, 'status');
		$message = data_get($data, 'message', t('unknown_error'));
		
		// HTTP Error Found
		if (!data_get($data, 'isSuccessful')) {
			// AJAX Response
			if (isFromAjax()) {
				return ajaxResponse()->json(['error' => $message], $status);
			}
			
			flash($message)->error();
			
			return redirect()->to(url('account'))->withInput();
		}
		
		// AJAX Response
		if (isFromAjax()) {
			if (!data_get($data, 'success')) {
				return ajaxResponse()->json(['error' => $message], $status);
			}
			
			$fileInput = data_get($data, 'extra.fileInput');
			
			return ajaxResponse()->json($fileInput);
		}
		
		// Notification Message
		if (data_get($data, 'success')) {
			flash($message)->success();
		} else {
			flash($message)->error();
		}
		
		return redirect()->to(url('account'));
	}
}
