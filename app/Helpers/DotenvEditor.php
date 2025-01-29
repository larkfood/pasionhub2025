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

use App\Exceptions\Custom\CustomException;

class DotenvEditor
{
	private static string $envFilePath;
	private static array $data = [];
	private static array $newData = [];
	private static array $wrappedKeys = [];
	
	/**
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	public function __construct()
	{
		self::$envFilePath = base_path('.env');
		self::$data = self::getOriginalData();
		self::$newData = self::$data;
	}
	
	/**
	 * Initialize static properties if needed
	 *
	 * @return void
	 */
	private static function initializeIfNeeded(): void
	{
		if (empty(self::$envFilePath)) {
			$dotenv = new self();
		}
	}
	
	/**
	 * Get key value from the .env file
	 *
	 * @param string $key The key of the variable
	 * @return string|null The value of the variable, or null if the variable does not exist
	 */
	public static function getValue(string $key): ?string
	{
		self::initializeIfNeeded();
		
		return self::$newData[$key] ?? null;
	}
	
	/**
	 * Update or add a key value in the .env file
	 *
	 * @param string $key
	 * @param $value
	 * @return void
	 */
	public static function setKey(string $key, $value): void
	{
		self::initializeIfNeeded();
		
		$value = getAsString($value);
		$value = trim($value, '"');
		self::$newData[$key] = $value;
	}
	
	/**
	 * Add empty line to the .env file
	 *
	 * @return void
	 */
	public static function addEmpty(): void
	{
		self::initializeIfNeeded();
		$latestItemValue = end(self::$newData);
		if ($latestItemValue != '***EMPTY-LINE***') {
			self::$newData['EMPTY-LINE-' . uniqid()] = '***EMPTY-LINE***';
		}
	}
	
	/**
	 * Add new comment to the .env file
	 *
	 * @param string $value
	 * @return void
	 */
	public static function addComment(string $value): void
	{
		self::initializeIfNeeded();
		$value = str_starts_with($value, '#') ? $value : '# ' . $value;
		self::$newData['COMMENT-LINE-' . uniqid()] = $value;
	}
	
	/**
	 * Check if key exists in the .env file
	 *
	 * @param string $key The key of the variable
	 * @return bool
	 */
	public static function keyExists(string $key): bool
	{
		self::initializeIfNeeded();
		
		return array_key_exists($key, self::$newData);
	}
	
	/**
	 * Delete key line from the .env file
	 *
	 * @param string $key The key of the variable to delete
	 * @return void
	 */
	public static function deleteKey(string $key): void
	{
		self::initializeIfNeeded();
		if (self::keyExists($key)) {
			unset(self::$newData[$key]);
		}
	}
	
	/**
	 * Get keys values
	 *
	 * @param array|null $keys
	 * @return array
	 */
	public static function getValues(?array $keys = null): array
	{
		self::initializeIfNeeded();
		if (empty($keys)) {
			return self::$newData;
		}
		
		$array = [];
		foreach ($keys as $key) {
			$array[$key] = self::getValue($key);
		}
		
		return $array;
	}
	
	/**
	 * Update or add bulk keys values to the .env file
	 *
	 * @param array $variables Associative array of key-value pairs
	 * @return void
	 */
	public static function setKeys(array $variables): void
	{
		self::initializeIfNeeded();
		if (empty($variables)) return;
		
		foreach ($variables as $key => $value) {
			self::setKey($key, $value);
		}
	}
	
	// ...
	
