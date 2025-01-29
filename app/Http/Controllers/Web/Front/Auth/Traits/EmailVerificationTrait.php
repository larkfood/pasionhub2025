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

trait EmailVerificationTrait
{
	/**
	 * Show the ReSend Verification Message Link
	 *
	 * @param $entity
	 * @param $entitySlug
	 * @return bool
	 */
	public function showReSendVerificationEmailLink($entity, $entitySlug): bool
	{
		if (empty($entity) || empty(data_get($entity, 'id')) || empty($entitySlug)) {
			return false;
		}
		
		// Show ReSend Verification Email Link
		if (session()->has('emailVerificationSent')) {
			$url = url($entitySlug . '/' . $entity['id'] . '/verify/resend/email');
			
			$message = t('Resend the verification message to verify your email address');
			$message .= ' <a href="' . $url . '" class="btn btn-sm btn-warning">' . t('Re-send') . '</a>';
			
			flash($message)->warning();
		}
		
		return true;
	}
	
	/**
	 * URL: Re-Send the verification message
	 *
	 * @param $entityId
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function reSendEmailVerification($entityId)
	{
		// Non-admin data resources
		$entitySlug = request()->segment(1);
		
		// Admin data resources
		if (isAdminPanel()) {
			$entitySlug = request()->segment(2);
		}
		
		// Add required data in the request for API
		request()->merge(['entitySlug' => $entitySlug]);
		
		// Call API endpoint
		$endpoint = '/' . $entitySlug . '/' . $entityId . '/verify/resend/email';
		$data = makeApiRequest(method: 'get', uri: $endpoint, data: request()->all());
		
		// Parsing the API response
		$message = data_get($data, 'message', t('unknown_error'));
		
		if (data_get($data, 'isSuccessful')) {
			// Notification Message
			if (data_get($data, 'success')) {
				notification($message, 'success');
			} else {
				notification($message, 'error');
			}
			
			if (!data_get($data, 'extra.emailVerificationSent')) {
				// Remove Notification Trigger
				if (session()->has('emailVerificationSent')) {
					session()->forget('emailVerificationSent');
				}
			}
		} else {
			notification($message, 'error');
		}
		
		return redirect()->back();
	}
}
