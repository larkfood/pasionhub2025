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

namespace App\Observers\Traits\Setting;

use App\Helpers\Files\Storage\StorageDisk;
use App\Models\Category;

trait StyleTrait
{
	/**
	 * Updating
	 *
	 * @param $setting
	 * @param $original
	 * @return mixed
	 */
	public function styleUpdating($setting, $original)
	{
		// Storage Disk Init.
		$disk = StorageDisk::getDisk();
		
		$this->removeOldBodyBackgroundImage($setting, $original, $disk);
		
		return $this->applyLogoMaxDimensionsLimit($setting, $original);
	}
	
	/**
	 * Saved
	 *
	 * @param $setting
	 */
	public function styleSaved($setting)
	{
		$this->updateCategoriesPicturesPaths($setting);
	}
	
	/**
	 * Remove old body_background_image from disk
	 *
	 * @param $setting
	 * @param $original
	 * @param $disk
	 */
	private function removeOldBodyBackgroundImage($setting, $original, $disk): void
	{
		if (array_key_exists('body_background_image_path', $setting->value)) {
			if (
				is_array($original['value'])
				&& !empty($original['value']['body_background_image_path'])
				&& $setting->value['body_background_image_path'] != $original['value']['body_background_image_path']
				&& !str_contains($original['value']['body_background_image_path'], config('larapen.media.picture'))
				&& $disk->exists($original['value']['body_background_image_path'])
			) {
				$disk->delete($original['value']['body_background_image_path']);
			}
		}
	}
	
	/**
	 * Apply the logo's maximum dimensions limit
	 *
	 * @param $setting
	 * @param $original
	 * @return mixed
	 */
	private function applyLogoMaxDimensionsLimit($setting, $original)
	{
		if (!isset($setting->value)) {
			return $setting;
		}
		
		$value = $setting->value;
		
		// Logo Max. Dimensions
		$logoMaxWidth = config('larapen.media.resize.namedOptions.logo-max.width', 430);
		$logoMaxHeight = config('larapen.media.resize.namedOptions.logo-max.height', 80);
		if (!empty(config('settings.style.header_height'))) {
			$logoMaxHeight = forceToInt(config('settings.style.header_height'));
			if (empty($logoMaxHeight)) {
				$logoMaxHeight = 80;
			}
		}
		
		// Logo Default Dimensions
		$logoDefaultWidth = config('larapen.media.resize.namedOptions.logo.width', 216);
		$logoDefaultHeight = config('larapen.media.resize.namedOptions.logo.height', 40);
		
		// Logo Dimensions
		$logoWidth = forceToInt($value['logo_width'] ?? $logoDefaultWidth);
		$logoHeight = forceToInt($value['logo_height'] ?? $logoDefaultHeight);
		if (empty($logoWidth)) {
			$logoWidth = $logoDefaultWidth;
		}
		if (empty($logoHeight)) {
			$logoHeight = $logoDefaultHeight;
		}
		if ($logoWidth > $logoMaxWidth) {
			$logoWidth = $logoMaxWidth;
		}
		if ($logoHeight > $logoMaxHeight) {
			$logoHeight = $logoMaxHeight;
		}
		
		$value['logo_width'] = $logoWidth;
		$value['logo_height'] = $logoHeight;
		
		$setting->value = $value;
		
		return $setting;
	}
	
	/**
	 * @param $setting
	 */
	private function updateCategoriesPicturesPaths($setting): void
	{
		// If the Default Front Skin is changed, then update its assets paths (like categories pictures, etc.)
		if (isset($setting->value['skin']) && !empty($setting->value['skin'])) {
			$categories = Category::root()->get();
			if ($categories->count() > 0) {
				foreach ($categories as $category) {
					$canSave = false;
					
					// If the Category contains a skinnable icon,
					// Change it by the selected skin icon.
					if (str_contains($category->image_path, 'app/categories/') && !str_contains($category->image_path, '/custom/')) {
						$pattern = '/app\/categories\/[^\/]+\//ui';
						$replacement = 'app/categories/' . $setting->value['skin'] . '/';
						$picture = preg_replace($pattern, $replacement, $category->image_path);
						if (!empty($picture)) {
							$category->image_path = $picture;
							$canSave = true;
						}
					}
					
					// (Optional)
					// If the Category contains a skinnable default icon,
					// Change it by the selected skin default icon.
					if (str_contains($category->image_path, 'app/default/categories/fa-folder-')) {
						$pattern = '/app\/default\/categories\/fa-folder-[^\.]+\./ui';
						$replacement = 'app/default/categories/fa-folder-' . $setting->value['skin'] . '.';
						$picture = preg_replace($pattern, $replacement, $category->image_path);
						if (!empty($picture)) {
							$category->image_path = $picture;
							$canSave = true;
						}
					}
					
					if ($canSave) {
						$category->save();
					}
				}
			}
		}
	}
}