	/**
	 * Get all key/value from the .env file as an associative array
	 *
	 * @return array Associative array of key-value pairs, with empty lines represented as empty strings
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	private static function getOriginalData(): array
	{
		$envFile = self::$envFilePath;
		
		// Ensure the .env file is readable
		self::ensureFileIsWritable($envFile);
		
		// Read the .env file content
		$envContent = file_get_contents($envFile);
		
		// Split the content by lines
		$lines = preg_split('/\r\n|\r|\n/', $envContent);
		
		$data = [];
		foreach ($lines as $line) {
			// Trim whitespace from the line
			$line = trim($line);
			
			// Save empty lines
			if (empty($line)) {
				$data['EMPTY-LINE-' . uniqid()] = '***EMPTY-LINE***';
				continue;
			}
			
			// Save comments
			if (str_starts_with($line, '#')) {
				$data['COMMENT-LINE-' . uniqid()] = $line;
				continue;
			}
			
			// Split the line into key and value
			[$key, $value] = explode('=', $line, 2);
			
			// Check if value is wrapped in quotes
			if (preg_match('/^"(.*)"$/', $value, $matches)) {
				$value = $matches[1] ?? '';
				self::$wrappedKeys[] = $key;
			} else {
				// Remove comments from the value
				$value = preg_replace('/\s*#.*$/', '', $value);
			}
			
			// Remove surrounding double quotes from the value if present
			$value = trim($value, '"');
			
			$data[$key] = $value;
		}
		
		return $data;
	}
	
	/**
	 * Update or add bulk keys values to the .env file
	 *
	 * @return void
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	public static function save(): void
	{
		// self::initializeIfNeeded();
		if (empty(self::$newData)) return;
		if (self::$newData == self::$data) return;
		
		// Ensure the .env file is writable
		self::ensureFileIsWritable(self::$envFilePath);
		
		// Read the .env file content
		$envContent = '';
		
		// Update or add each variable in the .env content
		foreach (self::$newData as $key => $value) {
			$envContent = self::setKeyInContent($envContent, $key, $value);
		}
		
		// Set the right start and end of file content
		$envContent = trim($envContent);
		$envContent .= "\n";
		
		// Write the updated content back to the .env file
		file_put_contents(self::$envFilePath, $envContent);
	}
	
	// PRIVATE
	
	/**
	 * Update or add a key in .env buffer
	 *
	 * @param string $envContent
	 * @param string $key
	 * @param string|null $value
	 * @return string
	 */
	private static function setKeyInContent(string $envContent, string $key, ?string $value): string
	{
		// It is an empty line
		if (str_starts_with($key, 'EMPTY-LINE')) {
			return self::addNewLine($envContent, true);
		}
		
		// It is a comment
		if (str_starts_with($key, 'COMMENT-LINE')) {
			$envContent = self::addNewLine($envContent);
			
			$value = str_starts_with($value, '#') ? $value : "# {$value}";
			$envContent .= "{$value}\n";
			
			return $envContent;
		}
		
		// Wrap the value if needed
		if (in_array($key, self::$wrappedKeys)) {
			$value = trim($value, '"');
			$value = '"' . $value . '"';
		}
		
		// Pattern to match the existing variable
		$pattern = "/^" . preg_quote($key, '/') . "=(.*)$/m";
		
		// Check if the variable already exists
		if (preg_match($pattern, $envContent)) {
			// If exists, replace the existing value with the new one
			$envContent = preg_replace($pattern, "{$key}={$value}", $envContent);
		} else {
			$envContent = self::addNewLine($envContent);
			
			// If it does not exist, append the new variable at the end
			$envContent .= "{$key}={$value}\n";
			
			// Replace three or more newlines with two newlines
			$envContent = reduceConsecutiveChar($envContent, "\n", 2);
		}
		
		return getAsString($envContent);
	}
	
	/**
	 * Add empty line to the .env file
	 *
	 * @param string $envContent
	 * @param bool $force
	 * @return string
	 */
	private static function addNewLine(string $envContent, bool $force = false): string
	{
		// Check if the file ends with a newline character
		$endsWithNewline = preg_match('/\n$/', $envContent);
		
		// If file does not end with a newline, add a newline
		if ($force || !$endsWithNewline) {
			$envContent .= "\n";
		}
		
		return $envContent;
	}
	
	/**
	 * Check if the file is writable. If the file doesn't exist,
	 * Check if the parent directory is writable, so the file can be created.
	 *
	 * @param string $filePath
	 * @return void
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	private static function ensureFileIsWritable(string $filePath): void
	{
		if ((is_file($filePath) && !is_writable($filePath)) || (!is_file($filePath) && !is_writable(dirname($filePath)))) {
			// $message = sprintf('Unable to write to the file at %s.', $filePath);
			$message = sprintf('The %s file is not writable.', $filePath);
			throw new CustomException($message);
		}
	}
}
