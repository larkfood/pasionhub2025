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

namespace App\Enums;

enum PostType: int
{
	use EnumToArray {
		all as traitAll;
	}
	
	case INDIVIDUAL = 1;
	case PROFESSIONAL = 2;
	
	public function label(): string
	{
		return match ($this) {
			self::INDIVIDUAL => trans('enum.individual'),
			self::PROFESSIONAL => trans('enum.professional'),
		};
	}
	
	/**
	 * @return array
	 */
	public static function all(): array
	{
		if (!app()->runningInConsole()) {
			if (!config('settings.listing_form.show_listing_type')) {
				return [];
			}
		}
		
		return self::traitAll();
	}
}
