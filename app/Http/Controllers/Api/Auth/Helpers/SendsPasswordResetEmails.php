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

namespace App\Http\Controllers\Api\Auth\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

trait SendsPasswordResetEmails
{
	/**
	 * Send a reset link to the given user.
	 *
	 * @bodyParam email string required The user's email address. Example: null
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function sendResetLinkEmail(Request $request): \Illuminate\Http\JsonResponse
	{
		$request->validate(['email' => 'required|email']);
		
		$credentials = $request->only('email');
		
		// We will send the password reset link to this user. Once we have attempted
		// to send the link, we will examine the response then see the message we
		// need to show to the user. Finally, we'll send out a proper response.
		$status = Password::sendResetLink($credentials);
		
		$message = trans($status);
		
		$data = [
			'success' => true,
			'message' => $message,
			'result'  => null,
			'extra'   => [
				'codeSentTo' => 'email',
			],
		];
		
		return $status === Password::RESET_LINK_SENT
			? apiResponse()->json($data)
			: apiResponse()->error($message);
	}
}
