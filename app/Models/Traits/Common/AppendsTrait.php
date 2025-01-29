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

namespace App\Models\Traits\Common;

use Illuminate\Database\Eloquent\Builder;

trait AppendsTrait
{
	/**
	 * @var bool
	 */
	private static bool $withoutAppends = false;
	
	/**
	 * @var array
	 */
	private static array $withAppends = [];
	
	/*
	|--------------------------------------------------------------------------
	| FUNCTIONS
	|--------------------------------------------------------------------------
	*/
	protected function getArrayableAppends(): array
	{
		if (self::$withoutAppends) {
			return [];
		} else {
			if (!empty(self::$withAppends)) {
				return self::$withAppends;
			}
		}
		
		return parent::getArrayableAppends();
	}
	
	/*
	|--------------------------------------------------------------------------
	| SCOPES
	|--------------------------------------------------------------------------
	*/
	public function scopeWithoutAppends(Builder $builder): Builder
	{
		self::$withoutAppends = true;
		
		return $builder;
	}
	
	public function scopeWithAppends(Builder $builder, array $withAppends = []): Builder
	{
		self::$withAppends = $withAppends;
		
		return $builder;
	}
}
