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

class UrlQuery
{
	protected ?string $url;
	protected array $parsedUrl;
	protected array $parameters;
	protected array $numericParameters = ['distance'];
	
	/**
	 * @param string|null $url
	 * @param array $parameters
	 * @param null $secure
	 */
	public function __construct(?string $url = null, array $parameters = [], $secure = null)
	{
		// Get URL (Accepts URL & URI|Path)
		$this->url = !empty($url)
			? str_starts_with(mb_strtolower($url), 'http') ? $url : url($url, $parameters, $secure)
			: request()->fullUrl();
		
		// Get parsed URL
		$parsedUrl = mb_parse_url($this->url);
		$this->parsedUrl = is_array($parsedUrl) ? $parsedUrl : [];
		
		// Get query parameters
		$this->parameters = [];
		if (isset($this->parsedUrl['query'])) {
			mb_parse_str($this->parsedUrl['query'], $this->parameters);
		}
		$this->parameters = array_merge($this->parameters, $parameters);
		
		// Remove all empty query parameters
		$this->removeEmptyParameters();
		
		// In addition,
		// Remove the country parameter when the DomainMapping plugin is installed
		if (config('plugins.domainmapping.installed')) {
			$this->removeParameters(['country']);
		}
	}
	
	/**
	 * Set (add or update) the given query parameters
	 *
	 * @param array<string, string|array> $parameters
	 * @return $this
	 */
	public function setParameters(array $parameters): static
	{
		foreach ($parameters as $key => $value) {
			Arr::set($this->parameters, $key, $value);
		}
		
		// Remove all empty query parameters
		$this->removeEmptyParameters();
		
		return $this;
	}
	
	/**
	 * Get all the query parameters
	 *
	 * @return array<string, string|array>
	 */
	public function getParameters(): array
	{
		return $this->parameters;
	}
	
	/**
	 * Get query parameters by excluding some ones
	 *
	 * @param array<int, string> $parameters
	 * @return array<string, string|array>
	 */
	public function getParametersExcluding(array $parameters): array
	{
		$filteredParameters = $this->parameters;
		
		foreach ($parameters as $parameter) {
			Arr::forget($filteredParameters, $parameter);
		}
		
		return $filteredParameters;
	}
	
	/**
	 * Get specific query parameters (if they exist)
	 *
	 * @param array<int, string> $parameters
	 * @return array<string, string|array>
	 */
	public function getSpecificParameters(array $parameters): array
	{
		$result = [];
		foreach ($parameters as $parameter) {
			$value = Arr::get($this->parameters, $parameter);
			if ($value !== null) {
				Arr::set($result, $parameter, $value);
			}
		}
		
		return $result;
	}
	
	/**
	 * Remove some query parameters
	 *
	 * @param array<int, string> $parameters
	 * @return $this
	 */
	public function removeParameters(array $parameters): static
	{
		// Remove empty elements
		$parameters = array_filter($parameters);
		
		// Remove the parameters
		foreach ($parameters as $parameter) {
			Arr::forget($this->parameters, $parameter);
		}
		
		return $this;
	}
	
	/**
	 * Remove all the query parameters which value is empty
	 *
	 * @return void
	 */
	protected function removeEmptyParameters(): void
	{
		$this->parameters = $this->removeEmptyRecursive($this->parameters);
	}
	
	/**
	 * Remove all empty query parameters recursively
	 *
	 * @param array $array
	 * @return array
	 */
	protected function removeEmptyRecursive(array $array): array
	{
		return array_filter($array, function ($value, $key) {
			if (is_array($value)) {
				$value = $this->removeEmptyRecursive($value);
			}
			
			return (in_array($key, $this->numericParameters))
				? !empty($value) || $value == 0
				: !empty($value);
		}, ARRAY_FILTER_USE_BOTH);
	}
	
	/**
	 * Remove all the query parameters
	 *
	 * @return $this
	 */
	public function removeAllParameters(): static
	{
		$this->parameters = [];
		
		return $this;
	}
	
	/**
	 * Build new URL with the updated query parameters
	 *
	 * @return string
	 */
	public function buildUrl(): string
	{
		$newQueryString = Arr::query($this->parameters);
		$modifiedUrl = $this->parsedUrl['scheme'] . '://' . $this->parsedUrl['host'];
		
		if (isset($this->parsedUrl['port'])) {
			$modifiedUrl .= ':' . $this->parsedUrl['port'];
		}
		
		if (isset($this->parsedUrl['path'])) {
			$modifiedUrl .= $this->parsedUrl['path'];
		}
		
		if ($newQueryString) {
			$modifiedUrl .= '?' . $newQueryString;
		}
		
		if (isset($this->parsedUrl['fragment'])) {
			$modifiedUrl .= '#' . $this->parsedUrl['fragment'];
		}
		
		return $modifiedUrl;
	}
	
	/**
	 * @return string
	 */
	public function toString(): string
	{
		return $this->buildUrl();
	}
	
	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->buildUrl();
	}
}
