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

// Increase the server resources
$iniConfigFile = __DIR__ . '/../../../../../../../../Helpers/Functions/ini.php';
if (file_exists($iniConfigFile)) {
	$configForUpload = true;
	include_once $iniConfigFile;
}

use App\Http\Controllers\Api\Payment\RetrievePackageFeatures;
use App\Http\Controllers\Web\Front\Post\CreateOrEdit\Traits\PricingPageUrlTrait;
use App\Http\Requests\Front\PhotoRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Larapen\LaravelMetaTags\Facades\MetaTag;

class PhotoController extends BaseController
{
	use RetrievePackageFeatures;
	use PricingPageUrlTrait;
	
	public $data = [];
	public $package = null;
	
	/**
	 * @throws \App\Exceptions\Custom\CustomException
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
		$array = [
			new Middleware('only.ajax', only: ['delete']),
		];
		
		return array_merge(parent::middleware(), $array);
	}
	
	/**
	 * Common Queries
	 *
	 * @return void
	 */
	private function commonQueries(): void
	{
		// Get the selected package
		$this->package = $this->getSelectedPackage();
		view()->share('selectedPackage', $this->package);
		
		// Set the Package's pictures limit
		$this->getCurrentActivePaymentInfo(null, $this->package);
	}
	
	/**
	 * Show the listing's pictures form
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
		
		$data = [];
		
		// Get Post
		$post = null;
		if (auth()->check()) {
			// Get post - Call API endpoint
			$endpoint = '/posts/' . $postId;
			$queryParams = [
				'embed'               => 'pictures',
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
		
		if (empty($post)) {
			abort(404, t('post_not_found'));
		}
		
		view()->share('post', $post);
		$this->shareNavItems($post);
		
		// Set the Package's pictures limit
		if (!empty($this->package)) {
			$this->getCurrentActivePaymentInfo(null, $this->package);
		} else {
			// Share the post's current active payment info (If exists)
			// & Set the Package's pictures limit
			$this->getCurrentActivePaymentInfo($post);
		}
		
		// Meta Tags
		MetaTag::set('title', t('update_my_listing'));
		MetaTag::set('description', t('update_my_listing'));
		
		// Get steps URLs & labels
		$previousStepUrl = urlGen()->editPost($post);
		$previousStepUrl = urlQuery($previousStepUrl)->setParameters(request()->only(['packageId']))->toString();
		$previousStepLabel = t('Previous');
		$formActionUrl = request()->fullUrl();
		if (
			$this->countPackages > 0
			&& $this->countPaymentMethods > 0
		) {
			$nextStepUrl = urlGen()->editPostPayment($post);
			$nextStepUrl = urlQuery($nextStepUrl)->setParameters(request()->only(['packageId']))->toString();
			$nextStepLabel = t('Next');
		} else {
			$nextStepUrl = urlGen()->postUri($post);
			$nextStepLabel = t('Finish');
		}
		
		// Share steps URLs & label variables
		view()->share('previousStepUrl', $previousStepUrl);
		view()->share('previousStepLabel', $previousStepLabel);
		view()->share('formActionUrl', $formActionUrl);
		view()->share('nextStepUrl', $nextStepUrl);
		view()->share('nextStepLabel', $nextStepLabel);
		
		return view('front.post.createOrEdit.multiSteps.edit.photos', $data);
	}
	
	/**
	 * Update the listing's pictures
	 *
	 * @param $postId
	 * @param \App\Http\Requests\Front\PhotoRequest $request
	 * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
	 */
	public function postForm($postId, PhotoRequest $request): JsonResponse|RedirectResponse
	{
		// Add required data in the request for API
		$inputArray = [
			'count_packages'        => $this->countPackages ?? 0,
			'count_payment_methods' => $this->countPaymentMethods ?? 0,
			'post_id'               => $postId,
		];
		request()->merge($inputArray);
		
		// Call API endpoint
		$endpoint = '/pictures';
		$data = makeApiRequest(method: 'post', uri: $endpoint, data: request()->all(), files: $request->allFiles());
		
		// Parsing the API response
		$status = (int)data_get($data, 'status');
		$message = data_get($data, 'message', t('unknown_error'));
		
		// HTTP Error Found
		if (!data_get($data, 'isSuccessful')) {
			// AJAX Response
			if (isFromAjax()) {
				return ajaxResponse()->json(['error' => $message], $status);
			}
			
			flash($message)->error();
			$previousUrl = url()->previous();
			
			return redirect()->to($previousUrl)->withInput();
		}
		
		// Notification Message
		if (data_get($data, 'success')) {
			flash($message)->success();
		} else {
			// AJAX Response
			if (isFromAjax()) {
				return ajaxResponse()->json(['error' => $message], $status);
			}
			
			flash($message)->error();
		}
		
		$post = data_get($data, 'extra.post.result');
		
		// Get Next URL
		if (data_get($data, 'extra.steps.payment')) {
			$nextUrl = urlGen()->editPostPayment($post);
		} else {
			$nextUrl = urlGen()->post($post);
		}
		$nextStepLabel = data_get($data, 'extra.nextStepLabel');
		
		view()->share('nextStepUrl', $nextUrl);
		view()->share('nextStepLabel', $nextStepLabel);
		
		// AJAX Response
		if (isFromAjax()) {
			$data = data_get($data, 'extra.fileInput');
			
			return ajaxResponse()->json($data);
		}
		
		// Non AJAX Response
		return redirect()->to($nextUrl);
	}
	
