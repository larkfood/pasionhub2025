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

namespace App\Http\Controllers\Web\Setup\Install\Traits\Checker\Components\Extension;

trait ImageTrait
{
	/**
	 * @return array
	 */
	protected function getImageFormats(): array
	{
		return [
			'gd'      => $this->getGdFormats(),
			'imagick' => $this->getImagickFormats(),
		];
	}
	
	/**
	 * @return array
	 */
	protected function getGdFormats(): array
	{
		if (!(extension_loaded('gd') && function_exists('gd_info'))) {
			return [];
		}
		
		$driver = \Intervention\Image\Drivers\Gd\Driver::class;
		$supportedFormats = getServerSupportedImageFormats($driver);
		$installedFormats = getServerInstalledImageFormats($driver);
		
		$imageFormats = [];
		foreach ($supportedFormats as $extension) {
			$name = getImageFormats($extension);
			if (empty($name)) continue;
			
			$imageFormats[] = [
				'type'              => 'imageFormat',
				'name'              => $name,
				'required'          => false,
				'isOk'              => in_array($extension, $installedFormats),
				'permanentChecking' => false,
				'warning'           => 'Not compiled with the installed GD extension.',
				'success'           => 'Compiled with the installed GD extension.',
			];
		}
		
		return $imageFormats;
	}
	
	/**
	 * @return array
	 */
	protected function getImagickFormats(): array
	{
		if (!(extension_loaded('imagick') && class_exists('\Imagick'))) {
			return [];
		}
		
		$driver = \Intervention\Image\Drivers\Imagick\Driver::class;
		$supportedFormats = getServerSupportedImageFormats($driver);
		$installedFormats = getServerInstalledImageFormats($driver);
		
		$imageFormats = [];
		foreach ($supportedFormats as $extension) {
			$name = getImageFormats($extension);
			if (empty($name)) continue;
			
			$imageFormats[] = [
				'type'              => 'imageFormat',
				'name'              => $name,
				'required'          => false,
				'isOk'              => in_array($extension, $installedFormats),
				'permanentChecking' => false,
				'warning'           => 'Not compiled with the installed Imagick extension.',
				'success'           => 'Compiled with the installed Imagick extension.',
			];
		}
		
		return $imageFormats;
	}
}
