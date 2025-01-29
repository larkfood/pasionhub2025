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

class ThumbnailService
{
	private ?string $filePath;
	private string|bool|null $filePathFallback;
	private ThumbnailParams $thumbParamsInstance;
	private array $params;
	
	/**
	 * @param string|null $filePath
	 * @param string|bool|null $filePathFallback
	 */
	public function __construct(?string $filePath, string|null|bool $filePathFallback = null)
	{
		$this->filePath = $filePath;
		$this->filePathFallback = $filePathFallback;
	}
	
	/**
	 * Get the image's thumbnail URL
	 *
	 * @param string|null $resizeOptionsName
	 * @return string|null
	 */
	public function url(?string $resizeOptionsName = 'picture-lg'): ?string
	{
		$this->resize($resizeOptionsName);
		
		return $this->thumbParamsInstance->url();
	}
	
	/**
	 * @param string|null $resizeOptionsName
	 * @param bool $webpFormat
	 * @return \App\Services\ThumbnailService
	 */
	public function resize(?string $resizeOptionsName = 'picture-lg', bool $webpFormat = false): static
	{
		if (empty($this->params)) {
			$this->setOption($resizeOptionsName);
		}
		
		$filePath = $this->params['filePath'] ?? null;
		
		thumbImage($filePath, $this->filePathFallback)->resize($this->params, $webpFormat);
		// if (!str_contains($filePath, 'default')) dd($filePath); // debug!
		
		return $this;
	}
	
	/**
	 * Get thumbnail parameters
	 *
	 * @param string|null $resizeOptionsName
	 * @return \App\Services\ThumbnailService
	 */
	public function setOption(?string $resizeOptionsName = 'picture-lg'): static
	{
		$this->thumbParamsInstance = thumbParam($this->filePath, $this->filePathFallback)->setOption($resizeOptionsName);
		$this->params = $this->thumbParamsInstance->resizeParameters();
		
		return $this;
	}
}
