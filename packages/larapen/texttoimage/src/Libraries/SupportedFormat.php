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

namespace Larapen\TextToImage\Libraries;

class SupportedFormat
{
	/**
	 * @param string|null $format
	 * @return string
	 */
	public static function getFormat(?string $format): string
	{
		$defaultFormat = 'png';
		if (empty($format)) return $defaultFormat;
		
		$driver = config('image.driver');
		$imagick = \Intervention\Image\Drivers\Imagick\Driver::class;
		
		$supportedFormats = [
			'jpg'  => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'gif'  => 'image/gif',
			'png'  => 'image/png',
			'avif' => 'image/avif',
			'bmp'  => 'image/bmp',
			'webp' => 'image/webp', // 'Animated WebP' is not supported
		];
		if ($driver == $imagick) {
			$supportedFormats = [
				'jpg'  => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'gif'  => 'image/gif',
				'png'  => 'image/png',
				'avif' => 'image/avif',
				'bmp'  => 'image/bmp',
				'webp' => 'image/webp',
				'tiff' => 'image/tiff',
				'tif'  => 'image/tiff',
				'jp2'  => 'image/jp2',
				'j2c'  => 'image/x-jp2-codestream',
				'j2k'  => 'image/x-jp2-codestream',
				'heic' => 'image/heic',
				'heif' => 'image/heif',
			];
		}
		
		$supportedFormats = array_keys($supportedFormats);
		$format = strtolower($format);
		
		return in_array($format, $supportedFormats) ? $format : $defaultFormat;
	}
}
