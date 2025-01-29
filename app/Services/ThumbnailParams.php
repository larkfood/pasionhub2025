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

namespace App\Services;

use App\Helpers\Files\Storage\StorageDisk;
use Illuminate\Contracts\Filesystem\Filesystem;

class ThumbnailParams
{
	private Filesystem $disk;
	private ?string $filePath;
	private ?string $filePathFallback;
	private array $params;
	
	/**
	 * @param string|null $filePath
	 * @param string|bool|null $filePathFallback
	 */
	public function __construct(?string $filePath, string|null|bool $filePathFallback = null)
	{
		// Storage disk init.
		$this->disk = StorageDisk::getDisk();
		
		// Default file path
		$defaultFilePath = config('larapen.media.picture');
		if (is_bool($filePathFallback)) {
			$this->filePathFallback = $filePathFallback ? $defaultFilePath : null;
		} else {
			$this->filePathFallback = !empty($filePathFallback) ? $filePathFallback : $defaultFilePath;
		}
		
		// File path
		$this->filePath = $filePath;
	}
	
	/**
	 * Get the image's thumbnail URL
	 *
	 * @param string|null $resizeOptionsName
	 * @return string|null
	 */
	public function url(?string $resizeOptionsName = 'picture-lg'): ?string
	{
		$params = $this->resizeParameters($resizeOptionsName);
		
		$filePath = $params['thumbFilePath'] ?? $params['filePath'] ?? $this->filePathFallback;
		if (empty($filePath) || !$this->disk->exists($filePath)) return null;
		
		try {
			$url = $this->disk->url($filePath) . getPictureVersion();
		} catch (\Throwable $e) {
			$url = url('common/file?path=' . $filePath) . getPictureVersion(true);
		}
		
		return $url;
	}
	
	/**
	 * @param string|null $resizeOptionsName
	 * @param bool $webpFormat
	 * @return array
	 */
	public function resizeParameters(?string $resizeOptionsName = 'picture-lg', bool $webpFormat = false): array
	{
		if (empty($this->params)) {
			$this->setOption($resizeOptionsName, $webpFormat);
		}
		
		return $this->params;
	}
	
	/**
	 * Get thumbnail parameters
	 *
	 * @param string|null $resizeOptionsName
	 * @param bool $webpFormat
	 * @return \App\Services\ThumbnailParams
	 */
	public function setOption(?string $resizeOptionsName = 'picture-lg', bool $webpFormat = false): static
	{
		$this->params = $this->getDefaultParameters();
		
		// Get file path
		$filePath = $this->filePath;
		
		$this->params['filePath'] = $filePath;
		$this->params['thumbFilePath'] = null;
		$this->params['webpFormat'] = $webpFormat;
		
		// Check if file path is empty
		if (empty($filePath)) {
			$this->params['filePath'] = $this->filePathFallback;
			
			return $this;
		}
		
		// Check if the original file exists (i.e. file before resize)
		if (!$this->disk->exists($filePath)) {
			$this->params['filePath'] = $this->filePathFallback;
			
			return $this;
		}
		
		// Check if the file path is not one of one default media
		if ($this->isDefaultMedia($filePath)) {
			return $this;
		}
		
		// Check if the resize option name is valid
		// Resizing ignored, return the image as is
		if (!$this->isValidResizeOption($resizeOptionsName)) {
			return $this;
		}
		
		// Check if the resize options name argument contents is statics dimensions
		if (preg_match('/\dx\d/i', $resizeOptionsName)) {
			$tmp = preg_split('/x/i', $resizeOptionsName);
			$this->params['width'] = (int)($tmp[0] ?? $this->params['width']);
			$this->params['height'] = (int)($tmp[1] ?? $this->params['height']);
		} else {
			// Resize Parameters (from Admin Settings)
			$absSettingKeyPrefix = 'settings.upload.img_resize_' . str_replace('-', '_', $resizeOptionsName);
			$this->params['width'] = (int)config($absSettingKeyPrefix . '_width', $this->params['width']);
			$this->params['height'] = (int)config($absSettingKeyPrefix . '_height', $this->params['height']);
			$this->params['method'] = config($absSettingKeyPrefix . '_method', $this->params['method']);
			$this->params['ratio'] = config($absSettingKeyPrefix . '_ratio', $this->params['ratio']);
			$this->params['upsize'] = config($absSettingKeyPrefix . '_upsize', $this->params['upsize']);
			$this->params['position'] = config($absSettingKeyPrefix . '_position', $this->params['position']);
			$this->params['relative'] = config($absSettingKeyPrefix . '_relative', $this->params['relative']);
			$this->params['bgColor'] = config($absSettingKeyPrefix . '_bgColor', $this->params['bgColor']);
		}
		
		// Get file name
		$filename = !str_ends_with($filePath, DIRECTORY_SEPARATOR) ? basename($filePath) : '';
		$fileDir = str($filePath)->dirname()->finish(DIRECTORY_SEPARATOR)->toString();
		
		// WebP
		$filenameWithoutExtension = pathinfo($filename, PATHINFO_FILENAME);
		$webpFilename = $filenameWithoutExtension . '.webp';
		if ($webpFormat) {
			$filename = $webpFilename;
		}
		
		// Thumb file name
		$thumbFileDir = $fileDir . 'thumbnails' . DIRECTORY_SEPARATOR;
		$thumbFilenamePrefix = $this->params['width'] . 'x' . $this->params['height'];
		$thumbFilename = $thumbFilenamePrefix . '-' . $filename;
		$thumbFilePath = $thumbFileDir . $thumbFilename;
		
		// Update the thumbnail parameters
		$this->params['option'] = $resizeOptionsName;
		$this->params['thumbFilePath'] = $thumbFilePath;
		
		return $this;
	}
	
	/**
	 * Get the default resize parameters
	 *
	 * @return array
	 */
	private function getDefaultParameters(): array
	{
		$params = [];
		
		$defaultOption = 'default';
		
		$params['option'] = $defaultOption;
		$params['filePath'] = null;
		$params['thumbFilePath'] = null;
		
		$config = config('larapen.media.resize.namedOptions.' . $defaultOption);
		$params['width'] = (int)data_get($config, 'width', 900);
		$params['height'] = (int)data_get($config, 'height', 900);
		$params['method'] = data_get($config, 'method', 'resize');
		$params['ratio'] = data_get($config, 'ratio', '1');
		$params['upsize'] = data_get($config, 'upsize', '0');
		$params['position'] = data_get($config, 'position', 'center');
		$params['relative'] = data_get($config, 'relative', false);
		$params['bgColor'] = data_get($config, 'bgColor', 'ffffff');
		
		return $params;
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
	
	/**
	 * @param string|null $resizeOptionsName
	 * @return bool
	 */
	private function isValidResizeOption(?string $resizeOptionsName): bool
	{
		if (empty($resizeOptionsName)) return false;
		
		// Get pre-resized picture URL
		$array = array_keys((array)config('larapen.media.resize.namedOptions'));
		
		return (in_array($resizeOptionsName, $array) || preg_match('/\dx\d/i', $resizeOptionsName));
	}
}
