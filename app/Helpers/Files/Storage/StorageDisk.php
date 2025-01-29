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

namespace App\Helpers\Files\Storage;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

class StorageDisk
{
	/**
	 * Get the default disk name
	 *
	 * @return string|null
	 */
	public static function getDiskName(): ?string
	{
		$defaultDisk = config('filesystems.default', 'public');
		
		// $defaultDisk = config('filesystems.cloud'); // Only for tests purpose!
		
		return getAsStringOrNull($defaultDisk);
	}
	
	/**
	 * Get the default disk resources
	 *
	 * @param string|null $name
	 * @return \Illuminate\Contracts\Filesystem\Filesystem
	 */
	public static function getDisk(string $name = null): Filesystem
	{
		$defaultDisk = !is_null($name) ? $name : self::getDiskName();
		
		return Storage::disk($defaultDisk);
	}
}
