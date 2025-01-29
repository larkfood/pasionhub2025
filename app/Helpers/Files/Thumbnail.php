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
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Contracts\Filesystem\Filesystem;

class Thumbnail
{
	private Filesystem $disk;
	private ?string $filePath;
	private string|bool|null $filePathFallback;
	private bool $isWebpFormatEnabled;
	
	/**
	 * @param string|null $filePath
	 * @param string|bool|null $filePathFallback
	 */
	public function __construct(?string $filePath, string|null|bool $filePathFallback = null)
	{
		// Storage disk init.
		$this->disk = StorageDisk::getDisk();
		
		// File path
		$this->filePath = $filePath;
		$this->filePathFallback = $filePathFallback;
		
		// Is WebP format enabled
		$this->isWebpFormatEnabled = (config('settings.optimization.webp_format') == '1');
	}
	
	/**
	 * Create thumbnail for the image
	 *
	 * @param array $options
	 * @param bool $webpFormat
	 * @return void
	 */
	public function resize(array $options = [], bool $webpFormat = false): void
	{
		$filePath = $this->filePath;
		
		// 0. Check if file path is empty
		if (empty($filePath)) {
			$this->filePath = $this->filePathFallback;
			
			return;
		}
		
		// Check if the original file exists (i.e. file before resize)
		if (!$this->disk->exists($filePath)) {
			$this->filePath = $this->filePathFallback;
			
			return;
		}
		
		// 1. Check if the file path is not one of one default media
		if ($this->isDefaultMedia($filePath)) {
			return;
		}
		
		// 2. Check if the resize option name is valid
		//    Resizing ignored, return the image as is
		if (empty($options)) {
			return;
		}
		
		// 3. Try to create a thumbnail for the picture (by getting the resize parameters)
		// Image Quality (for JPEG)
		$imageQuality = (int)config('settings.upload.image_quality', 90);
		
		// Image progressively rending
		$isProgressive = (config('settings.upload.image_progressive') == '1');
		
		// Get the resize parameters
		$width = (int)data_get($options, 'width', 900);
		$height = (int)data_get($options, 'height', 900);
		$method = data_get($options, 'method', 'resize');
		$ratio = data_get($options, 'ratio', '1');
		$upsize = data_get($options, 'upsize', '0');
		$position = data_get($options, 'position', 'center');
		$relative = data_get($options, 'relative', false);
		$bgColor = data_get($options, 'bgColor', 'ffffff');
		
		// Get the file name
		$filename = !str_ends_with($filePath, DIRECTORY_SEPARATOR) ? basename($filePath) : '';
		$fileDir = str($filePath)->dirname()->finish(DIRECTORY_SEPARATOR)->toString();
		
		// WebP
		$filenameWithoutExtension = pathinfo($filename, PATHINFO_FILENAME);
		$webpFilename = $filenameWithoutExtension . '.webp';
		if ($webpFormat) {
			if (!$this->isWebpFormatEnabled) {
				$this->filePath = null;
				
				return;
			}
			$filename = $webpFilename;
		}
		
		$thumbDirPrefix = 'thumbnails' . DIRECTORY_SEPARATOR;
		$thumbFilenamePrefix = $width . 'x' . $height;
		
		// Build the thumbnail file name
		$thumbFileDir = $fileDir . $thumbDirPrefix;
		$thumbFilename = $thumbFilenamePrefix . '-' . $filename;
		$thumbFilePath = $thumbFileDir . $thumbFilename;
		
		if (!$this->disk->exists($thumbFileDir)) {
			$this->disk->makeDirectory($thumbFileDir);
		}
		
		// 4. Does the thumb file exist?
		if ($this->disk->exists($thumbFilePath)) {
			// Save the thumbnail file path
			$this->filePath = $thumbFilePath;
			
			return;
		}
		
		// Get files full path
		$fileFullPath = $this->disk->path($filePath);
		$fileBasePath = str($fileFullPath)->dirname()->finish(DIRECTORY_SEPARATOR)->toString();
		$thumbBasePath = $fileBasePath . $thumbDirPrefix;
		$thumbFullPath = $thumbBasePath . $thumbFilename;
		
		// 5. The thumb file doesn't exist, so we can create it
		try {
			// Get file extension
			// $origExtension = FileSys::getExtension($this->disk->get($filePath));
			$origExtension = FileSys::getExtension($filePath);
			$origExtension = $origExtension ?? getClientImageFallbackExtension();
			
			// Get the client extension for the original extension
			$extension = getClientImageExtensionFor($origExtension);
			$extension = $webpFormat ? 'webp' : $extension;
			
			// Init. Intervention
			// $image = Image::read($this->disk->get($filePath));
			$image = Image::read($fileFullPath);
			
			// Get the image original dimensions
			$imgWidth = $image->width();
			$imgHeight = $image->height();
			
			// Manage Image By Method
			// Apply the resize method
			if ($method == 'resize') {
				
				if ($imgWidth > $width || $imgHeight > $height || $upsize == '1') {
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
				
			} else if ($method == 'fit') {
				
				// Fit ($ratio doesn't have any effect)
				$image = ($upsize == '1')
					? $image->cover($width, $height)
					: $image->coverDown($width, $height);
				
			} else if ($method == 'pad') {
				
				// Pad ($ratio doesn't have any effect)
				$image = ($upsize == '1')
					? $image->contain($width, $height, $bgColor, $position)
					: $image->pad($width, $height, $bgColor, $position);
				
			} else if ($method == 'resizeCanvas') {
				
				if ($imgWidth > $width || $imgHeight > $height || $upsize == '1') {
					// Resize (for ResizeCanvas)
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
				
				// ResizeCanvas
				$image = $relative
					? $image->resizeCanvasRelative($width, $height, $bgColor, $position)
					: $image->resizeCanvas($width, $height, $bgColor, $position);
				
				// Resize the canvas
				// $image = $image->resize($width, $height);
				
			} else {
				
				if ($imgWidth > $width || $imgHeight > $height) {
					// Resize (with hard parameters, with ratio)
					$image = $image->scaleDown($width, $height);
				}
				
			}
			
			// Encode the Image!
			$encodedImage = $image->encodeByExtension($extension, progressive: $isProgressive, quality: $imageQuality);
			unset($image);
			
		} catch (\Throwable $e) {
			return;
		}
		
		// Store the image on disk.
		// $this->disk->put($thumbFilePath, $encodedImage->toString());
		$encodedImage->save($thumbFullPath);
		unset($encodedImage);
	}
	
	/**
	 * Check if this is the default picture
	 *
	 * @param string $filePath
	 * @return bool
	 */
	private function isDefaultMedia(string $filePath): bool
	{
		return (
			str_contains($filePath, config('larapen.media.logo'))
			|| str_contains($filePath, config('larapen.media.logo-dark'))
			|| str_contains($filePath, config('larapen.media.logo-light'))
			|| str_contains($filePath, config('larapen.media.favicon'))
			|| str_contains($filePath, config('larapen.media.picture'))
			|| str_contains($filePath, config('larapen.media.avatar'))
		);
	}
}
