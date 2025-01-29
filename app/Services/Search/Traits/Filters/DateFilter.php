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

use Illuminate\Support\Facades\DB;

trait DateFilter
{
	protected function applyDateFilter(): void
	{
		if (!(isset($this->posts) && isset($this->postsTable))) {
			return;
		}
		
		$postedDate = request()->input('postedDate');
		$postedDate = (is_numeric($postedDate) || is_string($postedDate)) ? $postedDate : null;
		
		if (empty($postedDate)) {
			return;
		}
		
		$table = DB::getTablePrefix() . $this->postsTable;
		
		$this->posts->whereRaw($table . '.created_at BETWEEN DATE_SUB(NOW(), INTERVAL ? DAY) AND NOW()', [$postedDate]);
	}
}
