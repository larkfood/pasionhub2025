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
use App\Models\Section;

class SectionObserver
{
	/**
	 * Listen to the Entry updating event.
	 *
	 * @param \App\Models\Section $section
	 * @return void
	 */
	public function updating(Section $section)
	{
		if (isset($section->key) && isset($section->value)) {
			// Get the original object values
			$original = $section->getOriginal();
			
			// Storage Disk Init.
			$disk = StorageDisk::getDisk();
			
			if (is_array($original) && array_key_exists('value', $original)) {
				$original['value'] = jsonToArray($original['value']);
				
				// Remove old background_image_path from disk
				if (array_key_exists('background_image_path', $section->value)) {
					if (
						is_array($original['value'])
						&& !empty($original['value']['background_image_path'])
						&& $section->value['background_image_path'] != $original['value']['background_image_path']
						&& !str_contains($original['value']['background_image_path'], config('larapen.media.picture'))
						&& $disk->exists($original['value']['background_image_path'])
					) {
						$disk->delete($original['value']['background_image_path']);
					}
				}
				
				// Active
				// See the "app/Http/Controllers/Admin/InlineRequestController.php" file for complete operation
				if (array_key_exists('active', $section->value)) {
					$section->active = $section->value['active'];
				}
			}
		}
	}
	
	/**
	 * Listen to the Entry saved event.
	 *
	 * @param \App\Models\Section $section
	 * @return void
	 */
	public function updated(Section $section)
	{
		//...
	}
	
	/**
	 * Listen to the Entry saved event.
	 *
	 * @param \App\Models\Section $section
	 * @return void
	 */
	public function saved(Section $section)
	{
		// Removing Entries from the Cache
		$this->clearCache($section);
	}
	
	/**
	 * Listen to the Entry deleted event.
	 *
	 * @param \App\Models\Section $section
	 * @return void
	 */
	public function deleted(Section $section)
	{
		// Removing Entries from the Cache
		$this->clearCache($section);
	}
	
	/**
	 * Removing the Entity's Entries from the Cache
	 *
	 * @param $section
	 * @return void
	 */
	private function clearCache($section): void
	{
		try {
			cache()->flush();
		} catch (\Exception $e) {}
	}
}
