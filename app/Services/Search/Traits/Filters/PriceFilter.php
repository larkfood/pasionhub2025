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

trait PriceFilter
{
	protected function applyPriceFilter(): void
	{
		// The 'calculatedPrice' is a calculated column, so HAVING clause is required
		if (!isset($this->having)) {
			return;
		}
		
		$minPrice = request()->filled('minPrice') ? request()->input('minPrice') : null;
		$maxPrice = request()->filled('maxPrice') ? request()->input('maxPrice') : null;
		
		$minPrice = is_numeric($minPrice) ? $minPrice : null;
		$maxPrice = is_numeric($maxPrice) ? $maxPrice : null;
		
		if (!is_null($minPrice) && !is_null($maxPrice)) {
			if ($maxPrice > $minPrice) {
				$this->having[] = 'calculatedPrice >= ' . $minPrice;
				$this->having[] = 'calculatedPrice <= ' . $maxPrice;
			}
		} else {
			if (!is_null($minPrice)) {
				$this->having[] = 'calculatedPrice >= ' . $minPrice;
			}
			if (!is_null($maxPrice)) {
				$this->having[] = 'calculatedPrice <= ' . $maxPrice;
			}
		}
	}
}
