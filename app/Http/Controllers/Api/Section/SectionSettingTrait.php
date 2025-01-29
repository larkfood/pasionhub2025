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

namespace App\Http\Controllers\Api\Section;

trait SectionSettingTrait
{
	/**
	 * @param array|null $value
	 * @return array|null
	 */
	protected function searchFormSettings(?array $value = []): ?array
	{
		// Load Country's Background Image
		$countryBackgroundImage = config('country.background_image_path');
		if (isset($this->disk)) {
			if (!empty($countryBackgroundImage) && $this->disk->exists($countryBackgroundImage)) {
				$value['background_image_path'] = $countryBackgroundImage;
			}
		}
		
		$appLocale = config('app.locale');
		
		// Title: Count Posts & Users
		if (!empty($value['title_' . $appLocale])) {
			$title = $value['title_' . $appLocale];
			$title = replaceGlobalPatterns($title);
			
			$value['title_' . $appLocale] = $title;
		}
		
		// SubTitle: Count Posts & Users
		if (!empty($value['sub_title_' . $appLocale])) {
			$subTitle = $value['sub_title_' . $appLocale];
			$subTitle = replaceGlobalPatterns($subTitle);
			
			$value['sub_title_' . $appLocale] = $subTitle;
		}
		
		return $value;
	}
}
