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
use App\Helpers\Cookie;
use App\Helpers\DotenvEditor;
use App\Helpers\Ip;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Prologue\Alerts\Facades\Alert;

/**
 * Get the current route action
 * This function prevents null return of the core function
 *
 * @return string
 */
function currentRouteAction(): string
{
	$value = Route::currentRouteAction();
	
	return getAsString($value);
}

/**
 * @param string $string
 * @return string
 */
function currentRouteActionContains(string $string): string
{
	return str_contains(currentRouteAction(), $string);
}

/**
 * @param int $index
 * @param string|null $default
 * @return string|null
 */
function getRequestSegment(int $index, ?string $default = null): ?string
{
	if (!isFromApi()) {
		return request()->segment($index, $default);
	}
	
	if (isApiCurlRequestsEnabled()) {
		return request()->segment($index, $default);
	}
	
	$requestUri = request()->server('REQUEST_URI');
	
	return getUrlSegment($requestUri, $index, $default);
}

/**
 * Get a specific segment from the URL.
 *
 * Note: For accurate result, make sure that $requestUri starts by "/"
 * or $requestUri is a valid URL starting by the "http(s)://" protocol
 *
 * @param string $requestUri
 * @param int $index
 * @param string|null $default
 * @return string|null
 */
function getUrlSegment(string $requestUri, int $index, ?string $default = null): ?string
{
	$requestUri = trim(strtolower($requestUri));
	
	// Normalize the URL by removing query string if present
	$requestUri = str_starts_with($requestUri, 'http')
		? urlQuery($requestUri)->removeAllParameters()->toString()
		: parse_url($requestUri, PHP_URL_PATH);
	
	// Find the right $index value
	if (str_starts_with($requestUri, 'http')) {
		// When $requestUri starts by the "http(s)://" protocol
		$index = $index + 2;
	} else {
		if (!str_starts_with($requestUri, '/')) {
			// $requestUri starts by the first segment,
			// so we need to consider that segments keys stars by 0 (i.e. $index - 1)
			$index = $index - 1;
		}
	}
	
	// Split into segments
	$segments = explode('/', $requestUri);
	$value = $segments[$index] ?? $default;
	
	return getAsStringOrNull($value);
}

/**
 * Check if a model has translation columns
 *
 * @param $model
 * @return bool
 */
function isTranslatableModel($model): bool
{
	$isTranslatable = false;
	
	try {
		if (!($model instanceof Model)) {
			return false;
		}
		
		$isTranslatableModel = (
			property_exists($model, 'translatable')
			&& !empty($model->translatable)
		);
		
		if ($isTranslatableModel) {
			$isTranslatable = true;
		}
	} catch (\Throwable $e) {
		return false;
	}
	
	return $isTranslatable;
}

/**
 * Check if a model's column is translatable
 *
 * @param $model
 * @param string|\Closure $column
 * @return bool
 */
function isTranslatableColumn($model, string|\Closure $column): bool
{
	if (!is_string($column)) return false;
	
	return (isTranslatableModel($model) && in_array($column, $model->translatable));
}

/**
 * Default translator (e.g. en/global.php)
 *
 * @param string|null $key
 * @param array $replace
 * @param string $file
 * @param string|null $locale
 * @return array|\Illuminate\Contracts\Translation\Translator|string|null
 */
function t(string $key = null, array $replace = [], string $file = 'global', string $locale = null)
{
	if (is_null($locale)) {
		$locale = config('app.locale');
	}
	
	return trans($file . '.' . $key, $replace, $locale);
}

/**
 * Generate new App Key using Artisan
 *
 * Note:
 * - The APP_KEY variable in the /.env will be updated
 * - The App Key mays be regenerated (or updated) for security reasons
 * - Generating a new App Key using artisan invalidates all the sessions and cookies
 *
 * @return void
 */
function updateAppKeyWithArtisan(): void
{
	try {
		// Generating a new App Key, removes|clears
		// (or invalidates) all the sessions and cookies
		Artisan::call('key:generate', ['--force' => true]);
	} catch (\Throwable $e) {
	}
}

/**
 * Update the App Key without Artisan
 *
 * @param bool $clearCookies
 * @return void
 * @throws \App\Exceptions\Custom\CustomException
 */
