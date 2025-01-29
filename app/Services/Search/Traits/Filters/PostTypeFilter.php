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

namespace App\Services\Search\Traits\Filters;

use App\Enums\PostType;

trait PostTypeFilter
{
	protected function applyPostTypeFilter(): void
	{
		if (config('settings.listing_form.show_listing_type') != '1') {
			return;
		}
		
		if (!isset($this->posts)) {
			return;
		}
		
		$postTypeId = request()->input('type');
		$postTypeId = is_numeric($postTypeId) ? (int)$postTypeId : null;
		
		if (empty($postTypeId)) {
			return;
		}
		
		if (!$this->checkIfPostTypeExists($postTypeId)) {
			abort(404, t('post_type_not_found'));
		}
		
		$this->posts->where('post_type_id', $postTypeId);
	}
	
	/**
	 * Check if PostType exists
	 *
	 * @param $postTypeId
	 * @return bool
	 */
	private function checkIfPostTypeExists($postTypeId): bool
	{
		if (empty($postTypeId)) return false;
		
		return !empty(PostType::find($postTypeId));
	}
}
