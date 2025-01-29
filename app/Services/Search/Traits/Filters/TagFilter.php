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

trait TagFilter
{
	protected function applyTagFilter(): void
	{
		if (!isset($this->posts)) {
			return;
		}
		
		$tag = request()->input('tag');
		$tag = is_string($tag) ? $tag : null;
		
		if (empty(trim($tag))) {
			return;
		}
		
		$tag = rawurldecode($tag);
		$tag = mb_strtolower($tag);
		
		$this->posts->whereRaw('FIND_IN_SET(?, LOWER(tags)) > 0', [$tag]);
	}
}
