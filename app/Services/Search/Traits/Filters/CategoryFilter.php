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

namespace App\Services\Search\Traits\Filters;

use App\Models\Category;

trait CategoryFilter
{
	protected function applyCategoryFilter(): void
	{
		if (!isset($this->posts)) {
			return;
		}
		
		if (empty($this->cat) || !($this->cat instanceof Category)) {
			return;
		}
		
		$catChildrenIds = $this->getCategoryChildrenIds($this->cat, $this->cat->id);
		
		if (empty($catChildrenIds)) {
			return;
		}
		
		$this->posts->whereIn('category_id', $catChildrenIds);
	}
	
	/**
	 * Get all the category's children IDs
	 *
	 * @param $cat
	 * @param null $catId
	 * @param array $idsArr
	 * @return array
	 */
	private function getCategoryChildrenIds($cat, $catId = null, array &$idsArr = []): array
	{
		if (!empty($catId)) {
			$idsArr[] = $catId;
		}
		
		if (isset($cat->children) && $cat->children->count() > 0) {
			$subIdsArr = [];
			foreach ($cat->children as $subCat) {
				if ($subCat->active != 1) {
					continue;
				}
				
				$idsArr[] = $subCat->id;
				
				if (isset($subCat->children) && $subCat->children->count() > 0) {
					$subIdsArr = $this->getCategoryChildrenIds($subCat, null, $subIdsArr);
				}
			}
			$idsArr = array_merge($idsArr, $subIdsArr);
		}
		
		return $idsArr;
	}
}
