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

namespace App\Http\Controllers\Web\Front\Post;

use App\Http\Requests\Front\ReportRequest;
use App\Models\Post;
use App\Models\ReportType;
use App\Http\Controllers\Web\Front\FrontController;
use Illuminate\Routing\Controllers\Middleware;
use Larapen\LaravelMetaTags\Facades\MetaTag;

class ReportController extends FrontController
{
	/**
	 * ReportController constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->commonQueries();
	}
	
	/**
	 * Get the middleware that should be assigned to the controller.
	 */
	public static function middleware(): array
	{
		$array = [];
		
		if (config('settings.listing_page.auth_required_to_report_abuse')) {
			$array[] = new Middleware('auth', only: ['showReportForm', 'sendReport']);
		}
		
		return array_merge(parent::middleware(), $array);
	}
	
	/**
	 * Common Queries
	 */
	public function commonQueries()
	{
		// Get Report abuse types
		$reportTypes = ReportType::query()->get();
		view()->share('reportTypes', $reportTypes);
	}
	
	public function showReportForm($postId)
	{
		// Get Post
		$postId = hashId($postId, true) ?? $postId;
		$post = Post::findOrFail($postId);
		
		// Meta Tags
		$title = t('Report for', ['title' => mb_ucfirst($post->title)]);
		$description = t('Send a report for', ['title' => mb_ucfirst($post->title)]);
		
		MetaTag::set('title', $title);
		MetaTag::set('description', strip_tags($description));
		
		// Open Graph
		$this->og->title($title)->description($description);
		view()->share('og', $this->og);
		
		// SEO: noindex
		$noIndexListingsReportPages = (
			config('settings.seo.no_index_listing_report')
			&& currentRouteActionContains('Post\ReportController')
		);
		
		return view('front.post.report', compact('title', 'post', 'noIndexListingsReportPages'));
	}
	
	/**
	 * @param $postId
	 * @param \App\Http\Requests\Front\ReportRequest $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function sendReport($postId, ReportRequest $request)
	{
		// Call API endpoint
		$postId = hashId($postId, true) ?? $postId;
		$endpoint = '/posts/' . $postId . '/report';
		$data = makeApiRequest(method: 'post', uri: $endpoint, data: $request->all());
		
		// Parsing the API response
		$message = data_get($data, 'message', t('unknown_error'));
		
		// HTTP Error Found
		if (!data_get($data, 'isSuccessful')) {
			flash($message)->error();
			
			return redirect()->back()->withInput();
		}
		
		// Notification Message
		if (data_get($data, 'success')) {
			flash($message)->success();
		} else {
			flash($message)->error();
		}
		
		$post = data_get($data, 'extra.post');
		
		if (!empty($post)) {
			return redirect()->to(urlGen()->postUri($post));
		} else {
			return redirect()->to('/');
		}
	}
}
