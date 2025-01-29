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

namespace App\Http\Controllers\Api\Post\List\Search;

use App\Http\Controllers\Api\Category\CategoryBy;
use App\Models\Category;

trait CategoryTrait
{
	use CategoryBy;
	
	/**
	 * Get Category (Auto-detecting ID or Slug)
	 *
	 * @return mixed|null
	 */
	public function getCategory()
	{
		// Get the Category's right arguments
		$catParentId = request()->input('c');
		$catId = request()->input('sc', $catParentId);
		
		// Get the $catParentId as $catId when $catId is empty
		// And empty the $carParentId when that have the same value as $catId
		$catId = empty($catId) ? $catParentId : $catId;
		$catParentId = ($catParentId == $catId) ? null : $catParentId;
		
		// Validate parameters values
		$catParentId = isStringable($catParentId) ? $catParentId : null;
		$catId = isStringable($catId) ? $catId : null;
		
		// Get the Category
		$cat = null;
		if (!empty($catId)) {
			if (is_numeric($catId)) {
				$cat = $this->getCategoryById($catId);
			} else {
				$isCatIdString = is_string($catId);
				$isCatParentIdStringOrEmpty = (is_string($catParentId) || empty($catParentId));
				
				if ($isCatIdString && $isCatParentIdStringOrEmpty) {
					$cat = $this->getCategoryBySlug($catId, $catParentId);
				}
			}
			
			if (empty($cat)) {
				abort(404, t('category_not_found'));
			}
		}
		
		return $cat;
	}
	
	/**
	 * Get Root Categories
	 *
	 * @return mixed
	 */
	public function getRootCategories()
	{
		$limit = getNumberOfItemsToTake('categories');
		
		$cacheId = 'cat.0.categories.take.' . $limit . '.' . config('app.locale');
		$cats = cache()->remember($cacheId, $this->cacheExpiration, function () use ($limit) {
			return Category::root()->orderBy('lft')->take($limit)->get();
		});
		
		if ($cats->count() > 0) {
			$cats = $cats->keyBy('id');
		}
		
		return $cats;
	}
}
