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

namespace App\Http\Middleware;

use App\Exceptions\Custom\CustomException;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ScribeUpdater
{
	/**
	 * Handle an incoming request.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure $next
	 * @return mixed
	 */
	public function handle(Request $request, Closure $next)
	{
		if ($request->segment(1) == 'docs' && $request->segment(2) == 'api') {
			$baseUrl = getAsStringOrNull(url('/'));
			$appApiToken = getAsStringOrNull(config('larapen.core.api.token'));
			
			$this->updateScribeViewFile($baseUrl, $appApiToken);
		}
		
		return $next($request);
	}
	
	/**
	 * Update the Scribe (API docs) view file
	 *
	 * @param string|null $baseUrl
	 * @param string|null $appApiToken
	 * @return void
	 */
	private function updateScribeViewFile(?string $baseUrl = null, ?string $appApiToken = null): void
	{
		if (isDemoDomain()) return;
		
		try {
			$path = resource_path('views/scribe/index.blade.php');
			
			$buffer = null;
			if (File::exists($path)) {
				$buffer = File::get($path);
			}
			
			if (!empty($buffer)) {
				/*
				 * Update the Scribe's base URL JS variable value in the Scribe view file
				 * Examples:
				 * - In new version: var tryItOutBaseUrl = "website-url";
				 * - In old version: var baseUrl = "website-url";
				 */
				$this->updateScribeAppUrl($path, $buffer, $baseUrl, 'tryItOutBaseUrl');
				$this->updateScribeAppUrl($path, $buffer, $baseUrl, 'baseUrl');
				
				// Update the API Token value in the 'try it out' calls
				// in the Scribe view file, to use the current website own API Token
				$this->updateScribeAppApiToken($path, $buffer, $appApiToken);
				
				unset($buffer);
			}
		} catch (\Throwable $e) {
			logger()->error(getExceptionMessage($e));
		}
	}
	
	/**
	 * Update the Scribe's base URL JS variable value
	 *
	 * @param string $path
	 * @param string|null $buffer
	 * @param string|null $baseUrl
	 * @param string|null $scribeJsBaseUrlVarName
	 * @return void
	 */
	private function updateScribeAppUrl(
		string  $path,
		?string &$buffer,
		?string $baseUrl = null,
		?string $scribeJsBaseUrlVarName = null
	): void
	{
		if (empty($buffer) || !is_string($buffer)) return;
		
		$baseUrl = !empty($baseUrl) ? $baseUrl : url('/');
		$isValidBaseUrl = str_starts_with(strtolower($baseUrl), 'http');
		if (!$isValidBaseUrl) return;
		
		$scribeJsBaseUrlVarName = !empty($scribeJsBaseUrlVarName) ? $scribeJsBaseUrlVarName : 'tryItOutBaseUrl';
		$scribeJsBaseUrlVarName = preg_quote($scribeJsBaseUrlVarName, '/');
		
		try {
			// Get the Scribe doc JavaScript's base URL
			$pattern = '/var\s+' . $scribeJsBaseUrlVarName . '\s+=\s+"([^"]+)"/i';
			preg_match($pattern, $buffer, $matches);
			$docBaseUrl = $matches[1] ?? null;
			
			$isValidDocBaseUrl = (!empty($docBaseUrl) && str_starts_with(strtolower($docBaseUrl), 'http'));
			if (!$isValidDocBaseUrl) return;
			
			if (str_ends_with($docBaseUrl, '/')) {
				$baseUrl = str($baseUrl)->finish('/')->toString();
			}
			
			if ($docBaseUrl != $baseUrl) {
				// Update the JavaScript's base URL
				$pattern = '/var\s+' . $scribeJsBaseUrlVarName . '\s+=\s+"[^"]+"/i';
				$replacement = 'var ' . $scribeJsBaseUrlVarName . ' = "' . $baseUrl . '"';
				$buffer = preg_replace($pattern, $replacement, $buffer);
				
				// Update the API base URL (Optional)
				$docApiBaseUrl = str($docBaseUrl)->finish('/')->append('api')->toString();
				$apiBaseUrl = str($baseUrl)->finish('/')->append('api')->toString();
				$buffer = str_replace($docApiBaseUrl, $apiBaseUrl, $buffer);
				
				// Update the app's base URL
				$docBaseUrl = str($docBaseUrl)->rtrim('/')->toString();
				$baseUrl = str($baseUrl)->rtrim('/')->toString();
				$buffer = str_replace($docBaseUrl, $baseUrl, $buffer);
				
				File::replace($path, $buffer);
			}
		} catch (\Throwable $e) {
			logger()->error(getExceptionMessage($e));
		}
	}
	
	/**
	 * Update the API Token value in the 'try it out' calls
	 *
	 * @param string $path
	 * @param string|null $buffer
	 * @param string|null $appApiToken
	 * @return void
	 */
	private function updateScribeAppApiToken(string $path, ?string &$buffer, ?string $appApiToken = null): void
	{
		if (empty($buffer) || !is_string($buffer)) return;
		
		$appApiToken = !empty($appApiToken) ? $appApiToken : config('larapen.core.api.token');
		if (empty($appApiToken)) return;
		
		try {
			// Get the API doc's app's API token
			$docAppApiToken = $this->getScribeDocApiToken($buffer);
			if (empty($docAppApiToken)) return;
			
			// Check if the API token found (in the doc buffer) is different to the website API token
			if ($docAppApiToken != $appApiToken) {
				$regexes = $this->getScribeDocApiTokenRegexes($appApiToken);
				if (empty($regexes)) return;
				
				foreach ($regexes as $regex) {
					try {
						$pattern = $regex['pattern'];
						$replacement = $regex['replacement'];
						if (!empty($pattern) && !empty($replacement)) {
							$buffer = preg_replace($pattern, $replacement, $buffer);
						}
					} catch (\Throwable $e) {
						logger()->error(getExceptionMessage($e));
					}
				}
				
				// Save the new buffer
				File::replace($path, $buffer);
			}
		} catch (\Throwable $e) {
			logger()->error(getExceptionMessage($e));
		}
	}
	
	/**
	 * Get the API doc's app's API token
	 *
	 * @param string|null $buffer
	 * @return string|null
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	private function getScribeDocApiToken(?string $buffer): ?string
	{
		if (empty($buffer) || !is_string($buffer)) return null;
		
		$docAppApiToken = null;
		
		$docAppApiTokensFound = [];
		$regexes = $this->getScribeDocApiTokenRegexes();
		if (!empty($regexes)) {
			foreach ($regexes as $regex) {
				try {
					$pattern = $regex['pattern'];
					$tokenIndex = $regex['tokenIndex'];
					if (empty($pattern) || empty($tokenIndex)) continue;
					
					preg_match_all($pattern, $buffer, $matches, PREG_PATTERN_ORDER);
					if (!empty($matches[$tokenIndex]) && is_array($matches[$tokenIndex])) {
						$docAppApiTokensFound = array_merge($docAppApiTokensFound, $matches[$tokenIndex]);
					}
				} catch (\Throwable $e) {
					logger()->error(getExceptionMessage($e));
				}
			}
		}
		
		if (!empty($docAppApiTokensFound)) {
			// Remove all duplicated token (including empty elements)
			$docAppApiTokensFound = array_unique($docAppApiTokensFound);
			
			// If, 1 unique and not empty token is found, then get it as the Scribe doc's API token
			if (count($docAppApiTokensFound) === 1) {
				$docAppApiToken = $docAppApiTokensFound[0] ?? null;
			} else {
				$error = 'Unique Scribe doc API token NOT found in: ' . json_encode($docAppApiTokensFound);
				throw new CustomException($error);
			}
		}
		
		return getAsString($docAppApiToken);
	}
	
	/**
	 * @param string|null $appApiToken
	 * @return array
	 */
	private function getScribeDocApiTokenRegexes(?string $appApiToken = null): array
	{
		$appApiToken = strval($appApiToken);
		
		return [
			'bash'        => [
				'pattern'     => '/"X-AppApiToken(\s*):(\s*)([^"]+)"/u',
				'tokenIndex'  => 3,
				'replacement' => '"X-AppApiToken$1:$2' . $appApiToken . '"',
			],
			'javascript'  => [
				'pattern'     => '/"X-AppApiToken"(\s*):(\s*)"([^"]+)"/u',
				'tokenIndex'  => 3,
				'replacement' => '"X-AppApiToken"$1:$2"' . $appApiToken . '"',
			],
			'php'         => [
				'pattern'     => "/'X-AppApiToken'(\s*)=&gt;(\s*)'([^']+)'/u",
				'tokenIndex'  => 3,
				'replacement' => "'X-AppApiToken'$1=&gt;$2'" . $appApiToken . "'",
			],
			'input'       => [
				'pattern'     => '/name="X-AppApiToken"([^>]+)value="([^"]*)"/u',
				'tokenIndex'  => 2,
				'replacement' => 'name="X-AppApiToken"$1value="' . $appApiToken . '"',
			],
			/*
			 * Impact of the s Modifier:
			 * - Without the s Modifier: The dot (.) matches any character except for newline characters.
			 * - With the s Modifier: The dot (.) matches any character, including newline characters.
			 * - The s modifier will not affect a matching behavior if there are no dot (.) characters in the pattern.
			 */
			'exampleCode' => [
				'pattern'     => '/name="X-AppApiToken"(.*?)<code>([^<]*)<\/code>/us',
				'tokenIndex'  => 2,
				'replacement' => 'name="X-AppApiToken"$1<code>' . $appApiToken . '</code>',
			],
		];
	}
}
