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

use App\Http\Controllers\Web\Front\Account\Traits\MessagesTrait;
use App\Http\Requests\Front\ReplyMessageRequest;
use App\Http\Requests\Front\SendMessageRequest;
use Larapen\LaravelMetaTags\Facades\MetaTag;

class MessagesController extends AccountBaseController
{
	use MessagesTrait;
	
	private int $perPage = 10;
	
	public function __construct()
	{
		parent::__construct();
		
		$perPage = config('settings.pagination.per_page');
		$this->perPage = is_int($perPage) ? $perPage : $this->perPage;
	}
	
	/**
	 * Show all the message threads to the user.
	 *
	 * @return \Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
	 */
	public function index()
	{
		// Call API endpoint
		$endpoint = '/threads';
		$queryParams = [];
		if (request()->filled('filter')) {
			$queryParams['filter'] = request()->input('filter');
		}
		$queryParams = array_merge(request()->all(), $queryParams);
		$data = makeApiRequest(method: 'get', uri: $endpoint, data: $queryParams);
		
		$apiMessage = $this->handleHttpError($data);
		$apiResult = data_get($data, 'result');
		
		$appName = config('settings.app.name', 'Site Name');
		$title = t('messenger_inbox') . ' - ' . $appName;
		
		// Meta Tags
		MetaTag::set('title', $title);
		MetaTag::set('description', t('messenger_inbox'));
		
		if (isFromAjax()) {
			$threads = (array)data_get($apiResult, 'data');
			$totalThreads = (array)data_get($apiResult, 'meta.total');
			
			$result = [
				'threads' => view('front.account.messenger.threads.threads', ['totalThreads' => $totalThreads, 'threads' => $threads])->render(),
				'links'   => view('front.account.messenger.threads.links', ['apiResult' => $apiResult])->render(),
			];
			
			return ajaxResponse()->json($result);
		}
		
		return view('front.account.messenger.index', compact('apiResult'));
	}
	
	/**
	 * Shows a message thread.
	 *
	 * @param $id
	 * @return \Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
	 */
	public function show($id)
	{
		// Call API endpoint
		$endpoint = '/threads/' . $id;
		$queryParams = [
			'embed'   => 'user,post,messages,participants',
			'perPage' => $this->perPage, // for the thread's messages
		];
		$queryParams = array_merge(request()->all(), $queryParams);
		$data = makeApiRequest(method: 'get', uri: $endpoint, data: $queryParams);
		
		// Parsing the API response
		$message = data_get($data, 'message');
		
		// HTTP Error Found
		if (!data_get($data, 'isSuccessful')) {
			$message = $message ?? t('unknown_error');
			flash($message)->error();
			
			return redirect()->to('account/messages');
		}
		
		$thread = data_get($data, 'result');
		
		// Message Collection
		// $errorMessage = data_get($thread, 'messages.message');
		$apiResult = data_get($thread, 'messages.result');
		$messages = (array)data_get($apiResult, 'data');
		$totalMessages = (int)data_get($apiResult, 'meta.total', 0);
		$linksRender = view('front.account.messenger.messages.pagination', ['apiResult' => $apiResult])->render();
		
		// Meta Tags
		MetaTag::set('title', t('Messages Received'));
		MetaTag::set('description', t('Messages Received'));
		
		// Reverse the collection order like Messenger
		$messages = collect($messages)->reverse()->toArray();
		
		if (isFromAjax()) {
			$result = [
				'totalMessages' => $totalMessages,
				'messages'      => view(
					'front.account.messenger.messages.messages',
					[
						'thread'        => $thread,
						'totalMessages' => $totalMessages,
						'messages'      => $messages,
					]
				)->render(),
				'links'         => $linksRender,
			];
			
			return ajaxResponse()->json($result);
		}
		
		return view('front.account.messenger.show', compact('thread', 'totalMessages', 'messages', 'linksRender'));
	}
	
