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

namespace App\Http\Controllers\Web\Front\Post\Traits;


trait CatBreadcrumbTrait
{
	/**
	 * Get ordered category breadcrumb
	 *
	 * @param $cat
	 * @param int $position
	 * @return array
	 */
	private function getCatBreadcrumb($cat, int $position = 0): array
	{
		$array = $this->getUnorderedCatBreadcrumb($cat, $position);
		
		return $this->reorderCatBreadcrumbItemsPositions($array);
	}
	
	/**
	 * Get unordered category breadcrumb
	 *
	 * @param $cat
	 * @param int $position
	 * @param array $tab
	 * @return array
	 */
	private function getUnorderedCatBreadcrumb($cat, int &$position = 0, array &$tab = []): array
	{
		$isFromCatModel = (
			array_key_exists('parent_id', (array)$cat)
			&& array_key_exists('seo_title', (array)$cat)
		);
		
		if (empty($cat) || !$isFromCatModel) {
			return $tab;
		}
		
		if (empty($tab)) {
			$tab[] = [
				'name'     => data_get($cat, 'name'),
				'url'      => urlGen()->category($cat),
				'position' => $position,
			];
		}
		
		if (!empty(data_get($cat, 'parent'))) {
			$tab[] = [
				'name'     => data_get($cat, 'parent.name'),
				'url'      => urlGen()->category(data_get($cat, 'parent')),
				'position' => $position + 1,
			];
			
			if (!empty(data_get($cat, 'parent.parent'))) {
				$position = $position + 1;
				
				return $this->getUnorderedCatBreadcrumb(data_get($cat, 'parent'), $position, $tab);
			}
		}
		
		return $tab;
	}
	
	/**
	 * Reorder the items' positions
	 * And transform each item from array to collection
	 *
	 * @param array|null $array
	 * @return array
	 */
	private function reorderCatBreadcrumbItemsPositions(?array $array = []): array
	{
		if (!is_array($array)) {
			return [];
		}
		
		$countItems = count($array);
		if ($countItems > 0) {
			$tmp = $array;
			$j = $countParents = $countItems - 1;
			for ($i = 0; $i <= $countParents; $i++) {
				if (isset($array[$i]) && $tmp[$j]) {
					$array[$i]['position'] = $tmp[$j]['position'];
					
					// Transform the item from array to collection
					$array[$i] = collect($array[$i]);
				}
				$j--;
			}
			unset($tmp);
			$array = array_reverse($array);
		}
		
		return $array;
	}
}
