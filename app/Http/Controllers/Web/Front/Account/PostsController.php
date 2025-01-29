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

class PostsController extends AccountBaseController
{
	/**
	 * @param $pagePath
	 * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse|void
	 */
	public function getPage($pagePath)
	{
		switch ($pagePath) {
			case 'list':
				return $this->index();
			case 'archived':
				return $this->archivedPosts();
			case 'pending-approval':
				return $this->pendingApprovalPosts();
			case 'favourite':
				return $this->savedPosts();
			default:
				abort(404);
		}
	}
	
	/**
	 * @param $postId
	 * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
	 */
	public function index($postId = null)
	{
		// If "offline" button is clicked
		if (
			is_numeric($postId)
			&& $postId > 0
			&& str_contains(url()->current(), $postId . '/offline')
		) {
			// Call API endpoint
			$endpoint = '/posts/' . $postId . '/offline';
			$data = makeApiRequest(method: 'put', uri: $endpoint);
			
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
		
		// Call API endpoint
		$endpoint = '/posts';
		$queryParams = [
			'belongLoggedUser' => 1,
			'embed'            => 'category,postType,city,currency,payment,package,pictures',
			'sort'             => 'created_at',
		];
		$queryParams = array_merge(request()->all(), $queryParams);
		$data = makeApiRequest(method: 'get', uri: $endpoint, data: $queryParams);
		
		$apiMessage = $this->handleHttpError($data);
		$apiResult = data_get($data, 'result');
		
		$appName = config('settings.app.name', 'Site Name');
		$title = t('my_listings') . ' - ' . $appName;
		
		// Meta Tags
		MetaTag::set('title', $title);
		MetaTag::set('description', t('my_listings_on', ['appName' => config('settings.app.name')]));
		
		return view('front.account.posts', compact('apiResult'));
	}
	
	/**
	 * @param $postId
	 * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
	 */
	public function archivedPosts($postId = null)
	{
		// If "repost" button is clicked
		if (str_contains(url()->current(), $postId . '/repost')) {
			// Call API endpoint
			$endpoint = '/posts/' . $postId . '/repost';
			$data = makeApiRequest(method: 'put', uri: $endpoint);
			
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
			
			// Get User Resource
			$post = data_get($data, 'result');
			$postUrl = urlGen()->post($post);
			
			return redirect()->to($postUrl);
		}
		
		// Call API endpoint
		$endpoint = '/posts';
		$queryParams = [
			'belongLoggedUser' => 1,
			'archived'         => 1,
			'embed'            => 'category,postType,city,currency,payment,pictures',
			'sort'             => 'created_at',
		];
		$queryParams = array_merge(request()->all(), $queryParams);
		$data = makeApiRequest(method: 'get', uri: $endpoint, data: $queryParams);
		
		$apiMessage = $this->handleHttpError($data);
		$apiResult = data_get($data, 'result');
		
		// Meta Tags
		MetaTag::set('title', t('my_archived_listings'));
		MetaTag::set('description', t('my_archived_listings_on', ['appName' => config('settings.app.name')]));
		
		return view('front.account.posts', compact('apiResult'));
	}
	
	/**
	 * @return \Illuminate\Contracts\View\View
	 */
	public function pendingApprovalPosts()
	{
		// Call API endpoint
		$endpoint = '/posts';
		$queryParams = [
			'belongLoggedUser' => 1,
			'pendingApproval'  => 1,
			'embed'            => 'category,postType,city,currency,payment,pictures',
			'sort'             => 'created_at',
		];
		$queryParams = array_merge(request()->all(), $queryParams);
		$data = makeApiRequest(method: 'get', uri: $endpoint, data: $queryParams);
		
		$apiMessage = $this->handleHttpError($data);
		$apiResult = data_get($data, 'result');
		
		// Meta Tags
		MetaTag::set('title', t('my_pending_approval_listings'));
		MetaTag::set('description', t('my_pending_approval_listings_on', ['appName' => config('settings.app.name')]));
		
		return view('front.account.posts', compact('apiResult'));
	}
	
	/**
	 * @return \Illuminate\Contracts\View\View
	 */
	public function savedPosts()
	{
		// Call API endpoint
		$endpoint = '/savedPosts';
		$queryParams = [
			'embed' => 'post,city,currency,pictures,user',
			'sort'  => 'created_at',
		];
		$queryParams = array_merge(request()->all(), $queryParams);
		$data = makeApiRequest(method: 'get', uri: $endpoint, data: $queryParams);
		
		$apiMessage = $this->handleHttpError($data);
		$apiResult = data_get($data, 'result');
		
		// Transform the API result
		$apiResult = collect($apiResult)->mapWithKeys(function ($item, $key) {
			if ($key == 'data' && is_array($item)) {
				$newItem = [];
				foreach ($item as $idx => $savedPost) {
					$newItem[$savedPost['id']] = $savedPost['post'];
				}
				$item = $newItem;
			}
			
			return [$key => $item];
		})->toArray();
		
		// Meta Tags
		MetaTag::set('title', t('my_favourite_listings'));
		MetaTag::set('description', t('my_favourite_listings_on', ['appName' => config('settings.app.name')]));
		
		return view('front.account.posts', compact('apiResult'));
	}
	
	/**
	 * @param $pagePath
	 * @param $id
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function destroy($pagePath, $id = null)
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
		$endpoint = '/posts/' . $ids;
		$otherEndpoints = [
			'favourite' => '/savedPosts/' . $ids,
		];
		$endpoint = $otherEndpoints[$pagePath] ?? $endpoint;
		
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
		
		return redirect()->to('account/posts/' . $pagePath);
	}
}
