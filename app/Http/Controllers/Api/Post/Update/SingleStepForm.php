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

namespace App\Http\Controllers\Api\Post\Update;

use App\Http\Requests\Front\PostRequest;
use App\Http\Resources\PostResource;
use App\Models\City;
use App\Models\Post;
use App\Models\Scopes\ReviewedScope;
use App\Models\Scopes\VerifiedScope;

trait SingleStepForm
{
	/**
	 * @param $postId
	 * @param \App\Http\Requests\Front\PostRequest $request
	 * @return \Illuminate\Http\JsonResponse|mixed
	 */
	public function singleStepFormUpdate($postId, PostRequest $request)
	{
		$authUser = auth('sanctum')->user();
		if (empty($authUser)) {
			return apiResponse()->unauthorized();
		}
		
		$countryCode = $request->input('country_code', config('country.code'));
		
		$post = Post::withoutGlobalScopes([VerifiedScope::class, ReviewedScope::class])
			->inCountry($countryCode)
			->with(['payment'])
			->where('user_id', $authUser->getAuthIdentifier())
			->where('id', $postId)
			->first();
		
		if (empty($post)) {
			return apiResponse()->notFound(t('post_not_found'));
		}
		
		// Get the Post's City
		$city = City::find($request->input('city_id', 0));
		if (empty($city)) {
			return apiResponse()->error(t('city_not_found'));
		}
		
		// Conditions to Verify User's Email or Phone
		$emailVerificationRequired = config('settings.mail.email_verification') == '1'
			&& $request->filled('email')
			&& $request->input('email') != $post->email;
		$phoneVerificationRequired = config('settings.sms.phone_verification') == '1'
			&& $request->filled('phone')
			&& $request->input('phone') != $post->phone;
		
		// Check if the listing sensitive data have been changed
		$hasListingDataUpdated = false;
		if (config('settings.listing_form.listings_review_activation')) {
			$hasListingDataUpdated = (
				md5($post->title) != md5($request->input('title'))
				|| md5($post->description) != md5($request->input('description'))
			);
		}
		
		// Update Post
		$input = $request->only($post->getFillable());
		foreach ($input as $key => $value) {
			$post->{$key} = $value;
		}
		
		// Revoke the listing approval when its sensitive data have
		// been changed, allowing the admin user to approve the changes
		if ($hasListingDataUpdated) {
			$post->reviewed_at = null;
		}
		
		// Checkboxes
		$post->negotiable = $request->input('negotiable');
		$post->phone_hidden = $request->input('phone_hidden');
		
		// Other fields
		$post->lat = $city->latitude;
		$post->lon = $city->longitude;
		
		// Email verification key generation
		if ($emailVerificationRequired) {
			$post->email_token = md5(microtime() . mt_rand());
			$post->email_verified_at = null;
		}
		
		// Phone verification key generation
		if ($phoneVerificationRequired) {
			$post->phone_token = mt_rand(100000, 999999);
			$post->phone_verified_at = null;
		}
		
		// Save
		$post->save();
		
		$data = [
			'success' => true,
			'message' => null,
			'result'  => (new PostResource($post))->toArray($request),
		];
		
		$extra = [];
		
		// Save all pictures
		$extra['pictures'] = [];
		try {
			$extra['pictures'] = $this->singleStepPicturesStore($post->id, $request);
		} catch (\Throwable $e) {
			$data['success'] = false;
			$data['message'] = $e->getMessage();
			
			return apiResponse()->json($data);
		}
		
		// Custom Fields
		$this->fieldsValuesStore($post, $request);
		
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
		$data['message'] = t('your_listing_is_updated');
		
		// Send an Email Verification message
		if ($emailVerificationRequired) {
			$extra['sendEmailVerification'] = $this->sendEmailVerification($post);
			if (
				array_key_exists('success', $extra['sendEmailVerification'])
				&& array_key_exists('message', $extra['sendEmailVerification'])
			) {
				$extra['mail']['success'] = $extra['sendEmailVerification']['success'];
				$extra['mail']['message'] = $extra['sendEmailVerification']['message'];
			}
		}
		
		// Send a Phone Verification message
		if ($phoneVerificationRequired) {
			$extra['sendPhoneVerification'] = $this->sendPhoneVerification($post);
			if (
				array_key_exists('success', $extra['sendPhoneVerification'])
				&& array_key_exists('message', $extra['sendPhoneVerification'])
			) {
				$extra['mail']['success'] = $extra['sendPhoneVerification']['success'];
				$extra['mail']['message'] = $extra['sendPhoneVerification']['message'];
			}
		}
		
		$data['extra'] = $extra;
		
		return apiResponse()->json($data);
	}
}
