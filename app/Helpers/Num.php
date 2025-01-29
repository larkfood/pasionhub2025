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

/*
 * For some methods of this class,
 * the system locale need to be set in the 'AppServiceProvider'
 * by calling this method: systemLocale()->setLocale($locale);
 */

class Num
{
	/**
	 * Converts a number into a short version, eg: 1000 -> 1K
	 *
	 * Large number abbreviations:
	 * https://idlechampions.fandom.com/wiki/Large_number_abbreviations
	 *
	 * Note - PHP cannot handle large number like:
	 * Sextillion, Septillion, Octillion, Nonillion, Decillion, etc.
	 *
	 * @param float|int|string|null $value
	 * @param int $precision
	 * @param bool $roundThousands
	 * @return float|int|string
	 */
	public static function short(float|int|string|null $value, int $precision = 1, bool $roundThousands = true): float|int|string
	{
		if (empty($value)) return '0';
		if (!is_numeric($value)) return $value;
		
		$gap = 1000;
		$roundThousandsGap = $roundThousands ? 150 : 0;
		$multipliers = [
			[
				'multiplier' => 1,
				'suffix'     => '',
				'roundedGap' => 0,
			], // A hundred (or less) | 1 - 900
			[
				'multiplier' => $gap,
				'suffix'     => 'K',
				'roundedGap' => $roundThousandsGap,
			], // Thousands | 0.9K - 850K
			[
				'multiplier' => pow($gap, 2),
				'suffix'     => 'M',
				'roundedGap' => $roundThousandsGap * pow($gap, 1),
			], // Millions | 0.9M - 850M
			[
				'multiplier' => pow($gap, 3),
				'suffix'     => 'B',
				'roundedGap' => $roundThousandsGap * pow($gap, 2),
			], // Billions | 0.9B - 850B
			[
				'multiplier' => pow($gap, 4),
				'suffix'     => 't',
				'roundedGap' => $roundThousandsGap * pow($gap, 3),
			], // Trillion | 0.9t - 850t
			[
				'multiplier' => pow($gap, 5),
				'suffix'     => 'q',
				'roundedGap' => $roundThousandsGap * pow($gap, 4),
			], // Quadrillion | 0.9q - 850q
			[
				'multiplier' => pow($gap, 6),
				'suffix'     => 'Q',
				'roundedGap' => $roundThousandsGap * pow($gap, 5),
			], // Quintillion | 0.9Q - ...
		];
		
		foreach ($multipliers as $key => $item) {
			$multiplier = $item['multiplier'];
			$suffix = $item['suffix'];
			$roundedMultiplier = $multiplier - $item['roundedGap'];
			
			$lastKey = array_key_last($multipliers);
			$nextKey = $key + 1;
			
			$lastRoundedGap = data_get($multipliers, $lastKey . '.roundedGap', 0);
			$lastMultiplier = data_get($multipliers, $lastKey . '.multiplier') - $lastRoundedGap;
			
			$nextRoundedGap = data_get($multipliers, $nextKey . '.roundedGap', 0);
			$nextMultiplier = data_get($multipliers, $nextKey . '.multiplier', $lastMultiplier) - $nextRoundedGap;
			
			$canBeShorted = ($nextMultiplier == $lastMultiplier)
				? ($value >= $lastMultiplier)
				: ($value >= $roundedMultiplier && $value < $nextMultiplier);
			
			if ($canBeShorted) {
				$valueShorted = $value / $multiplier;
				// $value = $valueShorted . $suffix; // Debug with Tinkerwell
				$value = self::localeFormat($valueShorted, $precision) . $suffix;
				
				break;
			}
		}
		
		return $value;
	}
	
	/**
	 * @param float|int|string|null $value
	 * @param int $decimals
	 * @param bool $removeZeroAsDecimal
	 * @return float|int|string|null
	 */
	public static function localeFormat(
		float|int|string|null $value,
		int                   $decimals = 2,
		bool                  $removeZeroAsDecimal = true
	): float|int|string|null
	{
		// Convert string to numeric
		$value = self::getFloatRawFormat($value);
		
		if (!is_numeric($value)) return null;
		
		// Set locale for LC_NUMERIC (This is reset below)
		systemLocale()->setLocale(app()->getLocale(), LC_NUMERIC);
		
		// Get numeric formatting information & format '$value'
		$localeInfo = localeconv();
		$decPoint = $localeInfo['decimal_point'] ?? '.';
		$thousandsSep = $localeInfo['thousands_sep'] ?? ',';
		$value = number_format($value, $decimals, $decPoint, $thousandsSep);
		
		if ($removeZeroAsDecimal) {
			$value = self::removeZeroAsDecimal($value, $decimals, $decPoint);
		}
		
		systemLocale()->resetLcNumeric();
		
		return $value;
	}
	
