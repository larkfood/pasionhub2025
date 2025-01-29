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
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Symfony\Component\Mime\MimeTypes;

class FileSys
{
	/**
	 * Determine if the uploaded data contains a file
	 *
	 * @param string $key
	 * @param \Illuminate\Http\Request|null $request
	 * @return bool
	 */
	public static function hasRequestedFile(string $key, ?Request $request = null): bool
	{
		if (empty($request)) {
			$request = request();
		}
		
		$hasFile = $request->files->has($key);
		if (!$hasFile) {
			$hasFile = $request->hasFile($key);
		}
		
		return $hasFile;
	}
	
	/**
	 * Retrieve a file from the request
	 *
	 * @param string $key
	 * @param \Illuminate\Http\Request|null $request
	 * @return array|\Illuminate\Http\UploadedFile|\Illuminate\Http\UploadedFile[]|mixed|null
	 */
	public static function getRequestedFile(string $key, ?Request $request = null): mixed
	{
		if (empty($request)) {
			$request = request();
		}
		
		$file = $request->files->get($key);
		if (empty($file)) {
			$file = $request->file($key);
			if (empty($file)) {
				$file = $request->input($key);
				$file = self::getValidatedUploadedFiles($file);
			}
		}
		
		return $file;
	}
	
	/**
	 * Get an array of all the files on the request
	 *
	 * @param \Illuminate\Http\Request|null $request
	 * @return array|\Illuminate\Http\UploadedFile[]
	 */
	public static function getAllRequestedFiles(?Request $request = null): array
	{
		if (empty($request)) {
			$request = request();
		}
		
		$files = $request->files->all();
		if (empty($files)) {
			$files = $request->allFiles();
		}
		
		return $files;
	}
	
	/**
	 * Get uploaded file content
	 *
	 * @param \Illuminate\Http\UploadedFile|null $file
	 * @return string|null
	 */
	public static function getUploadedFileContent(?UploadedFile $file = null): ?string
	{
		if (!$file instanceof UploadedFile) return null;
		
		if (method_exists($file, 'getRealPath')) {
			return getAsStringOrNull(file_get_contents($file->getRealPath()));
		}
		
		if (method_exists($file, 'getContent')) {
			return getAsStringOrNull($file->getContent());
		}
		
		if (method_exists($file, 'get')) {
			try {
				return getAsStringOrNull($file->get());
			} catch (\Throwable $e) {}
		}
		
		return null;
	}
	
	/**
	 * Check if value is uploaded file data
	 *
	 * @param $value
	 * @return bool
	 */
	public static function isUploadedFile($value): bool
	{
		if (
			($value instanceof UploadedFile)
			|| (is_string($value) && str_starts_with($value, 'data:'))
		) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Get file mime-type
	 *
	 * @param \Illuminate\Http\UploadedFile|string|null $value
	 * @param bool $strictMode
	 * @return string|null
	 */
	public static function getMimeType(UploadedFile|string|null $value, bool $strictMode = false): ?string
	{
		if (empty($value)) return null;
		
		// Using Laravel uploaded file object
		if (!is_string($value)) {
			if ($value instanceof UploadedFile) {
				return $value->getMimeType();
			}
		}
		
		if (!is_string($value)) return null;
		
		// Using core PHP & base64 encoded file data
		$mimeType = self::getBase64EncodedFileMimeType($value);
		
		// Using Symfony & file extension
		if (empty($mimeType)) {
			$extension = !$strictMode ? self::getPathInfoExtension($value) : null;
			$mimeType = self::getExtensionMimeType($extension);
		}
		
		if (!empty($mimeType)) return $mimeType;
		
		// Get file full path
		$disk = StorageDisk::getDisk();
		$filePath = file_exists($value) ? $value : ($disk->exists($value) ? $disk->path($value) : null);
		
		if (!empty($filePath)) {
			$mimeType = self::getMimeTypeUsingMagicMime($filePath);
			if (empty($mimeType)) {
				$mimeType = self::getMimeTypeUsingFileInfo($filePath);
			}
		}
		
		return !empty($mimeType) ? strtolower($mimeType) : $mimeType;
	}
	
	/**
	 * Get file extension
	 *
	 * @param \Illuminate\Http\UploadedFile|string|null $value
	 * @param bool $strictMode
	 * @return string|null
	 */
	public static function getExtension(UploadedFile|string|null $value, bool $strictMode = false): ?string
	{
		if (empty($value)) return null;
		
		if (!is_string($value)) {
			if ($value instanceof UploadedFile) {
				return $value->getClientOriginalExtension();
			}
		}
		
		if (!is_string($value)) return null;
		
		// Using core PHP & base64 encoded file data
		$mimeType = self::getBase64EncodedFileMimeType($value);
		$extension = self::getMimeTypeExtension($mimeType);
		
		// File path ending by the extension (Only for non-strict mode)
		if (empty($extension)) {
			if (!$strictMode) {
				$extension = self::getPathInfoExtension($value);
			}
		}
		
		if (!empty($extension)) return $extension;
		
		// Get file full path
		$disk = StorageDisk::getDisk();
		$filePath = file_exists($value) ? $value : ($disk->exists($value) ? $disk->path($value) : null);
		
		if (!empty($filePath)) {
			// From the file mime-type, by using information from the magic.mime file
			$mimeType = self::getMimeTypeUsingMagicMime($filePath);
			$extension = self::getMimeTypeExtension($mimeType);
			
			// From the file mime-type, using PHP fileinfo extension
			if (empty($extension)) {
				$mimeType = self::getMimeTypeUsingFileInfo($filePath);
				$extension = self::getMimeTypeExtension($mimeType);
			}
		}
		
		return !empty($extension) ? strtolower($extension) : $extension;
	}
	
	// Symfony MimeTypes Class
	
	/**
	 * Get extension mime-type
	 *
	 * @param string|null $extension
	 * @return string|null
	 */
	public static function getExtensionMimeType(?string $extension): ?string
	{
		if (empty($extension)) return null;
		
		$mimeTypesInstance = new MimeTypes();
		$mimeTypes = $mimeTypesInstance->getMimeTypes($extension);
		$mimeType = $mimeTypes[0] ?? null;
		
		return !empty($mimeType) ? strtolower($mimeType) : null;
	}
	
	/**
	 * Get mime-type's extension
	 *
	 * @param string|null $mimeType
	 * @return string|null
	 */
	public static function getMimeTypeExtension(?string $mimeType): ?string
	{
		if (empty($mimeType)) return null;
		
		$mimeTypesInstance = new MimeTypes();
		$extensions = $mimeTypesInstance->getExtensions($mimeType);
		
		return $extensions[0] ?? null;
	}
	
	// MIME-TYPE
	
	/**
	 * Get base64 encoded file's mime-type
	 *
	 * @param string|null $string
	 * @return string|null
	 */
	public static function getBase64EncodedFileMimeType(?string $string): ?string
	{
		if (empty($string)) return null;
		if (!str_starts_with($string, 'data:')) return null;
		
		$matches = [];
		preg_match('/^data:(\w+\/[\w-]+);base64,/', $string, $matches);
		$mimeType = $matches[1] ?? null;
		
		if (empty($mimeType)) {
			try {
				$mimeType = mime_content_type($string);
			} catch (\Throwable $e) {
			}
		}
		
		return !empty($mimeType) ? strtolower($mimeType) : null;
	}
	
	/**
	 * Get file mime-type, using information from the 'magic.mime' file
	 *
	 * @param string|null $filePath
	 * @return string|null
	 */
	public static function getMimeTypeUsingMagicMime(?string $filePath): ?string
	{
		if (empty($filePath)) return null;
		if (!file_exists($filePath)) return null;
		
		try {
			$mimeType = mime_content_type($filePath);
		} catch (\Throwable $e) {
		}
		
		return !empty($mimeType) ? strtolower($mimeType) : null;
	}
	
	/**
	 * Get file mime-type, using PHP fileinfo extension
	 *
	 * @param string|null $filePath
	 * @return string|null
	 */
	public static function getMimeTypeUsingFileInfo(?string $filePath): ?string
	{
		if (empty($filePath)) return null;
		if (!file_exists($filePath)) return null;
		
		try {
			$mimeType = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $filePath);
		} catch (\Throwable $e) {
		}
		
		return !empty($mimeType) ? strtolower($mimeType) : null;
	}
	
