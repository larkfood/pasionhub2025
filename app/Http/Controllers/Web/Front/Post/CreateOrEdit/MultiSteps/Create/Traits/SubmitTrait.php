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

namespace App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Create\Traits;

use App\Helpers\Files\Upload;
use App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Create\FinishController;
use App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Create\PostController;
use App\Models\CategoryField;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait SubmitTrait
{
	/**
	 * Store all input data in database
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	protected function storeInputDataInDatabase(Request $request): RedirectResponse
	{
		// Get all saved input data
		$postInput = (array)session('postInput');
		$picturesInput = (array)session('picturesInput');
		$paymentInput = (array)session('paymentInput');
		
		if (empty($postInput)) {
			$postStep = $this->getStepByKey(PostController::class);
			$postStepUrl = $this->getNextStepUrl($postStep);
			
			return redirect()->to($postStepUrl);
		}
		
		// Create the global input to send for database saving
		$inputArray = $postInput;
		if (isset($inputArray['category_id'], $inputArray['cf'])) {
			$fields = CategoryField::getFields($inputArray['category_id']);
			if ($fields->count() > 0) {
				foreach ($fields as $field) {
					if ($field->type == 'file') {
						$cfFilePath = $inputArray['cf'][$field->id] ?? null;
						if (!empty($cfFilePath)) {
							if (hasTemporaryPath($cfFilePath)) {
								$inputArray['cf'][$field->id] = Upload::fromPath($cfFilePath);
							}
						}
					}
				}
			}
		}
		
		$inputArray['pictures'] = [];
		if (!empty($picturesInput)) {
			foreach ($picturesInput as $filePath) {
				if (!empty($filePath)) {
					if (hasTemporaryPath($filePath)) {
						$uploadedFile = Upload::fromPath($filePath);
						$inputArray['pictures'][] = $uploadedFile;
					}
				}
			}
		}
		$inputArray = array_merge($inputArray, $paymentInput);
		
		request()->merge($inputArray);
		
		// Set the pictures files in the current request (from the saved input variable)
		// Note: In that case file needs to be retrieved using $request->files->all() instead of $request->allFiles()
		$uploadedPictures = $inputArray['pictures'] ?? [];
		if (!empty($uploadedPictures)) {
			if (is_array($uploadedPictures)) {
				request()->files->set('pictures', $uploadedPictures);
			}
		}
		
		// Call API endpoint
		$endpoint = '/posts';
		$data = makeApiRequest(method: 'post', uri: $endpoint, data: request()->all(), files: request()->files->all());
		
		// dd($data);
		
		// Parsing the API response
		$message = data_get($data, 'message', t('unknown_error'));
		
		// HTTP Error Found
		if (!data_get($data, 'isSuccessful')) {
			flash($message)->error();
			
			if (data_get($data, 'extra.previousUrl')) {
				return redirect()->to(data_get($data, 'extra.previousUrl'))->withInput($request->except('pictures'));
			} else {
				return redirect()->back()->withInput($request->except('pictures'));
			}
		}
		
		// Get the listing ID
		$postId = data_get($data, 'result.id');
		
		// Notification Message
		if (data_get($data, 'success')) {
			session()->put('message', $message);
			
			// Save the listing's ID in session
			if (!empty($postId)) {
				$request->session()->put('postId', $postId);
			}
			
			// Clear Temporary Inputs & Files
			$this->clearTemporaryInput();
		} else {
			flash($message)->error();
			
			return redirect()->back()->withInput($request->except('pictures'));
		}
		
		// Get Listing Resource
		$post = data_get($data, 'result');
		
		abort_if(empty($post), 404, t('post_not_found'));
		
		// Get the next URL
		$nextStep = $this->getStepByKey(FinishController::class);
		$nextUrl = $this->getStepUrl($nextStep);
		
		if (!empty($paymentInput)) {
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
		}
		
		if (
			data_get($data, 'extra.sendEmailVerification.emailVerificationSent')
			|| data_get($data, 'extra.sendPhoneVerification.phoneVerificationSent')
		) {
			// Save the Next URL before verification
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
		
		$nextUrl = urlQuery($nextUrl)
			->setParameters(request()->only(['packageId']))
			->toString();
		
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
