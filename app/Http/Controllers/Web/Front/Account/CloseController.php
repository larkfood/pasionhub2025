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

use App\Helpers\Cookie;
use Illuminate\Http\RedirectResponse;

class CloseController extends AccountBaseController
{
	/**
	 * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
	 */
	public function index()
	{
		if (!isAccountClosureEnabled()) {
			flash(t('account_closure_disabled'))->error();
			
			return redirect()->to('account');
		}
		
		return view('front.account.close');
	}
	
	/**
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function submit(): RedirectResponse
	{
		// Has the user confirmed their account closure?
		$accountClosureConfirmed = (request()->input('close_account_confirmation') == 1);
		if (!$accountClosureConfirmed) {
			flash(t('account_closure_unconfirmed'))->info();
			
			return redirect()->back();
		}
		
		// Call API endpoint
		$endpoint = '/users/' . auth()->user()->getAuthIdentifier();
		$data = makeApiRequest(method: 'delete', uri: $endpoint, data: request()->all());
		
		// Parsing the API response
		$message = data_get($data, 'message', t('unknown_error'));
		
		// HTTP Error Found
		if (!data_get($data, 'isSuccessful')) {
			flash($message)->error();
			
			return redirect()->back()->withInput();
		}
		
		// Notification Message
		if (data_get($data, 'success')) {
			// Log out the user if he is still logged on a web device
			if (auth()->check()) {
				// The logout() method is no longer available (in auth()->logout()) once the user is deleted
				request()->session()->flush();
				request()->session()->regenerate();
			}
			
			// Remove all user's stored cookies (from his browser)
			Cookie::forgetAll();
			
			flash($message)->success();
		} else {
			flash($message)->error();
		}
		
		return redirect()->to('/');
	}
}
