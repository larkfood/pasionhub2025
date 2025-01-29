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

use App\Helpers\Date;
use App\Http\Controllers\Api\Post\Update\MultiStepsForm;
use App\Http\Controllers\Api\Post\Update\SingleStepForm;
use App\Http\Requests\Front\PostRequest\LimitationCompliance;
use App\Http\Resources\PostResource;
use App\Models\Package;
use App\Models\Post;
use App\Notifications\PostArchived;
use App\Notifications\PostRepublished;

trait UpdateTrait
{
	use MultiStepsForm;
	use SingleStepForm;
	
	/**
	 * Archive a listing
	 *
	 * Put a listing offline
	 *
	 * @authenticated
	 * @header Authorization Bearer {YOUR_AUTH_TOKEN}
	 *
	 * @urlParam id int required The post/listing's ID.
	 *
	 * @param $id
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function offline($id): \Illuminate\Http\JsonResponse
	{
		$authUser = auth('sanctum')->user();
		if (empty($authUser)) {
			return apiResponse()->unauthorized();
		}
		
		$post = Post::where('user_id', $authUser->getAuthIdentifier())->where('id', $id)->first();
		
		if (empty($post)) {
			return apiResponse()->notFound(t('post_not_found'));
		}
		
		if (!empty($post->archived_at)) {
			return apiResponse()->error(t('The listing is already offline'));
		}
		
		$post->archived_at = now();
		$post->archived_manually_at = now();
		$post->save();
		
		if (!empty($post->archived_at)) {
			$archivedPostsExpiration = (int)config('settings.cron.manually_archived_listings_expiration', 180);
			
			// Send Confirmation Email or SMS
			try {
				$post->notify(new PostArchived($post, $archivedPostsExpiration));
			} catch (\Throwable $e) {
				return apiResponse()->error(getExceptionMessage($e));
			}
			
			// Get delete date
			$willBeDeletedAt = $post->archived_at->addDays($archivedPostsExpiration);
			$willBeDeletedAtFormatted = Date::format($willBeDeletedAt);
			
			$message = t('offline_putting_message', [
				'postTitle'       => $post->title,
				'willBeDeletedAt' => $willBeDeletedAtFormatted,
				'dateDel'         => $willBeDeletedAtFormatted, // @note: need to be removed
			]);
			
			$data = [
				'success' => true,
				'message' => $message,
				'result'  => new PostResource($post),
			];
			
			return apiResponse()->json($data);
		} else {
			return apiResponse()->error(t('The putting offline has failed'));
		}
	}
	
	/**
	 * Repost a listing
	 *
	 * Repost a listing by un-archiving it.
	 *
	 * @authenticated
	 * @header Authorization Bearer {YOUR_AUTH_TOKEN}
	 *
	 * @urlParam id int required The post/listing's ID.
	 *
	 * @param $id
	 * @param \App\Http\Requests\Front\PostRequest\LimitationCompliance $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function repost($id, LimitationCompliance $request): \Illuminate\Http\JsonResponse
	{
		$authUser = auth('sanctum')->user();
		if (empty($authUser)) {
			return apiResponse()->unauthorized();
		}
		
		$post = Post::where('user_id', $authUser->getAuthIdentifier())->where('id', $id)->first();
		
		if (empty($post)) {
			return apiResponse()->notFound(t('post_not_found'));
		}
		
		if (empty($post->archived_at)) {
			return apiResponse()->error(t('The listing is already online'));
		}
		
		$today = now(Date::getAppTimeZone());
		
		// Try to use the user's possible subscription
		$authUser->loadMissing('payment');
		if (!empty($authUser->payment)) {
			$post->payment_id = $authUser->payment->id ?? null;
		}
		$post->archived_at = null;
		$post->archived_manually_at = null;
		$post->deletion_mail_sent_at = null;
		$post->created_at = $today;
		
		// If the "Allow listings to be reviewed by Admins" option is activated,
		// and the listing is not linked to a valid payment,
		// and all activated packages have price > 0, then
		// - Un-approve (un-reviewed) the listing (using the "reviewed" column)
		// - Update the "updated_at" date column  to now
		if (config('settings.listing_form.listings_review_activation')) {
			if (empty($post->payment)) {
				$packagesForFree = Package::query()->where('price', 0);
				if ($packagesForFree->count() <= 0) {
					$post->reviewed_at = null;
				}
			}
		}
		
		// Save the listing
		$post->save();
		
		if (empty($post->archived_at)) {
			// Send Confirmation Email or SMS
			try {
				$post->notify(new PostRepublished($post));
			} catch (\Throwable $e) {
				return apiResponse()->error(getExceptionMessage($e));
			}
			
			$data = [
				'success' => true,
				'message' => t('the_repost_has_done_successfully'),
				'result'  => new PostResource($post),
			];
			
			return apiResponse()->json($data);
		} else {
			return apiResponse()->error(t('the_repost_has_failed'));
		}
	}
}
