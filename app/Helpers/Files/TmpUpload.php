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

namespace App\Helpers\Files;

use App\Helpers\Files\Storage\StorageDisk;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Intervention\Image\Laravel\Facades\Image;

class TmpUpload
{
	/**
	 * @param $tmpUploadDir
	 * @param $file
	 * @return string|null
	 */
	public static function image($tmpUploadDir, $file): ?string
	{
		if (!$file instanceof UploadedFile) {
			return null;
		}
		
		$disk = StorageDisk::getDisk();
		
		try {
			// Get file's original infos
			$origFilename = $file->getClientOriginalName();
			$origExtension = $file->getClientOriginalExtension();
			$origExtension = !empty($origExtension) ? $origExtension : getClientImageFallbackExtension();
			
			// Get the client extension for the original extension
			$extension = getClientImageExtensionFor($origExtension);
			
			// Image quality
			$imageQuality = (int)config('settings.upload.image_quality', 90);
			
			// Image trimming tolerance
			$trimmingTolerance = (int)config('settings.upload.image_trimming_tolerance', 0);
			
			// Image progressively rending
			$isProgressive = (config('settings.upload.image_progressive') == '1');
			
			// Image default dimensions
			$width = (int)config('settings.upload.img_resize_width', 1000);
			$height = (int)config('settings.upload.img_resize_height', 1000);
			
			// Other parameters
			$ratio = config('settings.upload.img_resize_ratio', '1');
			$upsize = config('settings.upload.img_resize_upsize', '0');
			
			// Init. Intervention
			$image = Image::read($file);
			
			// Orient image according to exif data
			if (isExifExtensionEnabled()) {
				$image = $image->orient();
			}
			
			// Image trimming
			if ($trimmingTolerance > 0) {
				$image = $image->trim($trimmingTolerance);
			}
			
			// If the original dimensions are higher than the resize dimensions
			// OR the 'upsize' option is enable, then resize the image
			if ($image->width() > $width || $image->height() > $height || $upsize == '1') {
				// Resize
				if ($ratio != '1' && $upsize != '1') {
					$image = $image->resizeDown($width, $height);
				} else {
					if ($ratio == '1' && $upsize == '1') {
						$image = $image->scale($width, $height);
					} else {
						$image = ($ratio == '1')
							? $image->scaleDown($width, $height)
							: $image->resize($width, $height);
					}
				}
			}
			
			// Encode the Image!
			$encodedImage = $image->encodeByExtension($extension, progressive: $isProgressive, quality: $imageQuality);
			unset($image);
			
			// Generate the filename
			$filename = md5($origFilename . time());
			$filename = $filename . '.' . $extension;
			
			// Get the file path
			$filePath = $tmpUploadDir . '/' . $filename;
			
			// Store the image on disk
			$disk->put($filePath, $encodedImage->toString());
			unset($encodedImage);
			
			// Return the path (to the database later)
			return $filePath;
		} catch (\Throwable $e) {
			abort(500, $e->getMessage());
		}
	}
	
	/**
	 * @param $tmpUploadDir
	 * @param $file
	 * @return string|null
	 */
	public static function file($tmpUploadDir, $file): ?string
	{
		if (!$file instanceof UploadedFile) {
			return null;
		}
		
		$disk = StorageDisk::getDisk();
		
		try {
			// Get file original infos
			$origFilename = $file->getClientOriginalName();
			$origExtension = $file->getClientOriginalExtension();
			
			// Generate a filename
			$filename = md5($origFilename . time()) . '.' . $origExtension;
			
			// Get filepath
			$filePath = $tmpUploadDir . '/' . $filename;
			
			// Store the file on disk
			$disk->put($filePath, File::get($file->getrealpath()));
			
			// Return the path (to the database later)
			return $filePath;
		} catch (\Throwable $e) {
		}
		
		return null;
	}
}
