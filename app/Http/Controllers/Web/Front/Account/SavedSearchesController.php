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

namespace App\Http\Controllers\Web\Front\Account;

use Larapen\LaravelMetaTags\Facades\MetaTag;

class SavedSearchesController extends AccountBaseController
{
	/**
	 * @return \Illuminate\Contracts\View\View
	 */
	public function index()
	{
		// Call API endpoint
		$endpoint = '/savedSearches';
		$queryParams = [
			'embed' => 'user,country,pictures,postType,category,city',
			'sort'  => 'created_at',
		];
		$queryParams = array_merge(request()->all(), $queryParams);
		$headers = [
			'X-WEB-CONTROLLER' => class_basename(get_class($this)),
		];
		$data = makeApiRequest(method: 'get', uri: $endpoint, data: $queryParams, headers: $headers);
		
		$apiMessage = $this->handleHttpError($data);
		$apiResult = data_get($data, 'result');
		
		$appName = config('settings.app.name', 'Site Name');
		$title = t('my_saved_search') . ' - ' . $appName;
		
		// Meta Tags
		MetaTag::set('title', $title);
		MetaTag::set('description', t('my_saved_search_on', ['appName' => config('settings.app.name')]));
		
		return view('front.account.saved-searches.index', compact('apiMessage', 'apiResult'));
	}
	
	/**
	 * @param $id
	 * @return \Illuminate\Contracts\View\View
	 */
	public function show($id)
	{
		// Call API endpoint
		$endpoint = '/savedSearches/' . $id;
		$queryParams = [
			'embed' => 'user,country,pictures,postType,category,city',
			'sort'  => 'created_at',
		];
		$queryParams = array_merge(request()->all(), $queryParams);
		$headers = [
			'X-WEB-CONTROLLER' => class_basename(get_class($this)),
		];
		$data = makeApiRequest(method: 'get', uri: $endpoint, data: $queryParams, headers: $headers);
		
		$message = $this->handleHttpError($data);
		$savedSearch = data_get($data, 'result');
		$apiMessagePosts = data_get($savedSearch, 'posts.message');
		$apiResultPosts = data_get($savedSearch, 'posts.result');
		$apiExtraPosts = data_get($savedSearch, 'posts.extra');
		
		// Meta Tags
		MetaTag::set('title', t('my_saved_search'));
		MetaTag::set('description', t('my_saved_search_on', ['appName' => config('settings.app.name')]));
		
		return view(
			'front.account.saved-searches.show',
			compact('savedSearch', 'apiMessagePosts', 'apiResultPosts', 'apiExtraPosts')
		);
	}
	
	/**
	 * @param $id
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function destroy($id = null)
	{
		// Get Entries ID
		$ids = [];
		if (request()->filled('entries')) {
			$ids = request()->input('entries');
		} else {
			if (isStringable($id) && !empty($id)) {
				$ids[] = (string)$id;
			}
		}
		$ids = implode(',', $ids);
		
		// Get API endpoint
		$endpoint = '/savedSearches/' . $ids;
		
		// Call API endpoint
		$data = makeApiRequest(method: 'delete', uri: $endpoint, data: request()->all());
		
		// Parsing the API response
		$message = data_get($data, 'message', t('unknown_error'));
		
		// HTTP Error Found
		if (!data_get($data, 'isSuccessful')) {
			flash($message)->error();
			
			return redirect()->back();
		}
		
		// Notification Message
		if (data_get($data, 'success')) {
			flash($message)->success();
		} else {
			flash($message)->error();
		}
		
		return redirect()->back();
	}
}