function updateAppKeyWithoutArtisan(bool $clearCookies = false): void
{
	// Clear all cookies
	if ($clearCookies) {
		Cookie::forgetAll();
	}
	
	$appKey = generateAppKey();
	DotenvEditor::setKey('APP_KEY', $appKey);
	DotenvEditor::save();
}

/**
 * Generate a new App Key (with base64 of a random string)
 *
 * @return string
 */
function generateAppKey(): string
{
	$base64RandomString = base64_encode(createRandomString(32));
	
	return 'base64:' . $base64RandomString;
}

/**
 * @param string|null $defaultIp
 * @return string
 */
function getIp(?string $defaultIp = ''): string
{
	return Ip::get($defaultIp);
}

/**
 * Get host (domain with subdomain)
 *
 * @param string|null $url
 * @return string
 */
function getHost(string $url = null): string
{
	if (!empty($url)) {
		$host = parse_url($url, PHP_URL_HOST);
	} else {
		$host = (trim(request()->server('HTTP_HOST')) != '') ? request()->server('HTTP_HOST') : ($_SERVER['HTTP_HOST'] ?? '');
	}
	
	if ($host == '') {
		$host = parse_url(url()->current(), PHP_URL_HOST);
	}
	
	return getAsString($host);
}

/**
 * Get domain (host without a subdomain)
 *
 * @param string|null $url
 * @return string
 */
function getDomain(string $url = null): string
{
	if (!empty($url)) {
		$host = parse_url($url, PHP_URL_HOST);
	} else {
		$host = getHost();
	}
	
	$tmp = explode('.', $host);
	if (count($tmp) > 2) {
		$itemsToKeep = count($tmp) - 2;
		$tldArray = getTopLevelDomainRefList();
		if (isset($tmp[$itemsToKeep]) && isset($tldArray[$tmp[$itemsToKeep]])) {
			$itemsToKeep = $itemsToKeep - 1;
		}
		for ($i = 0; $i < $itemsToKeep; $i++) {
			Arr::forget($tmp, $i);
		}
		$domain = implode('.', $tmp);
	} else {
		$domain = @implode('.', $tmp);
	}
	
	return $domain;
}

/**
 * Get subdomain name
 *
 * NOTE:
 * The subdomains of the fetched subdomain are not retrieved
 * Example: xxx.yyy.zzz.foo.com, only "xxx" will be retrieved
 *
 * @return string
 */
function getSubDomainName(): string
{
	$host = getHost();
	
	return (substr_count($host, '.') > 1) ? trim(current(explode('.', $host))) : '';
}

/**
 * @return string
 */
function getCookieDomain(): string
{
	$host = getHost();
	$array = mb_parse_url($host);
	
	return (is_array($array) && !empty($array['path']))
		? $array['path']
		: $host;
}

/**
 * Check local environment
 *
 * @param string|null $url
 * @return bool
 */
function isLocalEnv(string $url = null): bool
{
	if (empty($url)) {
		$url = config('app.url');
	}
	
	return (
		str_contains($url, '127.0.0.1')
		|| str_contains($url, '::1')
		|| (!str_contains($url, '.'))
		|| str_ends_with(getDomain($url), '.local')
		|| str_ends_with(getDomain($url), '.localhost')
	);
}

/**
 * Check tld is a valid tld
 *
 * @param string|null $url
 * @return bool
 */
function checkTld(?string $url): bool
{
	if (empty($url)) {
		return false;
	}
	
	$parsedUrl = parse_url($url);
	if ($parsedUrl === false) {
		return false;
	}
	
	$tldArray = getTopLevelDomainRefList();
	$patten = implode('|', array_keys($tldArray));
	
	$matched = preg_match('/\.(' . $patten . ')$/i', $parsedUrl['host']);
	
	return (bool)$matched;
}

/**
 * Json To Array
 * NOTE: Used for MySQL Json and Laravel array (casts) columns
 *
 * @param $string
 * @return array
 */
function jsonToArray($string): array
{
	if (is_array($string)) {
		return $string;
	}
	
	if (is_object($string)) {
		return Arr::fromObject($string);
	}
	
	if (isJson($string)) {
		$array = json_decode($string, true);
		// If the JSON was encoded in JSON by mistake
		if (!is_array($array)) {
			return jsonToArray($array);
		}
	} else {
		$array = [];
	}
	
	return $array;
}

/**
 * Check if variable contains (valid) JSON data
 *
 * @param $string
 * @return bool
 */
