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

namespace App\Http\Controllers\Api\Post;

use App\Http\Controllers\Api\Post\Store\AutoRegistrationTrait;
use App\Http\Controllers\Api\Post\Store\StoreFieldValueTrait;
use App\Http\Requests\Front\PostRequest;
use App\Http\Resources\PostResource;
use App\Models\City;
use App\Models\Post;

trait StoreTrait
{
	use AutoRegistrationTrait;
	use StoreFieldValueTrait;
	
	/**
	 * @param \App\Http\Requests\Front\PostRequest $request
	 * @return \Illuminate\Http\JsonResponse|mixed
	 */
	public function storePost(PostRequest $request)
	{
		// Get the Post's City
		$city = City::find($request->input('city_id', 0));
		if (empty($city)) {
			return apiResponse()->error(t('city_not_found'));
		}
		
		$authUser = auth('sanctum')->user();
		
		// Conditions to Verify User's Email or Phone
		if (!empty($authUser)) {
			$emailVerificationRequired = (
				config('settings.mail.email_verification') == '1'
				&& $request->filled('email')
				&& $request->input('email') != $authUser->email
			);
			$phoneVerificationRequired = (
				config('settings.sms.phone_verification') == '1'
				&& $request->filled('phone')
				&& $request->input('phone') != $authUser->phone
			);
		} else {
			$emailVerificationRequired = config('settings.mail.email_verification') == '1' && $request->filled('email');
			$phoneVerificationRequired = config('settings.sms.phone_verification') == '1' && $request->filled('phone');
		}
		
		// New Post
		$post = new Post();
		$input = $request->only($post->getFillable());
		foreach ($input as $key => $value) {
			$post->{$key} = $value;
		}
		
		if (!empty($authUser)) {
			// Try to use the user's possible subscription
			$authUser->loadMissing('payment');
			if (!empty($authUser->payment)) {
				$post->payment_id = $authUser->payment->id ?? null;
			}
		}
		
		// Checkboxes
		$post->negotiable = $request->input('negotiable');
		$post->phone_hidden = $request->input('phone_hidden');
		
		// Other fields
		$post->country_code = $request->input('country_code', config('country.code'));
		$post->user_id = !empty($authUser->id) ? $authUser->id : null;
		$post->lat = $city->latitude;
		$post->lon = $city->longitude;
		$post->tmp_token = md5(microtime() . mt_rand(100000, 999999));
		$post->reviewed_at = null;
		
		if ($request->anyFilled(['email', 'phone'])) {
			$post->email_verified_at = now();
			$post->phone_verified_at = now();
			
			// Email verification key generation
			if ($emailVerificationRequired) {
				$post->email_token = md5(microtime() . mt_rand());
				$post->email_verified_at = null;
			}
			
			// Mobile activation key generation
			if ($phoneVerificationRequired) {
				$post->phone_token = mt_rand(100000, 999999);
				$post->phone_verified_at = null;
			}
		}
		
		if (
			config('settings.listing_form.listings_review_activation') != '1'
			&& !$emailVerificationRequired
			&& !$phoneVerificationRequired
		) {
			$post->reviewed_at = now();
		}
		
		// Save
		try {
			
			$post->save();
			
		} catch (\Throwable $e) {
			return apiResponse()->error($e->getMessage());
		}
		
		// Get the API response data
		$data = [
			'success' => true,
			'message' => $this->apiMsg['payable']['success'],
			'result'  => (new PostResource($post))->toArray($request),
		];
		
		$extra = [];
		
		// Save all pictures
		$extra['pictures'] = [];
		try {
			$extra['pictures'] = $this->singleStepPicturesStore($post->id, $request);
		} catch (\Throwable $e) {
			return apiResponse()->error($e->getMessage());
		}
		
		// Custom Fields
		$this->fieldsValuesStore($post, $request);
		
		// Auto-Register the Author
		$extra['autoRegisteredUser'] = $this->autoRegister($post, $request);
		
		if (!doesRequestIsFromWebClient()) {
			// ===| Make|send payment (if needed) |==============
			
			$payResult = $this->isPaymentRequested($request, $post);
			if (data_get($payResult, 'success')) {
				return $this->sendPayment($request, $post);
			}
			if (data_get($payResult, 'failure')) {
				return apiResponse()->error(data_get($payResult, 'message'));
			}
			
			// ===| If no payment is made (continue) |===========
		}
		
		$data['success'] = true;
		$data['message'] = $this->apiMsg['payable']['success'];
		
		// Send Verification Link or Code
		// Email
		if ($emailVerificationRequired) {
			// Send Verification Link by Email
			$extra['sendEmailVerification'] = $this->sendEmailVerification($post);
			if (
				array_key_exists('success', $extra['sendEmailVerification'])
				&& array_key_exists('message', $extra['sendEmailVerification'])
			) {
				$extra['mail']['success'] = $extra['sendEmailVerification']['success'];
				$extra['mail']['message'] = $extra['sendEmailVerification']['message'];
			}
		}
		
		// Phone
		if ($phoneVerificationRequired) {
			// Send Verification Code by SMS
			$extra['sendPhoneVerification'] = $this->sendPhoneVerification($post);
			if (
				array_key_exists('success', $extra['sendPhoneVerification'])
				&& array_key_exists('message', $extra['sendPhoneVerification'])
			) {
				$extra['mail']['success'] = $extra['sendPhoneVerification']['success'];
				$extra['mail']['message'] = $extra['sendPhoneVerification']['message'];
			}
		}
		
		// Once Verification Notification is sent (containing Link or Code),
		// Send Confirmation Notification, when user clicks on the Verification Link or enters the Verification Code.
		// Done in the "app/Observers/PostObserver.php" file.
		
		$data['extra'] = $extra;
		
		return apiResponse()->json($data);
	}
}
