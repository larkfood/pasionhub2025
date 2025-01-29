<?php

if (!function_exists('captcha')) {
	/**
	 * @param string $config
	 * @param bool $api
	 * @return string|array
	 */
	function captcha(string $config = 'default', bool $api = false): string|array
	{
		return app('captcha')->create($config, $api);
	}
}

if (!function_exists('captcha_src')) {
	/**
	 * @param string $config
	 * @param bool $api
	 * @return string
	 */
	function captcha_src(string $config = 'default', bool $api = false): string
	{
		return app('captcha')->src($config, $api);
	}
}

if (!function_exists('captcha_img')) {
	
	/**
	 * @param string $config
	 * @return string
	 */
	function captcha_img(string $config = 'default'): string
	{
		return app('captcha')->img($config);
	}
}

if (!function_exists('captcha_check')) {
	/**
	 * @param string $value
	 * @return bool
	 */
	function captcha_check(string $value): bool
	{
		return app('captcha')->check($value);
	}
}

if (!function_exists('captcha_api_check')) {
	/**
	 * @param string $value
	 * @param string $key
	 * @param string $config
	 * @return bool
	 */
	function captcha_api_check(string $value, string $key, string $config = 'default'): bool
	{
		return app('captcha')->checkApi($value, $key, $config);
	}
}
