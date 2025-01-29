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
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Http\File as HttpFile;

class Upload
{
	/**
	 * @param string|null $destPath
	 * @param $file
	 * @param string|array|null $param
	 * @param bool $withWatermark
	 * @return string|null
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	public static function image(?string $destPath, $file, string|array|null $param = null, bool $withWatermark = false): ?string
	{
		if (empty($file)) {
			return null;
		}
		
		// Case #1: No file is uploaded
		if (!$file instanceof UploadedFile) {
			if (!is_string($file)) {
				return null;
			}
			
			if (str_contains($file, $destPath) && !str_starts_with($file, $destPath)) {
				$file = $destPath . last(explode($destPath, $file));
			}
			
			if (str_starts_with($file, url('storage'))) {
				$file = ltrim(str_replace(url('storage'), '', $file), '/');
			}
			
			// Never save in DB the default fallback picture path
			if (str_contains($file, config('larapen.media.picture'))) {
				$file = null;
			}
			
			return $file;
		}
		
		// Case #2: File needs to be uploaded
		$disk = StorageDisk::getDisk();
		
		try {
			// Image quality
			$imageQuality = (int)config('settings.upload.image_quality', 90);
			
			// Image trimming tolerance
			$trimmingTolerance = (int)config('settings.upload.image_trimming_tolerance', 0);
			
			// Image progressively rending
			$isProgressive = (config('settings.upload.image_progressive') == '1');
			
			// Case #2: File is uploaded
			// Get file's original infos
			$origFilename = $file->getClientOriginalName();
			$origExtension = $file->getClientOriginalExtension();
			$origExtension = !empty($origExtension) ? $origExtension : getClientImageFallbackExtension();
			
			// Get the client extension for the original extension
			$extension = getClientImageExtensionFor($origExtension);
			
			// Param(s)
			if (is_string($param) || empty($param)) {
				$type = !empty($param) ? $param . '_' : '';
				
				$width = (int)config('settings.upload.img_resize_' . $type . 'width', 1000);
				$height = (int)config('settings.upload.img_resize_' . $type . 'height', 1000);
				$ratio = config('settings.upload.img_resize_' . $type . 'ratio', '1');
				$upsize = config('settings.upload.img_resize_' . $type . 'upsize', '0');
				$prefix = null;
			} else {
				$imageQuality = $param['quality'] ?? $imageQuality;
				$width = $param['width'] ?? 1000;
				$height = $param['height'] ?? 1000;
				$ratio = $param['ratio'] ?? '1';
				$upsize = $param['upsize'] ?? '0';
				$prefix = $param['filename'] ?? null;
			}
			
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
			
			// Is it with Watermark?
			if ($withWatermark) {
				// Check and load Watermark plugin
				$plugin = load_installed_plugin('watermark');
				if (!empty($plugin)) {
					/**
					 * @var \Intervention\Image\Interfaces\ImageInterface|null $image
					 */
					$image = call_user_func($plugin->class . '::apply', $image);
					if (is_null($image)) return null;
				}
			}
			
			// Encode the Image!
			$encodedImage = $image->encodeByExtension($extension, progressive: $isProgressive, quality: $imageQuality);
			unset($image);
			
			// Generate the filename
			$filename = md5($origFilename . random_int(1, 9999) . time());
			$filename = !empty($prefix) ? uniqid($prefix) : $filename;
			$filename = $filename . '.' . $extension;
			
			// Get the file path
			$filePath = $destPath . '/' . $filename;
			
			// Store the image on disk
			$disk->put($filePath, $encodedImage->toString());
			unset($encodedImage);
			
			// Save this path to the database
			return $filePath;
		} catch (\Throwable $e) {
			return self::showError($e);
		}
	}
	
	/**
	 * @param string|null $destPath
	 * @param $file
	 * @param string|null $diskName
	 * @return string|null
	 */
	public static function file(?string $destPath, $file, ?string $diskName = null): ?string
	{
		if (empty($file)) {
			return null;
		}
		
		if (!$file instanceof UploadedFile) {
			if (!is_string($file)) {
				return null;
			}
			
			if (str_contains($file, $destPath) && !str_starts_with($file, $destPath)) {
				$file = $destPath . last(explode($destPath, $file));
			}
			
			if (str_starts_with($file, url('storage'))) {
				$file = ltrim(str_replace(url('storage'), '', $file), '/');
			}
			
			return $file;
		}
		
		$disk = StorageDisk::getDisk($diskName);
		
		try {
			// Get file's original infos
			$origFilename = $file->getClientOriginalName();
			$origExtension = $file->getClientOriginalExtension();
			
			// Generate a filename
			$filename = md5($origFilename . random_int(1, 9999) . time());
			$filename = $filename . '.' . $origExtension;
			
			// Get filepath
			$filePath = $destPath . '/' . $filename;
			
			// Store the file on disk
			$disk->put($filePath, File::get($file->getrealpath()));
			
			// Return the path (to the database later)
			return $filePath;
		} catch (\Throwable $e) {
			return self::showError($e);
		}
	}
	
	/**
	 * Create an UploadedFile object from base64 file content
	 *
	 * @param string|null $string
	 * @param bool $test
	 * @return false|\Illuminate\Http\UploadedFile
	 */
	public static function fromBase64(?string $string, bool $test = true): bool|UploadedFile
	{
		$hasBase64String = (!empty($string) && str_starts_with($string, 'data:'));
		
		if (!$hasBase64String) {
			return false;
		}
		
		// Get file extension
		$origMimeType = FileSys::getBase64EncodedFileMimeType($string);
		$origExtension = FileSys::getMimeTypeExtension($origMimeType);
		
		// Get file data base64 string
		$base64String = FileSys::extractBase64String($string);
		
		// Decode the base64 string (to get a binary string)
		$decodedData = base64_decode($base64String);
		
		// Save it to temporary dir first
		$tmpFilePath = sys_get_temp_dir() . '/' . Str::uuid()->toString();
		file_put_contents($tmpFilePath, $decodedData);
		
		// This just to help us get file info
		$tmpFile = new HttpFile($tmpFilePath);
		
		$path = $tmpFile->getPathname();
		$origFilename = $tmpFile->getFilename() . '.' . $origExtension;
		$mimeType = $tmpFile->getMimeType();
		$mimeType = !empty($mimeType) ? $mimeType : $origMimeType;
		$error = null;
		
		return new UploadedFile($path, $origFilename, $mimeType, $error, $test);
	}
	
	/**
	 * Create an UploadedFile object from file's full path
	 *
	 * @param string $path
	 * @param bool $test
	 * @return false|\Illuminate\Http\UploadedFile
	 */
	public static function fromPath(string $path, bool $test = true): bool|UploadedFile
	{
		if (empty($path) || !Storage::exists($path)) {
			return false;
		}
		
		$path = Storage::path($path);
		
		$filesystem = new Filesystem();
		$originalName = $filesystem->name($path) . '.' . $filesystem->extension($path);
		$mimeType = $filesystem->mimeType($path);
		$error = null;
		
		return new UploadedFile($path, $originalName, $mimeType, $error, $test);
	}
	
	/**
	 * @param \Throwable $e
	 * @return null
	 */
	private static function showError(\Throwable $e): null
	{
		if (!isFromApi()) {
			notification($e->getMessage(), 'error');
		} else {
			abort(500, $e->getMessage());
		}
		
		return null;
	}
}