function isJson($string): bool
{
	return (is_string($string) && str($string)->isJson());
}

/**
 * @return string
 */
function vTime(): string
{
	$timeStamp = '?v=' . time();
	if (app()->environment(['staging', 'production'])) {
		$timeStamp = '';
	}
	
	return $timeStamp;
}

/**
 * Get the app's possible URL base
 *
 * @return string
 */
function getRawBaseUrl(): string
{
	// Get the Laravel app public path name
	$publicPathName = basename(rtrim(public_path(), '/'));
	
	// Get the HTTPS value
	$https = request()->server('HTTPS');
	$https = is_string($https) ? strtolower($https) : $https;
	$protocol = ($https !== 'off') ? 'https' : 'http';
	
	// Get the HTTP_HOST value
	$httpHost = trim(request()->server('HTTP_HOST'));
	$httpHost = rtrim($httpHost, '/');
	
	// Get the REQUEST_URI value
	$requestUri = trim(request()->server('REQUEST_URI'));
	$requestUri = strtok($requestUri, '?');
	$requestUri = trim($requestUri, '/');
	$requestUri = str_starts_with($requestUri, $publicPathName)
		? '/' . $publicPathName
		: '';
	
	// Get the base URL of the current URL
	$baseUrl = $protocol . '://' . $httpHost . $requestUri;
	
	// Fixing the base URL from admin or install
	$baseUrl = head(explode('/' . admin_uri(), $baseUrl));
	$baseUrl = head(explode('/install', $baseUrl));
	
	return rtrim($baseUrl, '/');
}

/**
 * Get the current request path by pattern
 *
 * @param string|null $pattern
 * @return string
 */
function getRequestPath(string $pattern = null): string
{
	if (empty($pattern)) {
		return request()->path();
	}
	
	$pattern = '#(' . $pattern . ')#ui';
	
	$matches = [];
	preg_match($pattern, request()->path(), $matches);
	
	return (!empty($matches[1])) ? $matches[1] : request()->path();
}

/**
 * Get random password
 *
 * @param int $length
 * @return string
 */
function getRandomPassword(int $length): string
{
	$allowedCharacters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!$%^&#!$%^&#';
	$random = str_shuffle($allowedCharacters);
	$password = substr($random, 0, $length);
	
	if (empty($password)) {
		$password = Str::random($length);
	}
	
	return $password;
}

/**
 * Get a unique code
 *
 * @param int $limit
 * @return string
 */
function uniqueCode(int $limit): string
{
	$uniqueCode = substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, $limit);
	
	if (empty($uniqueCode)) {
		$uniqueCode = Str::random($limit);
	}
	
	return $uniqueCode;
}

/**
 * Get locale without codeset|encoding
 * Examples: de_CH.UTF-8 => de_CH, en_GB.ISO8859-15 => en_GB, ...
 *
 * @param string|null $locale
 * @param bool $nullable
 * @return string
 */
function removeLocaleCodeset(?string $locale, bool $nullable = true): string
{
	$default = 'en_US';
	$default = getAsString(config('app.locale'), $default);
	$default = $nullable ? null : (!str_contains($default, '.') ? $default : 'en_US');
	
	if (empty($locale)) {
		return getAsString($default);
	}
	
	if (str_contains($locale, '.')) {
		$tmp = explode('.', $locale);
		$locale = current($tmp);
	}
	
	return getAsString($locale, $default);
}

/**
 * Get locale without country code
 * Examples: de_CH => de, en_GB => en, ...
 *
 * @param string|null $locale
 * @param bool $nullable
 * @return string|null
 */
function getPrimaryLocaleCode(?string $locale, bool $nullable = true): ?string
{
	$default = $nullable ? null : 'en';
	
	$locale = removeLocaleCodeset($locale, $nullable);
	if (extension_loaded('intl') && class_exists('\Locale')) {
		return \Locale::getPrimaryLanguage($locale);
	}
	if (isRegionalLocaleCode($locale)) {
		$locale = str($locale)->substr(0, -3)->toString();
	}
	
	return getAsString($locale, $default);
}

/**
 * Get a language name from its ISO code
 * Examples: de => German, ro => Romanian, ...
 *
 * Note: Use the getRegionalLocaleName() function instead for more options
 *
 * @param string|null $code
 * @param bool $nullable
 * @return string|null
 */