	/**
	 * Transform the given number to display it using the Currency format settings
	 * NOTE: Transform non-numeric value
	 *
	 * @param float|int|string|null $value
	 * @param int|null $decimals
	 * @param string|null $decPoint
	 * @param string|null $thousandsSep
	 * @param bool $removeZeroAsDecimal
	 * @return float|int|string|null
	 */
	public static function format(
		float|int|string|null $value,
		int                   $decimals = null,
		string                $decPoint = null,
		string                $thousandsSep = null,
		bool                  $removeZeroAsDecimal = true
	): float|int|string|null
	{
		// Convert string to numeric
		$value = self::getFloatRawFormat($value);
		
		if (!is_numeric($value)) return null;
		
		$defaultCurrency = config('selectedCurrency', config('currency'));
		if (is_null($decimals)) {
			$decimals = (int)data_get($defaultCurrency, 'decimal_places', 2);
		}
		if (is_null($decPoint)) {
			$decPoint = data_get($defaultCurrency, 'decimal_separator', '.');
		}
		if (is_null($thousandsSep)) {
			$thousandsSep = data_get($defaultCurrency, 'thousand_separator', ',');
		}
		
		// Currency format - Ex: USD 100,234.56 | EUR 100 234,56
		$value = number_format($value, $decimals, $decPoint, $thousandsSep);
		
		if ($removeZeroAsDecimal) {
			$value = self::removeZeroAsDecimal($value, $decimals, $decPoint);
		}
		
		return $value;
	}
	
	/**
	 * Format a number before insert it in MySQL database
	 * NOTE: The DB column need to be decimal (or float)
	 *
	 * @param float|int|string|null $value
	 * @param string $decPoint
	 * @param bool $canSaveZero
	 * @return float|int|string|null
	 */
	public static function formatForDb(
		float|int|string|null $value,
		string                $decPoint = '.',
		bool                  $canSaveZero = true
	): float|int|string|null
	{
		$value = strval($value);
		$value = preg_replace('/^[0\s]+(.+)$/', '$1', $value);  // 0123 => 123 | 00 123 => 123
		$value = preg_replace('/^[.]+/', '0.', $value);         // .123 => 0.123
		
		if ($canSaveZero) {
			$value = ($value == 0 && strlen(trim($value)) > 0) ? 0 : $value;
			if ($value === 0) {
				return $value;
			} else {
				if (empty($value)) {
					return $value;
				}
			}
		}
		
		if ($decPoint == '.') {
			// For string ending by '.000' like 'XX.000',
			// Replace the '.000' by ',000' like 'XX,000' before removing the thousands separator
			$value = preg_replace('/\.\s?(0{3}+)$/', ',$1', $value);
			
			// Remove eventual thousands separator
			$value = str_replace(',', '', $value);
		}
		if ($decPoint == ',') {
			// Remove eventual thousands separator
			$value = str_replace('.', '', $value);
			
			// Always save in DB decimals with dot (.) instead of comma (,)
			$value = str_replace(',', '.', $value);
		}
		
		// Skip only numeric and dot characters
		$value = preg_replace('/[^\d.]/', '', $value);
		
		// Use the first dot as decimal point (All the next dots will be ignored)
		$tmp = explode('.', $value);
		if (!empty($tmp)) {
			$value = $tmp[0] . (isset($tmp[1]) ? '.' . $tmp[1] : '');
		}
		
		if (empty($value)) {
			return null;
		}
		
		return $value;
	}
	
	/**
	 * Get Float Raw Format
	 *
	 * @param float|int|string|null $value
	 * @return float|int|string|null
	 */
	public static function getFloatRawFormat(float|int|string|null $value): float|int|string|null
	{
		if (is_numeric($value)) return $value;
		if (!is_string($value)) return null;
		
		$value = trim($value);
		$value = strtr($value, [' ' => '']);
		$value = preg_replace('/ +/', '', $value);
		$value = str_replace(',', '.', $value);
		$value = preg_replace('/[^\d.]/', '', $value);
		
		if (empty($value)) return null;
		
		return getAsString($value);
	}
	
	/**
	 * @param float|int|string|null $value
	 * @param array|null $itemCurrency
	 * @return string
	 */
	public static function money(float|int|string|null $value, ?array $itemCurrency = []): string
	{
		$value = self::applyCurrencyRate($value, $itemCurrency);
		
		if (config('settings.other.decimals_superscript')) {
			return static::moneySuperscript($value);
		}
		
		$currency = !empty($itemCurrency) ? $itemCurrency : config('selectedCurrency', config('currency'));
		
		$decimals = (int)data_get($currency, 'decimal_places', 2);
		$decPoint = data_get($currency, 'decimal_separator', '.');
		$thousandsSep = data_get($currency, 'thousand_separator', ',');
		
		$value = self::format($value, $decimals, $decPoint, $thousandsSep);
		
		// In line current
		if (data_get($currency, 'in_left') == 1) {
			$value = data_get($currency, 'symbol') . $value;
		} else {
			$value = $value . ' ' . data_get($currency, 'symbol');
		}
		
		return getAsString($value);
	}
	