	/**
	 * Stores a new message thread.
	 * Contact the Post's Author
	 * Note: This method does not call with AJAX
	 *
	 * @param $postId
	 * @param \App\Http\Requests\Front\SendMessageRequest $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function store($postId, SendMessageRequest $request)
	{
		request()->merge(['post_id' => $postId]);
		
		// Call API endpoint
		$endpoint = '/threads';
		$data = makeApiRequest(method: 'post', uri: $endpoint, data: $request->all(), files: $request->allFiles());
		
		// Parsing the API response
		$message = data_get($data, 'message', t('unknown_error'));
		
		// HTTP Error Found
		if (!data_get($data, 'isSuccessful')) {
			flash($message)->error();
			
			return redirect()->back()->withInput($request->except('file_path'));
		}
		
		// Notification Message
		if (data_get($data, 'success')) {
			flash($message)->success();
		} else {
			flash($message)->error();
		}
		
		// Get Post
		$post = data_get($data, 'extra.post');
		
		if (!empty($post)) {
			return redirect()->to(urlGen()->postUri($post));
		} else {
			return redirect()->back();
		}
	}
	
	/**
	 * Adds a new message to a current thread.
	 *
	 * @param $id
	 * @param \App\Http\Requests\Front\ReplyMessageRequest $request
	 * @return \Illuminate\Http\JsonResponse|void
	 */
	public function update($id, ReplyMessageRequest $request)
	{
		if (!isFromAjax()) {
			return;
		}
		
		// Call API endpoint
		$endpoint = '/threads/' . $id;
		$data = makeApiRequest(method: 'post', uri: $endpoint, data: $request->all(), files: $request->allFiles());
		
		// Parsing the API response
		$status = (int)data_get($data, 'status');
		$message = data_get($data, 'message', t('unknown_error'));
		
		$result = [
			'success' => (bool)data_get($data, 'success'),
			'msg'     => $message,
		];
		
		return ajaxResponse()->json($result, $status);
	}
	
	/**
	 * Actions on the Threads
	 *
	 * @param $threadId
	 * @return \Illuminate\Http\JsonResponse|void
	 */
	public function actions($threadId = null)
	{
		if (!isFromAjax()) {
			return;
		}
		
		// Call API endpoint
		$endpoint = '/threads/bulkUpdate' . $this->getSelectedIds($threadId);
		$data = makeApiRequest(method: 'post', uri: $endpoint, data: request()->all());
		
		// Parsing the API response
		$status = (int)data_get($data, 'status');
		$message = data_get($data, 'message', t('unknown_error'));
		
		$actionType = request()->input('type');
		
		$result = [
			'type'    => $actionType,
			'success' => (bool)data_get($data, 'success'),
			'msg'     => $message,
		];
		if (!empty($threadId)) {
			$result['baseUrl'] = request()->url();
		}
		
		// HTTP Error Found
		if (!data_get($data, 'isSuccessful')) {
			$result['success'] = false;
		}
		
		return ajaxResponse()->json($result, $status);
	}
	
	/**
	 * Delete Thread
	 *
	 * @param null $threadId
	 * @return \Illuminate\Http\JsonResponse|void
	 */
	public function destroy($threadId = null)
	{
		if (!isFromAjax()) {
			return;
		}
		
		// Call API endpoint
		$endpoint = '/threads' . $this->getSelectedIds($threadId);
		$data = makeApiRequest(method: 'delete', uri: $endpoint, data: request()->all());
		
		// Parsing the API response
		$status = (int)data_get($data, 'status');
		$message = data_get($data, 'message', t('unknown_error'));
		
		$result = [
			'type'    => 'delete',
			'success' => (bool)data_get($data, 'success'),
			'msg'     => $message,
		];
		if (!empty($threadId)) {
			$result['baseUrl'] = request()->url();
		}
		
		// HTTP Error Found
		if (!data_get($data, 'isSuccessful')) {
			$result['success'] = false;
		}
		
		return ajaxResponse()->json($result, $status);
	}
}
