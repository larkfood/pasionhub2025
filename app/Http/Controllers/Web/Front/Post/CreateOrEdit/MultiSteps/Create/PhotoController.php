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

namespace App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Create;

// Increase the server resources
$iniConfigFile = __DIR__ . '/../../../../../../../../Helpers/Functions/ini.php';
if (file_exists($iniConfigFile)) {
	$configForUpload = true;
	include_once $iniConfigFile;
}

use App\Helpers\Files\TmpUpload;
use App\Http\Requests\Front\PhotoRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PhotoController extends BaseController
{
	/**
	 * Listing pictures' step
	 *
	 * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
	 */
	public function getForm(): View|RedirectResponse
	{
		// Return to the last unlocked step if the current step remains locked
		$currentStep = $this->getStepByKey(get_class($this));
		$lastUnlockedStepUrl = $this->getLastUnlockedStepUrlOnlyIfGivenStepIsLocked($currentStep);
		if (!empty($lastUnlockedStepUrl)) {
			return redirect()->to($lastUnlockedStepUrl)->withHeaders(config('larapen.core.noCacheHeaders'));
		}
		
		// Check if the 'Pricing Page' must be started first, and make redirection to it.
		$pricingUrl = $this->getPricingPage($this->getSelectedPackage());
		if (!empty($pricingUrl)) {
			return redirect()->to($pricingUrl)->withHeaders(config('larapen.core.noCacheHeaders'));
		}
		
		// Create an unique temporary ID
		if (!session()->has('uid')) {
			session()->put('uid', uniqueCode(9));
		}
		
		$picturesInput = session('picturesInput');
		
		// Get steps URLs & labels
		$previousStepUrl = $this->getPrevStepUrl($currentStep);
		$previousStepLabel = t('Previous');
		$formActionUrl = request()->fullUrl();
		if (
			isset($this->countPackages, $this->countPaymentMethods)
			&& $this->countPackages > 0
			&& $this->countPaymentMethods > 0
			&& doesNoPackageOrPremiumOneSelected()
		) {
			$nextStepUrl = $this->getNextStepUrl($currentStep);
			$nextStepLabel = t('Next');
		} else {
			$nextStepUrl = $this->getNextStepUrl($currentStep + 1);
			$nextStepLabel = t('submit');
		}
		
		// Share steps URLs & label variables
		view()->share('previousStepUrl', $previousStepUrl);
		view()->share('previousStepLabel', $previousStepLabel);
		view()->share('formActionUrl', $formActionUrl);
		view()->share('nextStepUrl', $nextStepUrl);
		view()->share('nextStepLabel', $nextStepLabel);
		
		return view('front.post.createOrEdit.multiSteps.create.photos', compact('picturesInput'));
	}
	
	/**
	 * Listing pictures' step (POST)
	 *
	 * @param \App\Http\Requests\Front\PhotoRequest $request
	 * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
	 */
	public function postForm(PhotoRequest $request): JsonResponse|RedirectResponse
	{
		if (!isFromAjax($request)) {
			// Return to the last unlocked step if the current step remains locked
			$currentStep = $this->getStepByKey(get_class($this));
			$lastUnlockedStepUrl = $this->getLastUnlockedStepUrlOnlyIfGivenStepIsLocked($currentStep);
			if (!empty($lastUnlockedStepUrl)) {
				return redirect()->to($lastUnlockedStepUrl)->withHeaders(config('larapen.core.noCacheHeaders'));
			}
		}
		
		$savedPicturesInput = (array)session('picturesInput');
		
		// Get default/global pictures limit
		$defaultPicturesLimit = (int)config('settings.listing_form.pictures_limit', 5);
		
		// Get the picture number limit
		$countExistingPictures = count($savedPicturesInput);
		$picturesLimit = $defaultPicturesLimit - $countExistingPictures;
		
		// Use unique ID to store post's pictures
		if (session()->has('uid')) {
			$this->tmpUploadDir = $this->tmpUploadDir . '/' . session('uid');
		}
		
		$picturesInput = [];
		
		// Save uploaded files
		$files = $request->file('pictures');
		if (is_array($files) && count($files) > 0) {
			foreach ($files as $key => $file) {
				if (empty($file)) {
					continue;
				}
				
				$picturesInput[] = TmpUpload::image($this->tmpUploadDir, $file);
				
				// Check the picture number limit
				if ($key >= ($picturesLimit - 1)) {
					break;
				}
			}
			
			$newPicturesInput = array_merge($savedPicturesInput, $picturesInput);
			
			session()->put('picturesInput', $newPicturesInput);
		}
		
		// AJAX response
		$data = [];
		$data['initialPreview'] = [];
		$data['initialPreviewConfig'] = [];
		if (isFromAjax($request)) {
			if (is_array($picturesInput) && count($picturesInput) > 0 && isset($this->disk)) {
				foreach ($picturesInput as $key => $filePath) {
					if (empty($filePath)) {
						continue;
					}
					
					// $pictureUrl = thumbParam($filePath)->setOption('picture-md')->url();
					// $pictureUrl = hasTemporaryPath($filePath) ? $this->disk->url($filePath) : $pictureUrl;
					$pictureUrl = thumbService($filePath)->resize('picture-md')->url();
					$deleteUrl = url('posts/create/photos/' . $key . '/delete');
					
					try {
						$fileSize = $this->disk->exists($filePath) ? (int)$this->disk->size($filePath) : 0;
					} catch (\Throwable $e) {
						$fileSize = 0;
					}
					
					// Build Bootstrap-FileInput plugin's parameters
					$data['initialPreview'][] = $pictureUrl;
					$data['initialPreviewConfig'][] = [
						'key'     => $key,
						'caption' => basename($filePath),
						'size'    => $fileSize,
						'url'     => $deleteUrl,
						'extra'   => ['id' => $key],
					];
				}
			}
			
			return response()->json($data);
		}
		
		// Redirect to the next page or Submit the form
		if (
			isset($this->countPackages, $this->countPaymentMethods)
			&& $this->countPackages > 0
			&& $this->countPaymentMethods > 0
			&& doesNoPackageOrPremiumOneSelected()
		) {
			if (is_array($picturesInput) && count($picturesInput) > 0) {
				flash(t('The pictures have been updated'))->success();
			}
			
			// Get the next URL
			$currentStep = $this->getStepByKey(get_class($this));
			$nextUrl = $this->getNextStepUrl($currentStep);
			
			return redirect()->to($nextUrl);
		} else {
			// Submit the form
			session()->flash('message', t('your_listing_is_created'));
			
			return $this->storeInputDataInDatabase($request);
		}
	}
	
	/**
	 * Remove a listing picture
	 *
	 * @param $pictureId
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
	 */
	public function removePicture($pictureId, Request $request): JsonResponse|RedirectResponse
	{
		$picturesInput = session('picturesInput');
		
		$message = t('The picture cannot be deleted');
		$result = [
			'status'  => 0,
			'message' => $message,
		];
		
		if (isset($picturesInput[$pictureId])) {
			$res = true;
			try {
				$this->removePictureWithItsThumbs($picturesInput[$pictureId]);
			} catch (\Throwable $e) {
				$res = false;
			}
			
			if ($res) {
				unset($picturesInput[$pictureId]);
				
				if (!empty($picturesInput)) {
					session()->put('picturesInput', $picturesInput);
				} else {
					session()->forget('picturesInput');
				}
				
				$message = t('The picture has been deleted');
				
				if (isFromAjax()) {
					$result['status'] = 1;
					$result['message'] = $message;
					
					return response()->json($result);
				} else {
					flash($message)->success();
					
					return redirect()->back();
				}
			}
		}
		
		if (isFromAjax()) {
			return response()->json($result);
		} else {
			flash($message)->error();
			
			return redirect()->back();
		}
	}
	
	/**
	 * Reorder the listing pictures
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function reorderPictures(Request $request): JsonResponse
	{
		$params = $request->input('params');
		
		$result = ['status' => 0];
		
		if (isset($params['stack']) && count($params['stack']) > 0) {
			// Use unique ID to store post's pictures
			if (session()->has('uid')) {
				$this->tmpUploadDir = $this->tmpUploadDir . '/' . session('uid');
			}
			
			$newPicturesInput = [];
			$statusOk = false;
			foreach ($params['stack'] as $position => $item) {
				if (array_key_exists('caption', $item) && !empty($item['caption'])) {
					$newPicturesInput[] = $this->tmpUploadDir . '/' . $item['caption'];
					$statusOk = true;
				}
			}
			if ($statusOk) {
				session()->put('picturesInput', $newPicturesInput);
				$result['status'] = 1;
				$result['message'] = t('Your picture has been reorder successfully');
			}
		}
		
		return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE);
	}
}