	// EXTENSION
	
	/**
	 * Get filename extension
	 *
	 * @param string|null $filename
	 * @return string|null
	 */
	public static function getPathInfoExtension(?string $filename): ?string
	{
		if (empty($filename)) return null;
		if (!str_contains($filename, '.')) return null;
		
		$extension = pathinfo($filename, PATHINFO_EXTENSION);
		$extension = !empty($extension) ? ltrim($extension, '.') : null;
		
		return !empty($extension) ? strtolower($extension) : null;
	}
	
	// OTHER
	
	/**
	 * Check if the string is a valid base64 file string
	 *
	 * @param string|null $string
	 * @return bool
	 */
	public static function isBase64FileString(?string $string): bool
	{
		if (empty($string)) return false;
		
		// Check if the string contains the base64 data format prefix
		if (preg_match('/^data:\w+\/[\w-]+\;base64,/', $string)) {
			// Remove the prefix to validate the remaining base64 content
			$base64String = self::extractBase64String($string);
			
			// Decode the base64 string
			$decodedData = base64_decode($base64String, true);
			
			// Check if decoding was successful and if the result is a binary string
			if ($decodedData !== false && preg_match('%^[a-zA-Z0-9/+]*={0,2}$%', $base64String)) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Extract base64 data URI
	 *
	 * @param string|null $dataUri
	 * @return string|null
	 */
	public static function extractBase64String(?string $dataUri): ?string
	{
		if (empty($dataUri)) return false;
		
		// Return null if the string is not a valid base64 data URI
		$base64String = null;
		
		// Check if the string contains the base64 data format prefix
		if (preg_match('/^data:\w+\/[\w-]+;base64,/', $dataUri)) {
			// Remove the prefix and return the base64 content
			$base64String = preg_replace('/^data:\w+\/[\w-]+;base64,/', '', $dataUri);
		}
		
		return getAsString($base64String);
	}
	
	// PRIVATE
	
	/**
	 * Validate uploaded files
	 *
	 * Note: This function returns:
	 * - An UploadedFile instance if it's a single file,
	 * - An array containing only UploadedFile instances and null values if it is multiple files,
	 * - Or null if it doesn't meet the requirements
	 *
	 * @param $files
	 * @return \Illuminate\Http\UploadedFile|array|null
	 */
	private static function getValidatedUploadedFiles($files): UploadedFile|array|null
	{
		if ($files instanceof UploadedFile) {
			// Return the single UploadedFile instance directly
			return $files;
		} elseif (is_array($files)) {
			// Filter to keep only UploadedFile instances and null items
			$filteredFiles = array_filter($files, fn($file) => $file instanceof UploadedFile || is_null($file));
			
			// Ensure the array contains only UploadedFile instances or null values
			return count($filteredFiles) === count($files) ? $filteredFiles : null;
		}
		
		// Return null if not a valid type
		return null;
	}
}
