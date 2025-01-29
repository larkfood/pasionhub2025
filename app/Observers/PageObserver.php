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

use App\Helpers\Files\Storage\StorageDisk;
use App\Models\Page;

class PageObserver
{
	/**
	 * Listen to the Entry deleting event.
	 *
	 * @param Page $page
	 * @return void
	 */
	public function deleting($page)
	{
		// Storage Disk Init.
		$disk = StorageDisk::getDisk();
		
		// Delete the page picture
		if (!empty($page->image_path)) {
			if ($disk->exists($page->image_path)) {
				$disk->delete($page->image_path);
			}
		}
	}
	
	/**
	 * Listen to the Entry saved event.
	 *
	 * @param Page $page
	 * @return void
	 */
	public function saved(Page $page)
	{
		// Removing Entries from the Cache
		$this->clearCache($page);
	}
	
	/**
	 * Listen to the Entry deleted event.
	 *
	 * @param Page $page
	 * @return void
	 */
	public function deleted(Page $page)
	{
		// Removing Entries from the Cache
		$this->clearCache($page);
	}
	
	/**
	 * Removing the Entity's Entries from the Cache
	 *
	 * @param $page
	 * @return void
	 */
	private function clearCache($page): void
	{
		try {
			cache()->flush();
		} catch (\Exception $e) {
		}
	}
}
