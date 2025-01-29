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

namespace App\Exceptions\Handler\Traits;

use Illuminate\Support\Facades\View;

trait ExceptionTrait
{
	/**
	 * Get theme error view (dot-separated) path
	 *
	 * @param string|null $viewName
	 * @return string|null
	 */
	protected function getThemeErrorViewPath(?string $viewName = null): ?string
	{
		// Set default theme errors views directory in the possible views base directories array
		$viewPathDirs = [
			'front.errors'
		];
		
		/*
		 * Create a custom view namespace to ensure Laravel uses the theme's error directory instead
		 * of the default "resources/views/errors" directory. This allows us to reference error views with
		 * "theme::errors." rather than "errors.", avoiding potential confusion with the default view hint for "resources/views/errors".
		 *
		 * Next, prepend the theme's error views directory to the $viewPathDirs array.
		 */
		$themePath = base_path('extras/themes/customized/views');
		if (is_dir($themePath)) {
			View::addNamespace('customized', $themePath);
			array_unshift($viewPathDirs, 'customized::errors');
		}
		
		// Use the first view found
		$viewPath = null;
		foreach ($viewPathDirs as $viewPathDir) {
			$tmpViewPath = "{$viewPathDir}.{$viewName}";
			if (view()->exists($tmpViewPath)) {
				$viewPath = $tmpViewPath;
				break;
			}
		}
		
		return $viewPath;
	}
}
