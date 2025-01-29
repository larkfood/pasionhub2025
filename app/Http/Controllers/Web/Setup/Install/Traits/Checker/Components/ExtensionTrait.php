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

namespace App\Http\Controllers\Web\Setup\Install\Traits\Checker\Components;

use App\Http\Controllers\Web\Setup\Install\Traits\Checker\Components\Extension\ImageTrait;

trait ExtensionTrait
{
	use ImageTrait;
	
	/**
	 * @param string $extension
	 * @param null $requiredVersion
	 * @return string
	 */
	private function getExtensionWarning(string $extension, $requiredVersion = null): string
	{
		$requiredVersion = !empty($requiredVersion)
			? ' <code>' . $requiredVersion . '</code> or higher'
			: '';
		
		return 'PHP ' . $extension . ' extension' . $requiredVersion . ' is required.';
	}
	
	/**
	 * @param string $extension
	 * @param null $version
	 * @return string
	 */
	private function getExtensionMessage(string $extension, $version = null): string
	{
		$version = !empty($version) ? $version : $this->getExtensionVersion($extension);
		$version = !empty($version) ? ' <code>' . $version . '</code>' : '';
		
		return 'PHP ' . $extension . ' extension' . $version . ' is installed.';
	}
	
	/**
	 * @param string $extension
	 * @param string|null $fallback
	 * @param bool $strict
	 * @return string|null
	 */
	private function getExtensionVersion(string $extension, ?string $fallback = null, bool $strict = false): ?string
	{
		$version = null;
		
		// curl
		if ($extension == 'curl') {
			$version = (extension_loaded('curl') && function_exists('curl_version'))
				? data_get(curl_version(), 'version')
				: null;
		}
		
		// gd
		if ($extension == 'gd') {
			if (extension_loaded('gd') && function_exists('gd_info')) {
				$gdInfo = gd_info();
				$versionInfo = $gdInfo['GD Version'] ?? '';
				preg_match('/\(([\d\.]+)\s/', $versionInfo, $matches);
				$version = $matches[1] ?? null;
			}
		}
		
		// imagick
		if ($extension == 'imagick') {
			$version = phpversion($extension);
			$version = is_string($version) ? $version : null;
			$version = ($version != PHP_VERSION) ? $version : null;
			if (!$strict) {
				if (!empty($version)) {
					$imageMagickVersion = $this->getImageMagickVersion();
					if (!empty($imageMagickVersion)) {
						$version .= ' (compiled with ' . $imageMagickVersion . ')';
					}
				}
			}
		}
		
		// image_magick (imagick submodule)
		if ($extension == 'image_magick') {
			$version = $this->getImageMagickVersion(true);
		}
		
		// All the other extensions
		if (empty($version)) {
			if (extension_loaded($extension)) {
				$extVersion = phpversion($extension);
				$version = is_string($extVersion) ? $extVersion : null;
				$version = ($version != PHP_VERSION) ? $version : null;
			}
		}
		
		$version = !empty($version) ? $version : $fallback;
		
		return (!empty($version) && !$strict) ? 'v' . $version : $version;
	}
	
	/**
	 * Get the ImageMagick library version
	 *
	 * @param bool $strict
	 * @return string|null
	 */
	private function getImageMagickVersion(bool $strict = false): ?string
	{
		if (!(extension_loaded('imagick') && class_exists('\Imagick'))) {
			return null;
		}
		
		$v = \Imagick::getVersion();
		$versionString = $v['versionString'] ?? '';
		
		if ($strict) {
			preg_match('/ImageMagick ([0-9]+\.[0-9]+\.[0-9]+)/i', $versionString, $matches);
			$version = $matches[1] ?? null;
		} else {
			$pattern = '/\b(?:https?:\/\/)?(?:www\.)?imagemagick\.org\b/i';
			$versionString = preg_replace($pattern, '', $versionString);
			$versionString = preg_replace('/ Q16/i', '', $versionString);
			$version = normalizeSpace($versionString);
		}
		
		return $version;
	}
}
