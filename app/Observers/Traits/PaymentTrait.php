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

namespace App\Observers\Traits;

use App\Models\Language;
use App\Models\Scopes\ActiveScope;

trait PaymentTrait
{
	/**
	 * Clear caches related to the promotion payments
	 *
	 * @param $payment
	 * @return void
	 */
	private function clearCacheRelatedToPromotionPayment($payment): void
	{
		$isPromoting = (str_ends_with($payment->payable_type, 'Post'));
		
		if (!$isPromoting) {
			return;
		}
		
		if (empty($payment->payable)) {
			return;
		}
		
		try {
			$post = $payment->payable;
			
			cache()->forget($post->country_code . '.sitemaps.posts.xml');
			
			cache()->forget($post->country_code . '.home.getPosts.premium');
			cache()->forget($post->country_code . '.home.getPosts.latest');
			
			cache()->forget('post.withoutGlobalScopes.with.city.pictures.' . $post->id);
			cache()->forget('post.with.city.pictures.' . $post->id);
			
			// Need to be caught (Independently)
			$languages = Language::query()->withoutGlobalScopes([ActiveScope::class])->get(['code']);
			
			if ($languages->count() > 0) {
				foreach ($languages as $language) {
					cache()->forget('post.withoutGlobalScopes.with.city.pictures.' . $post->id . '.' . $language->code);
					cache()->forget('post.with.city.pictures.' . $post->id . '.' . $language->code);
				}
			}
			
			cache()->forget('posts.similar.category.' . $post->category_id . '.post.' . $post->id);
			cache()->forget('posts.similar.city.' . $post->city_id . '.post.' . $post->id);
		} catch (\Throwable $e) {
		}
	}
	
	/**
	 * Clear caches related to the subscription payments
	 *
	 * @param $payment
	 * @return void
	 */
	private function clearCacheRelatedToSubscriptionPayment($payment): void
	{
		//...
	}
}