	/**
	 * @param float|int|string|null $value
	 * @param array|null $itemCurrency
	 * @return string
	 */
	public static function moneySuperscript(float|int|string|null $value, ?array $itemCurrency = []): string
	{
		$value = self::format($value);
		$currency = !empty($itemCurrency) ? $itemCurrency : config('selectedCurrency', config('currency'));
		
		$decPoint = data_get($currency, 'decimal_separator', '.');
		$tmp = explode($decPoint, $value);
		
		$integer = $tmp[0] ?? $value;
		$decimal = $tmp[1] ?? '00';
		$currencySymbol = data_get($currency, 'symbol');
		
		$value = (data_get($currency, 'in_left') == 1)
			? $currencySymbol . $integer . '<sup>' . $decimal . '</sup>'
			: $integer . '<sup>' . $currencySymbol . $decimal . '</sup>';
		
		return getAsString($value);
	}
	
	/**
	 * Remove decimal value if it's null
	 *
	 * Note:
	 * Remove unnecessary zeroes after decimal. "1.0" -> "1"; "1.00" -> "1"
	 * Intentionally does not affect partials, eg "1.50" -> "1.50"
	 *
	 * @param float|int|string|null $value
	 * @param int|null $decimals
	 * @param string|null $decPoint
	 * @return float|int|string|null
	 */
	public static function removeZeroAsDecimal(
		float|int|string|null $value,
		?int                  $decimals = null,
		?string               $decPoint = null
	): float|int|string|null
	{
		if ((int)$decimals <= 0) return $value;
		
		$decPoint ??= '.';
		$defaultDecimal = str_pad('', (int)$decimals, '0');
		
		return str_replace($decPoint . $defaultDecimal, '', strval($value));
	}
	
	/**
	 * @param float|int|string|null $value
	 * @param array|null $itemCurrency
	 * @return float|int|string|null
	 */
	public static function applyCurrencyRate(float|int|string|null $value, ?array $itemCurrency = []): float|int|string|null
	{
		if (!is_numeric($value)) return $value;
		
		// Get the selected currency data
		$currency = !empty($itemCurrency) ? $itemCurrency : config('selectedCurrency', config('currency'));
		
		// Get the currency rate
		$currencyRate = self::getCurrencyRate($currency);
		
		// Apply the currency rate
		try {
			$value = $value * $currencyRate;
		} catch (\Throwable $e) {
			// Debug
		}
		
		return $value;
	}
	
	/**
	 * Get the currency rate
	 *
	 * @param array|null $currency
	 * @return float|int
	 */
	public static function getCurrencyRate(?array $currency = []): float|int
	{
		$defaultRate = 1;
		
		if (empty($currency)) return $defaultRate;
		
		$rate = data_get($currency, 'rate', $defaultRate);
		$rate = getAsString($rate, $defaultRate);
		if (!is_numeric($rate)) {
			$rate = str_contains($rate, '.') ? floatval($rate) : intval($rate);
		}
		
		if (!is_numeric($rate)) return $defaultRate;
		
		return $rate;
	}
	
	/**
	 * Clean Float Value
	 * Fixed: MySQL don't accept the comma format number
	 *
	 * This function takes the last comma or dot (if any) to make a clean float,
	 * ignoring thousands separator, currency or any other letter.
	 *
	 * Example:
	 * $num = '1.999,369€';
	 * var_dump(Num::toFloat($num)); // float(1999.369)
	 * $otherNum = '126,564,789.33 m²';
	 * var_dump(Num::toFloat($otherNum)); // float(126564789.33)
	 *
	 * @param float|int|string|null $value
	 * @return float|int
	 */
	public static function toFloat(float|int|string|null $value): float|int
	{
		$value = strval($value);
		
		// Check negative numbers
		$isNegative = false;
		if (str_starts_with(trim($value), '-')) {
			$isNegative = true;
		}
		
		$dotPos = strrpos($value, '.');
		$commaPos = strrpos($value, ',');
		
		$dotPos = is_numeric($dotPos) ? $dotPos : 0;
		$commaPos = is_numeric($commaPos) ? $commaPos : 0;
		
		$isDotAfterComma = ($dotPos > $commaPos);
		$isCommaAfterDot = ($commaPos > $dotPos);
		
		$sepPos = $isDotAfterComma ? $dotPos : ($isCommaAfterDot ? $commaPos : 0);
		
		if ($sepPos == 0) {
			$value = preg_replace('/\D/', '', $value);
			if ($isNegative) {
				$value = '-' . $value;
			}
			
			return floatval($value);
		}
		
		$integer = preg_replace('/\D/', '', substr($value, 0, $sepPos));
		$decimal = preg_replace('/\D/', '', substr($value, $sepPos + 1, strlen($value)));
		$decimal = rtrim($decimal, '0');
		
		if (intval($decimal) == 0) {
			$value = $integer;
			if ($isNegative) {
				$value = '-' . $value;
			}
			
			return intval($value);
		}
		
		$value = $integer . '.' . $decimal;
		if ($isNegative) {
			$value = '-' . $value;
		}
		
		return floatval($value);
	}
	
	/**
	 * Convert the given number to its file size equivalent.
	 *
	 * @param float|int $size
	 * @return string
	 */
	public static function fileSize(float|int $size): string
	{
		$units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
		
		for ($i = 0; $size >= 1024 && $i < count($units) - 1; $i++) {
			$size /= 1024;
		}
		
		return round($size, 2) . ' ' . $units[$i];
	}
}
