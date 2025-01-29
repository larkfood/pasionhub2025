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

namespace App\Observers;

use App\Models\MetaTag;

class MetaTagObserver
{
	/**
	 * Listen to the Entry saved event.
	 *
	 * @param MetaTag $metaTag
	 * @return void
	 */
	public function saved(MetaTag $metaTag)
	{
		// Removing Entries from the Cache
		$this->clearCache($metaTag);
	}
	
	/**
	 * Listen to the Entry deleted event.
	 *
	 * @param MetaTag $metaTag
	 * @return void
	 */
	public function deleted(MetaTag $metaTag)
	{
		// Removing Entries from the Cache
		$this->clearCache($metaTag);
	}
	
	/**
	 * Removing the Entity's Entries from the Cache
	 *
	 * @param $metaTag
	 * @return void
	 */
	private function clearCache($metaTag): void
	{
		try {
			cache()->flush();
		} catch (\Exception $e) {}
	}
}
