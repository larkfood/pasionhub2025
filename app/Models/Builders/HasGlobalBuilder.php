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

namespace App\Models\Builders;

use App\Models\Builders\Classes\GlobalBuilder;

trait HasGlobalBuilder
{
	use HasEnumFields;
	
	/**
	 * Get a new query builder instance for the connection
	 * that extend the Laravel eloquent core builder
	 *
	 * @param $query
	 * @return \App\Models\Builders\Classes\GlobalBuilder
	 */
	public function newEloquentBuilder($query)
	{
		return new GlobalBuilder($query);
	}
}