	/**
	 * Delete a listing picture
	 *
	 * @param $postId
	 * @param $id
	 * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
	 */
	public function delete($postId, $id)
	{
		// Add required data in the request for API
		$inputArray = ['post_id' => $postId];
		request()->merge($inputArray);
		
		// Call API endpoint
		$endpoint = '/pictures/' . $id;
		$data = makeApiRequest(method: 'delete', uri: $endpoint, data: request()->all());
		
		// Parsing the API response
		$status = (int)data_get($data, 'status');
		$message = data_get($data, 'message', t('unknown_error'));
		
		$result = ['status' => 0];
		
		// HTTP Error Found
		if (!data_get($data, 'isSuccessful')) {
			if (isFromAjax()) {
				$result['error'] = $message;
				
				return ajaxResponse()->json($result, $status);
			}
			
			return redirect()->back();
		}
		
		// Notification Message
		if (data_get($data, 'success')) {
			if (isFromAjax()) {
				$result['status'] = 1;
				$result['message'] = $message;
				
				return ajaxResponse()->json($result);
			} else {
				flash($message)->success();
			}
		} else {
			if (isFromAjax()) {
				$result['error'] = $message;
				
				return ajaxResponse()->json($result, $status);
			} else {
				flash($message)->error();
			}
		}
		
		return redirect()->back();
	}
	
	/**
	 * Reorder the listing's pictures
	 *
	 * @param $postId
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function reorder($postId, Request $request): \Illuminate\Http\JsonResponse
	{
		$httpStatus = 200;
		$result = ['status' => 0, 'message' => null];
		
		$params = $request->input('params');
		
		if (
			isset($params['stack'])
			&& is_array($params['stack'])
			&& count($params['stack']) > 0
		) {
			$body = [];
			foreach ($params['stack'] as $position => $item) {
				if (array_key_exists('key', $item) && $item['key'] != '') {
					$body[] = [
						'id'       => $item['key'],
						'position' => $position,
					];
				}
			}
			
			if (!empty($body)) {
				$inputArray = [
					'post_id' => $postId,
					'body'    => json_encode($body),
				];
				request()->merge($inputArray);
				
				// Call API endpoint
				$endpoint = '/pictures/reorder';
				$headers = ['X-Action' => 'bulk'];
				$data = makeApiRequest(method: 'post', uri: $endpoint, data: $request->all(), headers: $headers);
				
				// Parsing the API response
				$httpStatus = (int)data_get($data, 'status');
				$message = data_get($data, 'message', t('unknown_error'));
				
				if (data_get($data, 'isSuccessful') && data_get($data, 'success')) {
					$result = [
						'status'  => 1,
						'message' => $message,
					];
				} else {
					$result['error'] = $message;
				}
			}
		}
		
		return ajaxResponse()->json($result, $httpStatus);
	}
}