function getPrimaryLocaleName(?string $code, bool $nullable = true): ?string
{
	$default = $nullable ? null : (!empty($code) ? $code : 'English');
	
	if (empty($code)) {
		return $default;
	}
	
	// Get language list
	$languages = getLanguageRefList();
	
	$code = getPrimaryLocaleCode($code, $nullable);
	$name = $languages[$code] ?? $default;
	
	return getAsString($name, $default);
}

/**
 * Get a language regional locale from its ISO code (or from its locale)
 * Examples: de => de_DE, ro => ro_RO, ...
 *
 * @param string|null $locale
 * @param bool $nullable
 * @return string|null
 */
function getRegionalLocaleCode(?string $locale, bool $nullable = true): ?string
{
	$default = $nullable ? null : (!empty($locale) ? $locale : 'en_US');
	
	if (empty($locale)) {
		return $default;
	}
	
	if (isRegionalLocaleCode($locale)) {
		return $locale;
	}
	
	// Get languages linked to their main country
	$isoLanguageCountries = getLanguagesLinkedToTheirMainCountry();
	
	if (!empty($isoLanguageCountries[$locale]['locale'])) {
		$locale = $isoLanguageCountries[$locale]['locale'];
	}
	
	return getAsString($locale, $default);
}

/**
 * Get a language regional locale name from its ISO code (or from its locale)
 * Examples: de_CH => German (Switzerland), en_GB => English (United Kingdom), ...
 *
 * @param string|null $locale
 * @param bool $nullable
 * @return string|null
 */
function getRegionalLocaleName(?string $locale, bool $nullable = true): ?string
{
	if (!isRegionalLocaleCode($locale)) {
		return getPrimaryLocaleName($locale, $nullable);
	}
	
	// Get localized locale
	$locale = getRegionalLocaleCode($locale, $nullable);
	
	// Get locales with name
	$localesWithName = getLocalesWithName('merged', false);
	
	$name = $localesWithName[$locale] ?? $locale;
	
	return getAsString($name, $locale);
}

/**
 * Check if a language ISO code (or locale) is regional
 * Examples: de_CH => true, de => false, ...
 *
 * @param string|null $locale
 * @return bool
 */
function isRegionalLocaleCode(?string $locale): bool
{
	if (empty($locale)) {
		return false;
	}
	
	if (extension_loaded('intl') && class_exists('\Locale')) {
		return !empty(\Locale::getRegion($locale));
	}
	$countryCodeList = array_keys(getCountryRefList());
	$tmp = explode('_', $locale);
	$countryCode = end($tmp);
	
	return in_array($countryCode, $countryCodeList);
}

if (!function_exists('getLangTag')) {
	/**
	 * Get locale's language tag
	 * Example: en-US, pt-BR, fr-CA, ... (Usage of "-" instead of "_")
	 *
	 * The language tag syntax is defined by the IETF's BCP 47
	 * Info: https://www.w3.org/International/articles/language-tags/
	 *
	 * @param string|null $locale
	 * @param bool $nullable
	 * @return string|null
	 */
	function getLangTag(?string $locale = null, bool $nullable = true): ?string
	{
		$default = 'en-US';
		$default = getAsString(config('app.locale'), $default);
		$default = $nullable ? null : (!str_contains($default, '_') ? $default : 'en');
		
		if (empty($locale)) {
			return $default;
		}
		
		$locale = str_replace('_', '-', $locale);
		$locale = getAsString($locale, $default);
		
		return removeLocaleCodeset($locale, $nullable);
	}
}

/**
 * @param string $url
 * @param string $string
 * @param int $length
 * @param string $attributes
 * @return string
 */
function linkStrLimit(string $url, string $string, int $length = 0, string $attributes = ''): string
{
	if (!is_string($attributes)) {
		$attributes = '';
	}
	
	if (!empty($attributes)) {
		$attributes = ' ' . $attributes;
	}
	
	$tooltip = '';
	if (is_numeric($length) && $length > 0 && str($string)->length() > $length) {
		$tooltip = ' data-bs-toggle="tooltip" title="' . $string . '"';
	}
	
	$out = '<a href="' . $url . '"' . $attributes . $tooltip . '>';
	if ($length > 0) {
		$out .= str($string)->limit($length);
	} else {
		$out .= $string;
	}
	$out .= '</a>';
	
	return $out;
}

