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

use App\Helpers\Arr;

/**
 * @param string|null $driver
 * @return array
 */
function getServerSupportedImageFormats(?string $driver = null): array
{
	if (empty($driver)) {
		$defaultDriver = \Intervention\Image\Drivers\Gd\Driver::class;
		$driver = config('image.driver', $defaultDriver);
	}
	
	$values = getReferrerList('supported-images');
	$values = $values['server'][$driver] ?? [];
	
	return !empty($values) ? array_keys($values) : [];
}

/**
 * @return array
 */
function getClientSupportedImageFormats(): array
{
	$values = getReferrerList('supported-images');
	$values = $values['client']['extensions'] ?? [];
	
	return !empty($values) ? array_keys($values) : [];
}

/**
 * @param string|null $driver
 * @return array
 */
function getServerInstalledImageFormats(?string $driver = null): array
{
	if (empty($driver)) {
		$driver = config('image.driver');
	}
	
	$formats = ($driver == \Intervention\Image\Drivers\Imagick\Driver::class)
		? getImagickInstalledImageFormats()
		: getGdInstalledImageFormats();
	
	// When "jpeg" is in the list, add "jpg" if missing (before "jpeg")
	if (in_array('jpeg', $formats) && !in_array('jpg', $formats)) {
		$collection = collect($formats);
		$index = $collection->search('jpeg');
		if ($index !== false) {
			$collection->splice($index, 0, ['jpg']);
		}
		$formats = $collection->toArray();
	}
	
	// When "tiff" is in the list, add "tif" if missing (after "tiff")
	if (in_array('tiff', $formats) && !in_array('tif', $formats)) {
		$collection = collect($formats);
		$index = $collection->search('tiff');
		if ($index !== false) {
			$collection->splice($index + 1, 0, ['tif']);
		}
		$formats = $collection->toArray();
	}
	
	return $formats;
}

/**
 * @return array
 */
function getClientInstalledImageFormats(): array
{
	$supportedFormats = getClientSupportedImageFormats();
	$installedFormats = getServerInstalledImageFormats();
	$formats = array_intersect($supportedFormats, $installedFormats);
	
	return array_values($formats);
}

/**
 * @return array
 */
function getServerAllowedImageFormats(): array
{
	$installedFormats = getServerInstalledImageFormats();
	$installedFormatList = collect($installedFormats)->join(',');
	
	$formatList = config('settings.upload.image_types', $installedFormatList);
	$formatList = normalizeSeparatedList($formatList);
	
	$formats = explode(',', $formatList);
	$formats = array_filter($formats, fn ($item) => $item !== '');
	$formats = array_intersect($formats, $installedFormats);
	$formats = array_values($formats);
	
	return Arr::sortByReference($formats, getServerSupportedImageFormats());
}

/**
 * @return array
 */
function getClientAllowedImageFormats(): array
{
	$installedFormats = getClientInstalledImageFormats();
	$installedFormatList = collect($installedFormats)->join(',');
	
	$formatList = config('settings.upload.client_image_types', $installedFormatList);
	$formatList = normalizeSeparatedList($formatList);
	
	$formats = explode(',', $formatList);
	$formats = array_filter($formats, fn ($item) => $item !== '');
	$formats = array_intersect($formats, $installedFormats);
	$formats = array_values($formats);
	
	return Arr::sortByReference($formats, getServerSupportedImageFormats());
}

/**
 * @param string|null $extension
 * @return string
 */
function getClientImageExtensionFor(?string $extension): string
{
	$fallbackExtension = getClientImageFallbackExtension();
	if (empty($extension)) return $fallbackExtension;
	
	$allowedFormats = getClientAllowedImageFormats();
	
	return in_array($extension, $allowedFormats) ? $extension : $fallbackExtension;
}

/**
 * @return string
 */
function getClientImageFallbackExtension(): string
{
	$fallbackExtension = 'jpg';
	
	$values = getReferrerList('supported-images');
	$values = $values['client']['fallbackExtension'] ?? $fallbackExtension;
	
	return getAsString($values, $fallbackExtension);
}

function getGdInstalledImageFormats(): array
{
	if (!(extension_loaded('gd') && function_exists('gd_info'))) {
		return [];
	}
	
	$driver = \Intervention\Image\Drivers\Gd\Driver::class;
	$config = getReferrerList('supported-images');
	$defaultSupportedFormats = $config['server'][$driver] ?? [];
	$defaultSupportedFormats = array_keys($defaultSupportedFormats);
	
	// Get GD library information
	$gdInfo = gd_info();
	
	$formats = collect($gdInfo)
		->filter(fn ($value, $key) => (str_contains(strtolower($key), 'support') && $value === true))
		->keys()
		->map(fn ($item) => str($item)->before(' ')->lower()->toString())
		->unique()
		->filter(fn ($item) => in_array($item, $defaultSupportedFormats));
	
	if ($formats->count() > 0) {
		$formats = $formats->values();
	}
	
	$formats = $formats->toArray();
	
	return Arr::sortByReference($formats, getServerSupportedImageFormats());
}

function getImagickInstalledImageFormats(): array
{
	if (!(extension_loaded('imagick') && class_exists('\Imagick'))) {
		return [];
	}
	
	$driver = \Intervention\Image\Drivers\Imagick\Driver::class;
	$config = getReferrerList('supported-images');
	$defaultSupportedFormats = $config['server'][$driver] ?? [];
	$defaultSupportedFormats = array_keys($defaultSupportedFormats);
	
	// Create an instance of Imagick
	$imagick = new \Imagick();
	
	// Get the supported formats
	$supportedFormats = $imagick->queryFormats();
	
	$formats = collect($supportedFormats)
		->map(fn ($item) => str($item)->lower()->toString())
		->filter(fn ($item) => in_array($item, $defaultSupportedFormats))
		->values()
		->toArray();
	
	return Arr::sortByReference($formats, getServerSupportedImageFormats());
}

/**
 * @param string|null $extension
 * @return array|string|null
 */
function getImageFormats(?string $extension = null): array|string|null
{
	$values = getReferrerList('supported-images');
	$values = $values['imageExtensions'] ?? [];
	
	if (!empty($extension)) {
		$name = $values[$extension] ?? null;
		$name = is_string($name) ? $name : null;
		if (!empty($name)) {
			$name .= str($extension)->upper()->wrap(' (.', ')')->toString();
		}
		
		return $name;
	}
	
	return $values;
}
