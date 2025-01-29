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

use Illuminate\Support\Facades\DB;

trait Having
{
	protected function applyHaving(): void
	{
		if (!(isset($this->posts) && isset($this->having))) {
			return;
		}
		
		// Get valid columns name
		$this->having = collect($this->having)->map(function ($value, $key) {
			if (str_contains($value, '.')) {
				$value = DB::getTablePrefix() . $value;
			}
			
			return $value;
		})->toArray();
		
		// Set HAVING
		$having = '';
		if (is_array($this->having) && count($this->having) > 0) {
			foreach ($this->having as $key => $value) {
				if (trim($value) == '') {
					continue;
				}
				
				if ($having == '') {
					$having .= $value;
				} else {
					$having .= ' AND ' . $value;
				}
			}
		}
		
		if (!empty($having)) {
			$this->posts->havingRaw($having);
		}
	}
}