/**
 * Get Translation from Column (from Json, Array or String)
 *
 * @param $column
 * @param string|null $locale
 * @return string
 */
function getColumnTranslation($column, string $locale = null): string
{
	if (empty($locale)) {
		$locale = app()->getLocale();
	}
	
	if (!is_array($column)) {
		if (isJson($column)) {
			$column = json_decode($column, true);
		} else {
			$column = [$column];
		}
	}
	
	$fallbackLocale = config('app.fallback_locale');
	$translation = $column[$locale] ?? ($column[$fallbackLocale] ?? head($column));
	
	return getAsString($translation);
}

/**
 * Convert a full path to a relative path
 * Old name: relativeAppPath
 *
 * @param string|null $path
 * @return string
 */
function getRelativePath(?string $path): string
{
	$documentRoot = request()->server('DOCUMENT_ROOT');
	$path = str_replace($documentRoot, '', $path);
	
	$basePath = base_path();
	$path = str_replace($basePath, '', $path);
	
	return (!empty($path) && is_string($path)) ? $path : '/';
}

/**
 * Parse the HTTP Accept-Language header
 * NOTE: Get the preferred language: $firstKey = array_key_first($array);
 *
 * @param string|null $acceptLanguage
 * @return array
 */
function parseAcceptLanguageHeader(string $acceptLanguage = null): array
{
	if (empty($acceptLanguage)) {
		$acceptLanguage = request()->server('HTTP_ACCEPT_LANGUAGE');
	}
	
	$acceptLanguageTab = explode(',', $acceptLanguage);
	
	$array = [];
	if (!empty($acceptLanguageTab)) {
		foreach ($acceptLanguageTab as $key => $value) {
			$tmp = explode(';', $value);
			if (empty($tmp)) continue;
			
			if (isset($tmp[0]) && isset($tmp[1])) {
				$q = str_replace('q=', '', $tmp[1]);
				$array[$tmp[0]] = (double)$q;
			} else {
				$array[$tmp[0]] = 1;
			}
		}
	}
	arsort($array);
	
	return $array;
}

/**
 * During a cURL request (using the Laravel HTTP Client),
 * Should the request be retried?
 *
 * Note:
 * - The initial request encounters can be a ConnectionException, then the request can be retried.
 * - The request can also be retried, for GET request, when the exception error contains:
 *   "cURL error 28: Connection timed out after {x} milliseconds"
 * - Don't retry in the other cases
 * - More info: https://laravel.com/docs/master/http-client#retries
 *
 * @param \Exception $e
 * @param \Illuminate\Http\Client\PendingRequest $request
 * @param string|null $method
 * @return bool
 */
function shouldHttpRequestBeRetried(Exception $e, PendingRequest $request, ?string $method = null): bool
{
	// cURL error found
	$msg = $e->getMessage();
	$isHttpGetRequest = (!empty($method) && strtolower($method) == 'get');
	$isTimeoutError = (str_contains($msg, 'cURL') && str_contains($msg, 'Connection'));
	$isTimeoutError = ($isTimeoutError && $isHttpGetRequest);
	
	// Connection exception encountered
	$isConnectionException = ($e instanceof ConnectionException);
	
	return ($isConnectionException || $isTimeoutError);
}

/**
 * Parse and get error from HTTP client request's exception or response as string
 *
 * @param $exceptionOrResponse
 * @return string
 */
function parseHttpRequestError($exceptionOrResponse): string
{
	if (is_string($exceptionOrResponse)) {
		return $exceptionOrResponse;
	}
	
	$message = null;
	
	if (
		$exceptionOrResponse instanceof Throwable
		&& method_exists($exceptionOrResponse, 'getMessage')
	) {
		$message = $exceptionOrResponse->getMessage();
	}
	
	if ($exceptionOrResponse instanceof \Illuminate\Http\Client\Response) {
		$responseErrorMessage = null;
		
		if (method_exists($exceptionOrResponse, 'reason')) {
			try {
				$responseErrorMessage = $exceptionOrResponse->reason();
			} catch (\Exception $e) {
			}
		}
		if (empty($responseErrorMessage)) {
			if (method_exists($exceptionOrResponse, 'json')) {
				try {
					$responseErrorMessage = $exceptionOrResponse->json();
				} catch (\Exception $e) {
				}
			}
		}
		if (empty($responseErrorMessage)) {
			if (method_exists($exceptionOrResponse, 'body')) {
				try {
					$responseErrorMessage = $exceptionOrResponse->body();
				} catch (\Exception $e) {
				}
			}
		}
		if (!empty($responseErrorMessage)) {
			$message = $responseErrorMessage;
		}
	}
	
	if (is_array($message)) {
		$message = json_encode($message);
	}
	if (is_string($message)) {
		$message = strip_tags($message);
	}
	if (empty($message) || !is_string($message)) {
		$message = 'Failed to get the request\'s data.';
	}
	
	return $message;
}

