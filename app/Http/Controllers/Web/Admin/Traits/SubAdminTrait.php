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

namespace App\Http\Controllers\Web\Admin\Traits;

use App\Models\Scopes\ActiveScope;

trait SubAdminTrait
{
	/**
	 * Increment new Entries Codes
	 *
	 * @param string|null $prefix
	 * @return string
	 */
	public function autoIncrementCode(?string $prefix = null): string
	{
		// Init.
		$startAt = 0;
		$customPrefix = config('larapen.core.locationCodePrefix', 'Z');
		$customPrefix = is_string($customPrefix) ? $customPrefix : 'Z';
		$padLength = 3;
		$pad = '0';
		
		// Get the latest Entry
		$latestAddedEntry = $this->xPanel->model->withoutGlobalScope(ActiveScope::class)
			->where('country_code', '=', $this->countryCode)
			->where('code', 'LIKE', $prefix . $customPrefix . '%')
			->orderByDesc('code')
			->first();
		
		if (!empty($latestAddedEntry)) {
			$codeTab = explode($prefix, $latestAddedEntry->code);
			$latestAddedId = $codeTab[1] ?? null;
			if (!empty($latestAddedId)) {
				if (is_numeric($latestAddedId)) {
					$newId = $latestAddedId + 1;
				} else {
					$newId = $this->alphanumericToUniqueIncrementation($latestAddedId, $startAt, $padLength, $customPrefix, $pad);
				}
			} else {
				$newId = $customPrefix . str($startAt + 1)->padLeft($padLength, $pad);
			}
		} else {
			$newId = $customPrefix . str($startAt + 1)->padLeft($padLength, $pad);
		}
		
		// Full new ID
		return $prefix . $newId;
	}
	
	/**
	 * Increment existing alphanumeric value by Transforming the given value
	 * e.g. AB => ZZ001 => ZZ002 => ZZ003 ...
	 *
	 * @param string|null $value
	 * @param int $startAt
	 * @param int $padLength
	 * @param string|null $customPrefix
	 * @param string $pad
	 * @return string
	 */
	private function alphanumericToUniqueIncrementation(?string $value, int $startAt, int $padLength, ?string $customPrefix, string $pad = '0'): string
	{
		if (!empty($value)) {
			// Numeric value
			if (is_numeric($value)) {
				
				$value = $customPrefix . str($value + 1)->padLeft(2, $pad);
				
			} // NOT numeric value
			else {
				
				// Value contains the Custom Prefix
				if (str_starts_with($value, $customPrefix)) {
					
					$prefixLoop = '';
					$partOfValue = '';
					
					$tmp = explode($customPrefix, $value);
					if (count($tmp) > 0) {
						foreach ($tmp as $item) {
							if (!empty($item)) {
								$partOfValue = $item;
								break;
							} else {
								$prefixLoop .= $customPrefix;
							}
						}
					}
					
					if (!empty($partOfValue)) {
						if (is_numeric($partOfValue)) {
							$tmpValue = str($partOfValue + 1)->padLeft($padLength, $pad);
						} else {
							// If the part of the value is not numeric, Get a (sub-)new unique code
							$tmpValue = $this->alphanumericToUniqueIncrementation($partOfValue, $startAt, $padLength, $customPrefix, $pad);
						}
					} else {
						$tmpValue = str($startAt + 1)->padLeft($padLength, $pad);
					}
					
					$value = $prefixLoop . $tmpValue;
					
				} // Value DOESN'T contain the Custom Prefix
				else {
					$value = $customPrefix . str($startAt + 1)->padLeft($padLength, $pad);
				}
			}
			
		} else {
			$value = $customPrefix . str($startAt + 1)->padLeft($padLength, $pad);
		}
		
		return $value;
	}
}
