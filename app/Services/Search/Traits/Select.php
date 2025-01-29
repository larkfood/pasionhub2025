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

namespace App\Services\Search\Traits;

use App\Helpers\Num;
use Illuminate\Support\Facades\DB;

trait Select
{
	protected function setSelect(): void
	{
		if (!(isset($this->posts) && isset($this->postsTable))) {
			return;
		}
		
		// Default Select Columns
		$select = [
			$this->postsTable . '.id',
			'country_code',
			'user_id',
			'category_id',
			'post_type_id',
			'title',
			$this->postsTable . '.price',
			'city_id',
			'featured',
			$this->postsTable . '.created_at',
			'email_verified_at',
			'phone_verified_at',
			'reviewed_at',
		];
		if (isFromApi() && !doesRequestIsFromWebClient()) {
			$select[] = $this->postsTable . '.description';
			$select[] = 'contact_name';
			$select[] = $this->postsTable . '.auth_field';
			$select[] = $this->postsTable . '.phone';
			$select[] = $this->postsTable . '.email';
		}
		if (config('settings.listings_list.show_listings_tags')) {
			$select[] = 'tags';
		}
		if (config('plugins.reviews.installed')) {
			$select[] = 'rating_cache';
			$select[] = 'rating_count';
		}
		
		// Default GroupBy Columns
		$groupBy = [$this->postsTable . '.id'];
		
		// Merge Columns
		$this->select = array_merge($this->select, $select);
		$this->groupBy = array_merge($this->groupBy, $groupBy);
		
		// Add the Select Columns
		if (!empty($this->select)) {
			foreach ($this->select as $column) {
				$this->posts->addSelect($column);
			}
		}
		
		// If the MySQL strict mode is activated, ...
		// Append all the non-calculated fields available in the 'SELECT' in 'GROUP BY' to prevent error related to 'only_full_group_by'
		if (self::$dbModeStrict) {
			$this->groupBy = $this->select;
		}
		
		// Get the currency rate
		$currencyRate = Num::getCurrencyRate(config('selectedCurrency'));
		
		// Price conversion (For the Currency Exchange plugin)
		$this->posts->addSelect(DB::raw('(' . DB::getTablePrefix() . $this->postsTable . '.price * ?) AS calculatedPrice'));
		$this->posts->addBinding($currencyRate, 'select');
	}
}
