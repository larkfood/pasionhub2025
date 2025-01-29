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

namespace App\Helpers;

use Illuminate\Support\Facades\File;

class PhpArrayFile
{
	/**
	 * Get the content in the given file path.
	 *
	 * @param string|null $filePath
	 * @param bool $createIfNotExists
	 * @return array
	 */
	public static function getFileContent(?string $filePath, bool $createIfNotExists = false): array
	{
		if (!File::exists($filePath)) {
			if ($createIfNotExists) {
				self::writeFile($filePath, []);
			}
			
			return [];
		}
		
		if (File::exists($filePath)) {
			return (array)include $filePath;
		}
		
		return [];
	}
	
	/**
	 * Write a config/language file from array.
	 *
	 * @param string|null $filePath
	 * @param array $contentArray
	 * @return void
	 */
	public static function writeFile(?string $filePath, array $contentArray): void
	{
		if (empty($filePath)) {
			return;
		}
		
		if (!File::exists($directory = dirname($filePath))) {
			mkdir($directory, 0777, true);
		}
		
		$content = "<?php \n\nreturn [";
		
		if (!empty($contentArray)) {
			$content .= self::stringLineMaker($contentArray);
			$content .= "\n";
		}
		
		$content .= "];\n";
		
		File::put($filePath, $content);
	}
	
	/**
	 * Write the lines of the inner array of the config/language file.
	 *
	 * @param array $array
	 * @param string $prepend
	 * @return string
	 */
	public static function stringLineMaker(array $array, string $prepend = ''): string
	{
		$output = '';
		
		foreach ($array as $key => $value) {
			$key = str_replace('\"', '"', addslashes($key));
			
			if (is_array($value)) {
				$value = self::stringLineMaker($value, $prepend . '    ');
				
				$output .= "\n{$prepend}    '{$key}' => [{$value}\n{$prepend}    ],";
			} else {
				$value = str_replace('\"', '"', addslashes($value));
				
				$output .= "\n{$prepend}    '{$key}' => '{$value}',";
			}
		}
		
		return $output;
	}
}
