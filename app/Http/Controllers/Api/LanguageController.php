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

namespace App\Http\Controllers\Api;

use App\Http\Resources\EntityCollection;
use App\Http\Resources\LanguageResource;
use App\Models\Language;
use App\Models\Scopes\ActiveScope;

/**
 * @group Languages
 */
class LanguageController extends BaseController
{
	/**
	 * List languages
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index(): \Illuminate\Http\JsonResponse
	{
		$isNonActiveIncluded = (request()->filled('includeNonActive') && request()->integer('includeNonActive') == 1);
		
		$cacheFiltersId = '.' . (int)$isNonActiveIncluded;
		
		$cacheId = 'languages.all' . $cacheFiltersId;
		$languages = cache()->remember($cacheId, $this->cacheExpiration, function () use ($isNonActiveIncluded) {
			$languages = Language::query();
			
			if ($isNonActiveIncluded) {
				$languages->withoutGlobalScopes([ActiveScope::class]);
			} else {
				$languages->active();
			}
			
			// Sorting
			$languages = $this->applySorting($languages, ['lft']);
			
			return $languages->get();
		});
		
		$resourceCollection = new EntityCollection(class_basename($this), $languages);
		
		$message = ($languages->count() <= 0) ? t('no_languages_found') : null;
		
		return apiResponse()->withCollection($resourceCollection, $message);
	}
	
	/**
	 * Get language
	 *
	 * @urlParam code string required The language's code. Example: en
	 *
	 * @param $code
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show($code): \Illuminate\Http\JsonResponse
	{
		$cacheId = 'language.' . $code;
		$language = cache()->remember($cacheId, $this->cacheExpiration, function () use ($code) {
			$language = Language::query()->where('code', '=', $code);
			
			return $language->first();
		});
		
		abort_if(empty($language), 404, t('language_not_found'));
		
		$resource = new LanguageResource($language);
		
		return apiResponse()->withResource($resource);
	}
}