/**
 * @return array
 */
function getHttpStatusCodes(): array
{
	$statusTexts = \Illuminate\Http\Response::$statusTexts;
	$statusTexts[419] = getHttp419ExceptionMessage();
	
	return $statusTexts;
}

/**
 * @param $status
 * @return bool
 */
function isValidHttpStatus(&$status): bool
{
	$requestedStatus = $status;
	
	if (empty($requestedStatus)) return false;
	
	$requestedStatus = getAsInt($requestedStatus);
	$isValid = array_key_exists($requestedStatus, getHttpStatusCodes());
	
	if ($isValid) {
		$status = $requestedStatus;
	}
	
	return $isValid;
}

/**
 * @param $status
 * @return string
 */
function getHttpStatusMessage($status): string
{
	$default = 'Unknown status text';
	
	if (!isValidHttpStatus($status)) {
		return $default;
	}
	
	$message = getHttpStatusCodes()[$status];
	
	return getAsString($message, $default);
}

/**
 * @param \Illuminate\Http\Request|null $request
 * @return string
 */
function getHttp419ExceptionMessage(?Request $request = null): string
{
	if (is_null($request)) {
		$request = request();
	}
	
	$message = (isFromApi($request) || isFromAjax($request))
		? t('page_expired_reload_needed')
		: t('page_expired');
	
	return getAsString($message);
}

/**
 * Check if the current request is from an AJAX call
 *
 * @param \Illuminate\Http\Request|null $request
 * @return bool
 */
function isFromAjax(?Request $request = null): bool
{
	if (!$request instanceof Request) {
		$request = request();
	}
	
	return ($request->ajax() || $request->wantsJson());
}

/**
 * @param string|null $charset
 * @return bool
 */
function isCharsetConflictFound(?string $charset = null): bool
{
	if (empty($charset)) {
		$charset = config('larapen.core.charset', 'utf-8');
	}
	
	$systemCharset = @ini_get('default_charset');
	$systemCharset = is_string($systemCharset) ? $systemCharset : '';
	
	return (strtolower($charset) != strtolower($systemCharset));
}

/**
 * Add Content-Type Header (Only if missing)
 *
 * @param string $type
 * @param array|null $headers
 * @return array
 */
function addContentTypeHeader(string $type, ?array $headers = []): array
{
	$headers = is_array($headers) ? $headers : [];
	
	$charset = config('larapen.core.charset', 'utf-8');
	$defaultHeaders = ['Content-Type' => $type . '; charset=' . strtoupper($charset)];
	
	return array_merge($defaultHeaders, $headers);
}

/**
 * @param array|null $referrers
 * @param bool $nullable
 * @return bool
 */
function isFromValidReferrer(?array $referrers = [], bool $nullable = false): bool
{
	if (empty($referrers)) {
		$referrers = [getUrlHost(url('/'))];
	}
	
	$isFromValidReferrer = false;
	
	$httpReferrer = request()->server('HTTP_REFERER');
	if ($nullable && empty($httpReferrer)) {
		return true;
	}
	
	foreach ($referrers as $referrer) {
		$isPattern = (
			str_contains($referrer, 'https?')
			|| str_contains($referrer, '.*')
			|| str_contains($referrer, '\.')
		);
		
		// Check to see what the referrer is
		$isFromValidReferrer = $isPattern
			? preg_match('|' . $referrer . '|ui', $httpReferrer)
			: str_contains($httpReferrer, $referrer);
		if ($isFromValidReferrer) {
			break;
		}
	}
	
	return $isFromValidReferrer;
}

function isSettingsAppDarkModeEnabled(): bool
{
	return (config('settings.app.dark_mode') == '1');
}

function isDarkModeEnabledForCurrentUser(): bool
{
	return (
		isSettingsAppDarkModeEnabled()
		&& auth()->check()
		&& isset(auth()->user()->dark_mode)
		&& auth()->user()->dark_mode == 1
	);
}

