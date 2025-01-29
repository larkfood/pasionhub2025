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

namespace App\Listeners;

use App\Events\PostWasVisited;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdatePostVisits
{
	/**
	 * Create the event listener.
	 */
	public function __construct()
	{
		//
	}
	
	/**
	 * Handle the event.
	 *
	 * @param \App\Events\PostWasVisited $event
	 * @return bool
	 */
	public function handle(PostWasVisited $event)
	{
		if (!isFromApi()) return false;
		
		$post = $event->post;
		$postId = $post->id ?? null;
		$postUserId = $post->user_id ?? null;
		
		// Don't count the author's self-visits
		$guard = 'sanctum';
		if (auth($guard)->check()) {
			if (auth($guard)->user()->getAuthIdentifier() == $postUserId) {
				return false;
			}
		}
		
		$alreadyVisited = (
			request()->hasHeader('X-VISITED-BY-SAME-SESSION')
			&& request()->header('X-VISITED-BY-SAME-SESSION') == $postId
		);
		if (!$alreadyVisited) {
			$this->incrementVisits($post);
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * @param $post
	 * @return void
	 */
	private function incrementVisits($post): void
	{
		try {
			// Remove|unset the 'pictures' attribute (added to limit pictures number related to a selected package)
			$attributes = $post->getAttributes();
			if (isset($attributes['pictures'])) {
				unset($attributes['pictures']);
				$post->setRawAttributes($attributes, true);
			}
			
			// Increment the listing's visit count
			$post->visits = $post->visits + 1;
			$post->save();
		} catch (\Throwable $e) {
		}
	}
}
