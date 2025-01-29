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

enum UserType: int
{
	use EnumToArray;
	
	case PROFESSIONAL = 1;
	case INDIVIDUAL = 2;
	
	public function label(): string
	{
		return match ($this) {
			self::PROFESSIONAL => trans('enum.user_professional'),
			self::INDIVIDUAL => trans('enum.user_individual'),
		};
	}
}
