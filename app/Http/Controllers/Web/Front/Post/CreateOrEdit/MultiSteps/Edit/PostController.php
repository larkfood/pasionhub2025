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

namespace App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Edit;

use App\Enums\PostType;
use App\Http\Controllers\Api\Payment\RetrievePackageFeatures;
use App\Http\Controllers\Web\Front\Auth\Traits\VerificationTrait;
use App\Http\Requests\Front\PostRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Larapen\LaravelMetaTags\Facades\MetaTag;

class PostController extends BaseController
{
	use VerificationTrait;
	use RetrievePackageFeatures;
	
	public $data;
	
	/**
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->commonQueries();
	}
	
	/**
	 * Common Queries
	 *
	 * @return void
	 */
	private function commonQueries(): void
	{
		if (config('settings.listing_form.show_listing_type') == '1') {
			$postTypes = PostType::all();
			view()->share('postTypes', $postTypes);
		}
	}
	
	/**
	 * Show the form
	 *
	 * @param $id
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
	 */
	public function getForm($id, Request $request)
	{
		// Check if the form type is 'Single-Step Form' and make redirection to it (permanently).
		if (isSingleStepFormEnabled()) {
			$url = urlGen()->editPost($id);
			if ($url != request()->fullUrl()) {
				return redirect()->to($url, 301)->withHeaders(config('larapen.core.noCacheHeaders'));
			}
		}
		
		$data = [];
		
		// Get Post
		$post = null;
		if (auth()->check()) {
			// Get post - Call API endpoint
			$endpoint = '/posts/' . $id;
			$queryParams = [
				'embed'               => 'category,city,subAdmin1,subAdmin2',
				'countryCode'         => config('country.code'),
				'unactivatedIncluded' => 1,
				'belongLoggedUser'    => 1, // Logged user required
				'noCache'             => 1,
			];
			$queryParams = array_merge(request()->all(), $queryParams);
			$data = makeApiRequest(method: 'get', uri: $endpoint, data: $queryParams);
			
			$apiMessage = $this->handleHttpError($data);
			$post = data_get($data, 'result');
		}
		
		abort_if(empty($post), 404, t('post_not_found'));
		
		view()->share('post', $post);
		$this->shareNavItems($post);
		
		// Share the post's current active payment info (If exists)
		$this->getCurrentActivePaymentInfo($post);
		
		// Get the Post's City's Administrative Division
		$adminType = config('country.admin_type', 0);
		$admin = data_get($post, 'city.subAdmin' . $adminType);
		if (!empty($admin)) {
			view()->share('admin', $admin);
		}
		
		// Meta Tags
		MetaTag::set('title', t('update_my_listing'));
		MetaTag::set('description', t('update_my_listing'));
		
		// Get steps URLs & labels
		$previousStepUrl = urlGen()->post($post);
		$previousStepLabel = t('Back');
		$nextStepUrl = url()->current();
		$nextStepLabel = t('Update');
		
		// Share steps URLs & label variables
		view()->share('previousStepUrl', $previousStepUrl);
		view()->share('previousStepLabel', $previousStepLabel);
		view()->share('nextStepUrl', $nextStepUrl);
		view()->share('nextStepLabel', $nextStepLabel);
		
		return view('front.post.createOrEdit.multiSteps.edit.post', $data);
	}
	
	/**
	 * Submit the form
	 *
	 * @param $id
	 * @param \App\Http\Requests\Front\PostRequest $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function postForm($id, PostRequest $request): RedirectResponse
	{
		// Call API endpoint
		$endpoint = '/posts/' . $id;
		$data = makeApiRequest(method: 'put', uri: $endpoint, data: $request->all(), files: $request->allFiles());
		
		// Parsing the API response
		$message = data_get($data, 'message', t('unknown_error'));
		
		// HTTP Error Found
		if (!data_get($data, 'isSuccessful')) {
			flash($message)->error();
			$previousUrl = data_get($data, 'extra.previousUrl');
			$previousUrl = !empty($previousUrl) ? $previousUrl : url()->previous();
			
			return redirect()->to($previousUrl)->withInput();
		}
		
		// Notification Message
		if (data_get($data, 'success')) {
			flash($message)->success();
		} else {
			flash($message)->error();
		}
		
		// Get Listing Resource
		$post = data_get($data, 'result');
		
		// Get the next URL
		$nextUrl = urlGen()->editPostPhotos($post);
		
		if (
			data_get($data, 'extra.sendEmailVerification.emailVerificationSent')
			|| data_get($data, 'extra.sendPhoneVerification.phoneVerificationSent')
		) {
			session()->put('itemNextUrl', $nextUrl);
			
			if (data_get($data, 'extra.sendEmailVerification.emailVerificationSent')) {
				session()->put('emailVerificationSent', true);
				
				// Show the Re-send link
				$this->showReSendVerificationEmailLink($post, 'posts');
			}
			
			if (data_get($data, 'extra.sendPhoneVerification.phoneVerificationSent')) {
				session()->put('phoneVerificationSent', true);
				
				// Show the Re-send link
				$this->showReSendVerificationSmsLink($post, 'posts');
				
				// Phone Number verification
				// Get the token|code verification form page URL
				// The user is supposed to have received this token|code by SMS
				$nextUrl = url('posts/verify/phone/');
			}
		}
		
		// Mail Notification Message
		if (data_get($data, 'extra.mail.message')) {
			$mailMessage = data_get($data, 'extra.mail.message');
			if (data_get($data, 'extra.mail.success')) {
				flash($mailMessage)->success();
			} else {
				flash($mailMessage)->error();
			}
		}
		
		return redirect()->to($nextUrl);
	}
}
