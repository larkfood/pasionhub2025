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

namespace App\Http\Controllers\Web\Setup\Update\Traits;

use App\Services\Lang\LangManager;
use App\Models\Language;

trait LanguageTrait
{
	/**
	 * (Try to) Fill the missing lines in all languages files
	 */
	private function syncLanguageFilesLines()
	{
		// Don't sync. languages files lines for dev environment
		if (isDevEnv(url()->current())) {
			return;
		}
		
		// Get the current Default Language
		$defaultLang = Language::where('default', 1)->first();
		if (empty($defaultLang)) {
			return;
		}
		
		// Init. the language manager
		$manager = new LangManager();
		
		// SYNC. THE LANGUAGE FILES LINES
		// Get all the others languages (from DB)
		$languages = Language::where('code', '!=', $defaultLang->code)->get();
		if ($languages->count() > 0) {
			foreach ($languages as $language) {
				$manager->syncLines($defaultLang->code, $language->code);
			}
		}
	}
}
