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

namespace App\Http\Controllers\Web\Front\Traits;

use Illuminate\Support\Facades\File;

trait RobotsTxtTrait
{
	/**
	 * Check & Create the robots.txt file if it doesn't exist
	 */
	public function checkRobotsTxtFile(): void
	{
		// Get the robots.txt file path
		$robotsFile = public_path('robots.txt');
		
		// Generate the robots.txt (If it does not exist)
		if (!File::exists($robotsFile)) {
			$robotsTxt = '';
			
			// Custom robots.txt content
			$robotsTxtArr = preg_split('/\r\n|\r|\n/', config('settings.seo.robots_txt', ''));
			if (!empty($robotsTxtArr)) {
				foreach ($robotsTxtArr as $key => $value) {
					$robotsTxt .= trim($value) . "\n";
				}
			}
			
			if (config('settings.seo.robots_txt_sm_indexes')) {
				$robotsTxt .= "\n";
				$robotsTxt .= getSitemapsIndexes();
			}
			
			// Create the robots.txt file
			if (File::isWritable(dirname($robotsFile))) {
				File::put($robotsFile, $robotsTxt);
			}
		}
	}
}
