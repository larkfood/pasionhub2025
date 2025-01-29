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

namespace App\Enums;

use App\Helpers\Arr;

trait EnumToArray
{
	/**
	 * @param string|null $orderBy
	 * @param string $order
	 * @return array
	 */
	public static function all(?string $orderBy = null, string $order = 'asc'): array
	{
		$entries = collect(self::cases())
			->mapWithKeys(fn ($item) => [$item->value => self::find($item->value)])
			->toArray();
		
		if (empty($orderBy)) {
			$orderBy = 'label';
		}
		
		try {
			$entries = Arr::mbSortBy($entries, $orderBy, $order);
		} catch (\Throwable $e) {
		}
		
		return $entries;
	}
	
	/**
	 * @param $value
	 * @return array
	 */
	public static function find($value = null): array
	{
		if (empty($value)) return [];
		
		$item = self::tryFrom($value);
		if (empty($item)) return [];
		
		return [
			'id'    => $item->value,
			'name'  => $item->name,
			'label' => $item->label(),
		];
	}
	
	/**
	 * @return array
	 */
	public static function names(): array
	{
		return array_column(self::cases(), 'name');
	}
	
	/**
	 * @return array
	 */
	public static function values(): array
	{
		return array_column(self::cases(), 'value');
	}
}
