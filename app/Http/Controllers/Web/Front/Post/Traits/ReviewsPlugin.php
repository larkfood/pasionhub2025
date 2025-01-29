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

namespace App\Http\Controllers\Web\Front\Post\Traits;

trait ReviewsPlugin
{
	private string $postHelperClass = '\extras\plugins\reviews\app\Helpers\Post';
	
	/**
	 * @param $postId
	 * @return array
	 */
	public function getReviews($postId): array
	{
		if (config('plugins.reviews.installed')) {
			if (class_exists($this->postHelperClass)) {
				return $this->postHelperClass::getReviews($postId);
			}
		}
		
		return [];
	}
}
