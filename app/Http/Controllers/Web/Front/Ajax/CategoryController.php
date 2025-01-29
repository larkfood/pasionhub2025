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

namespace App\Http\Controllers\Web\Front\Ajax;

use App\Http\Controllers\Web\Front\Post\CreateOrEdit\Traits\CategoriesTrait;
use App\Http\Controllers\Web\Front\FrontController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends FrontController
{
	use CategoriesTrait;
	
	protected array $catsWithPictureTypes = ['c_picture_list', 'c_bigIcon_list'];
	protected string $catDisplayType = 'c_bigIcon_list';
	
	/**
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getCategoriesHtml(Request $request): JsonResponse
	{
		$languageCode = $request->input('languageCode', config('app.locale'));
		$selectedCatId = $request->input('selectedCatId');
		$catId = $request->input('catId');
		$catId = !empty($catId) ? $catId : null; // Change 0 to null
		$page = $request->integer('page');
		
		// Update global vars
		$this->catDisplayType = config('settings.listing_form.cat_display_type', 'c_bigIcon_list');
		
		// Get category by ID - Call API endpoint
		$category = $this->getCategoryById($catId, $languageCode);
		
		// Get categories - Call API endpoint
		$apiMessage = null;
		$apiResult = $this->getCategories($catId, $languageCode, $apiMessage, $page);
		
		// Get categories list and format it
		$categories = data_get($apiResult, 'data', []);
		$categories = $this->formatCategories($categories, $catId);
		
		$hasChildren = (
			empty($catId)
			|| (!empty($category) && !empty($category['children']))
		);
		
		$data = [
			'apiResult'      => $apiResult,
			'apiMessage'     => $apiMessage,
			'catDisplayType' => $this->catDisplayType,
			'categories'     => $categories, // Adjacent Categories (Children)
			'category'       => $category,
			'hasChildren'    => $hasChildren,
			'catId'          => $selectedCatId,
		];
		
		// Get categories list buffer
		$html = getViewContent('front.post.createOrEdit.inc.category.select', $data);
		
		// Send JSON Response
		$result = [
			'html'        => $html,
			'category'    => $category,
			'hasChildren' => $hasChildren,
			'parent'      => $category['parent'] ?? null,
		];
		
		return ajaxResponse()->json($result);
	}
	
	/**
	 * @param $catId
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getCustomFieldsHtml($catId, Request $request): JsonResponse
	{
		$languageCode = $request->input('languageCode');
		$postId = $request->input('postId');
		
		// Set the xhr data
		$xhrData = ['customFields' => ''];
		
		if (empty($catId)) {
			return ajaxResponse()->json($xhrData);
		}
		
		// Get the Category's Custom Fields - Call API endpoint
		$endpoint = '/categories/' . $catId . '/fields';
		$queryParams = [
			'post_id'       => $postId,
			'errors'        => $request->input('errors'),
			'oldInput'      => $request->input('oldInput'),
			'sort'          => '-lft',
			'language_code' => $languageCode ?? config('app.locale'),
		];
		$queryParams = array_merge(request()->all(), $queryParams);
		$data = makeApiRequest(method: 'post', uri: $endpoint, data: $queryParams);
		
		$apiMessage = $this->handleHttpError($data);
		
		$fields = data_get($data, 'result');
		$errors = data_get($data, 'extra.errors');
		$oldInput = data_get($data, 'extra.oldInput');
		
		// Get the Custom Fields (in HTML)
		$customFields = '';
		if (!empty($fields)) {
			$data = [
				'fields'       => $fields,
				'languageCode' => $languageCode,
				'errors'       => $errors,
				'oldInput'     => $oldInput,
			];
			$customFields = getViewContent('front.post.createOrEdit.inc.fields', $data);
		}
		
		// Update the xhr data
		$xhrData['customFields'] = $customFields;
		
		return ajaxResponse()->json($xhrData);
	}
}