function isDarkModeEnabledForCurrentDevice(): bool
{
	return (
		isSettingsAppDarkModeEnabled()
		&& Cookie::has('darkTheme')
		&& Cookie::get('darkTheme') == 'dark'
	);
}

/**
 * Flash message notification
 *
 * @param string|null $message
 * @param string|null $level
 * @param string|null $currentUrl
 * @param \Illuminate\Http\Request|null $request
 * @return void
 */
function notification(
	?string  $message = null,
	?string  $level = 'info',
	?string  $currentUrl = null,
	?Request $request = null
): void
{
	if (isFromApi($request) || isFromAjax($request)) return;
	if (empty($message)) return;
	
	$level = !empty($level) ? $level : 'info';
	try {
		if (isAdminPanel($currentUrl)) {
			// Levels: success, error, warning, info
			Alert::$level($message)->flash();
		} else {
			// Levels: info, success, error, warning
			flash($message)->$level();
		}
	} catch (\Throwable $e) {
	}
}

/**
 * Check if the class uses a specific trait (recursively or not)
 *
 * Note:
 * The PHP class_uses() only checks the immediate traits used by the class,
 * not those used by parent classes. Make it recursive can take
 * to account traits used by parent classes, we need to iterate through the class hierarchy.
 *
 * @param $class
 * @param $trait
 * @param bool $recursively
 * @return bool
 */
function doesClassUse($class, $trait, bool $recursively = false): bool
{
	if (is_string($class)) {
		$class = str($class)->start('\\')->toString();
	}
	
	if (!is_object($class) && (is_string($class) && !class_exists($class))) {
		return false;
	}
	
	if ($recursively) {
		try {
			$reflectionClass = new \ReflectionClass($class);
			while ($reflectionClass) {
				$traits = $reflectionClass->getTraitNames();
				if (in_array($trait, $traits)) {
					return true;
				}
				
				$reflectionClass = $reflectionClass->getParentClass();
			}
		} catch (\Throwable $e) {
		}
		
		return false;
	}
	
	return in_array($trait, class_uses($class));
}

/**
 * Check if method exists in a class, and if it is a static method
 *
 * @param $class
 * @param string $method
 * @return bool
 */
function staticMethodExists($class, string $method): bool
{
	if (is_string($class)) {
		$class = str($class)->start('\\')->toString();
	}
	
	if (!is_object($class) && (is_string($class) && !class_exists($class))) {
		return false;
	}
	
	if (!method_exists($class, $method)) {
		return false;
	}
	
	try {
		$reflectionMethod = new \ReflectionMethod($class, $method);
		
		return $reflectionMethod->isStatic();
	} catch (\Throwable $e) {
	}
	
	return false;
}

/**
 * Check if Laravel class use the Macroable trait
 *
 * @param $class
 * @return bool
 */
function isMacroable($class): bool
{
	$trait = \Illuminate\Support\Traits\Macroable::class;
	
	$usesMacroableTrait = doesClassUse($class, $trait);
	$macroFunctionExists = staticMethodExists($class, 'macro');
	$isMacroable = ($usesMacroableTrait && $macroFunctionExists);
	
	$macroableCanBeBypassed = false;
	if (class_exists($class)) {
		/*
		 * For now, no way to verify if these Laravel classes uses the framework Macroable trait (recursively)
		 * or if these Laravel classes have a macro() function
		 */
		$bypassMacroableCheckFor = [
			'\Illuminate\Database\Eloquent\Builder',
		];
		
		try {
			$reflectionClass = new ReflectionClass($class);
			$namespace = $reflectionClass->getNamespaceName();
			$className = $reflectionClass->getShortName();
			$classFullName = $namespace . '\\' . $className;
			$classFullName = str($classFullName)->start('\\')->toString();
			
			$macroableCanBeBypassed = in_array($classFullName, $bypassMacroableCheckFor);
		} catch (\Throwable $e) {
		}
	}
	
	return ($isMacroable || $macroableCanBeBypassed);
}

/**
 * @param string|null $color
 * @return string|null
 */
function getHtmlColor(?string $color): ?string
{
	if (empty($color)) return $color;
	
	$color = str($color);
	$color = isHexColor($color) ? $color->start('#') : $color->ltrim('#');
	
	return $color->toString();
}
