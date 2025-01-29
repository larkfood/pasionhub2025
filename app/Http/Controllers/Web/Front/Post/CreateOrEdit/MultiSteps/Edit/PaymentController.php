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

use App\Http\Controllers\Api\Payment\Promotion\MultiStepsPayment;
use App\Http\Controllers\Web\Front\Payment\HasPaymentRedirection;
use App\Http\Requests\Front\PackageRequest;
use App\Models\PaymentMethod;
use App\Models\Post;
use App\Models\Scopes\StrictActiveScope;
use App\Models\Scopes\VerifiedScope;
use App\Models\Scopes\ReviewedScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Larapen\LaravelMetaTags\Facades\MetaTag;

class PaymentController extends BaseController
{
	use MultiStepsPayment, HasPaymentRedirection;
	
	public $request;
	
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
		// Set payment settings for promotion packages (Multi-Steps Form)
		$this->setPaymentSettingsForPromotion();
	}
	
	/**
	 * Show the form
	 *
	 * @param $postId
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
	 */
	public function getForm($postId, Request $request)
	{
		// Check if the form type is 'Single-Step Form' and make redirection to it (permanently).
		if (isSingleStepFormEnabled()) {
			$url = urlGen()->editPost($postId);
			if ($url != request()->fullUrl()) {
				return redirect()->to($url, 301)->withHeaders(config('larapen.core.noCacheHeaders'));
			}
		}
		
		// Get auth user
		$authUser = auth()->user();
		
		// Get Post
		$post = null;
		if (!empty($authUser)) {
			$post = Post::query()
				->inCountry()
				->with([
					'possiblePayment' => fn ($q) => $q->with('package'),
					'paymentEndingLater',
				])
				->withoutGlobalScopes([VerifiedScope::class, ReviewedScope::class])
				->where('user_id', $authUser->getAuthIdentifier())
				->where('id', $postId)
				->first();
		}
		
		if (empty($post)) {
			abort(404, t('post_not_found'));
		}
		
		view()->share('post', $post);
		$this->shareNavItems($post);
		
		// Share the post's current active payment info (If exists)
		$this->getCurrentActivePaymentInfo($post);
		
		// Meta Tags
		MetaTag::set('title', t('update_my_listing'));
		MetaTag::set('description', t('update_my_listing'));
		
		// Get steps URLs & labels
		$previousStepUrl = urlGen()->post($post);
		$previousStepLabel = t('Skip');
		$formActionUrl = url()->current();
		$nextStepUrl = null;
		$nextStepLabel = t('Pay');
		
		// Share steps URLs & label variables
		view()->share('previousStepUrl', $previousStepUrl);
		view()->share('previousStepLabel', $previousStepLabel);
		view()->share('formActionUrl', $formActionUrl);
		view()->share('nextStepUrl', $nextStepUrl);
		view()->share('nextStepLabel', $nextStepLabel);
		
		return view('front.post.createOrEdit.multiSteps.edit.packages');
	}
	
	/**
	 * Submit the form
	 *
	 * @param $postId
	 * @param \App\Http\Requests\Front\PackageRequest $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function postForm($postId, PackageRequest $request): RedirectResponse
	{
		// Get auth user
		$authUser = auth()->user();
		
		// Get Post
		$post = null;
		if (!empty($authUser)) {
			$post = Post::query()
				->inCountry()
				->with([
					'possiblePayment' => fn ($q) => $q->with('package'),
					'paymentEndingLater',
				])
				->withoutGlobalScopes([VerifiedScope::class, ReviewedScope::class])
				->where('user_id', $authUser->getAuthIdentifier())
				->where('id', $postId)
				->first();
		}
		
		if (empty($post)) {
			abort(404, t('post_not_found'));
		}
		
		// Add required data in the request for API
		$inputArray = [
			'payable_type' => 'Post',
			'payable_id'   => $postId,
		];
		$request->merge($inputArray);
		
		// Check if the payment process has been triggered
		// NOTE: Payment bypass email or phone verification
		// ===| Make|send payment (if needed) |==============
		
		$postObj = $this->retrievePayableModel($request, $postId);
		if (!empty($postObj)) {
			$payResult = $this->isPaymentRequested($request, $postObj);
			if (data_get($payResult, 'success')) {
				return $this->sendPayment($request, $postObj);
			}
			if (data_get($payResult, 'failure')) {
				flash(data_get($payResult, 'message'))->error();
			}
		}
		
		// ===| If no payment is made (continue) |===========
		
		$isOfflinePayment = PaymentMethod::query()
			->where('name', 'offlinepayment')
			->where('id', $request->input('payment_method_id'))
			->exists();
		
		// Notification Message
		if (!$isOfflinePayment) {
			flash(t('no_payment_is_made'))->info();
		}
		
		// Get the next URL & Notification
		$nextUrl = urlGen()->post($post);
		
		return redirect()->to($nextUrl);
	}
}
