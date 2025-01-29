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

namespace App\Observers\Traits\Setting;

use App\Models\Post;

trait ListingFormTrait
{
	/**
	 * Updating
	 *
	 * @param $setting
	 * @param $original
	 * @return void
	 */
	public function listingFormUpdating($setting, $original)
	{
		$this->autoReviewedExistingPostsIfApprobationIsEnabled($setting);
	}
	
	/**
	 * Auto approve all the existing listings,
	 * If the Posts Approbation feature is enabled
	 *
	 * @param $setting
	 * @return void
	 */
	private function autoReviewedExistingPostsIfApprobationIsEnabled($setting): void
	{
		// Enable Posts Approbation by User Admin (Post Review)
		if (array_key_exists('listings_review_activation', $setting->value)) {
			// If Listing Approbation is enabled,
			// then set the reviewed field to "true" for all the existing Posts
			if ((int)$setting->value['listings_review_activation'] == 1) {
				Post::whereNull('reviewed_at')->update(['reviewed_at' => now()]);
			}
		}
	}
}
