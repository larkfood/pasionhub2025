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

use App\Helpers\Files\TmpUpload;
use App\Http\Requests\Front\PostRequest;
use App\Models\CategoryField;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PostController extends BaseController
{
	/**
	 * Listing's step
	 *
	 * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
	 */
	public function getForm(): View|RedirectResponse
	{
		// Check if the 'Pricing Page' must be started first, and make redirection to it.
		$pricingUrl = $this->getPricingPage($this->getSelectedPackage());
		if (!empty($pricingUrl)) {
			return redirect()->to($pricingUrl)->withHeaders(config('larapen.core.noCacheHeaders'));
		}
		
		// Check if the form type is 'Single-Step Form' and make redirection to it (permanently).
		if (isSingleStepFormEnabled()) {
			$url = urlGen()->addPost();
			if ($url != request()->fullUrl()) {
				return redirect()->to($url, 301)->withHeaders(config('larapen.core.noCacheHeaders'));
			}
		}
		
		// Create an unique temporary ID
		if (!session()->has('cfUid')) {
			session()->put('cfUid', 'cf-' . uniqueCode(9));
		}
		
		$postInput = session('postInput');
		
		// Get steps URLs & labels
		$previousStepUrl = null;
		$previousStepLabel = null;
		$formActionUrl = request()->fullUrl();
		$nextStepUrl = null;
		$nextStepLabel = t('Next');
		
		// Share steps URLs & label variables
		view()->share('previousStepUrl', $previousStepUrl);
		view()->share('previousStepLabel', $previousStepLabel);
		view()->share('formActionUrl', $formActionUrl);
		view()->share('nextStepUrl', $nextStepUrl);
		view()->share('nextStepLabel', $nextStepLabel);
		
		return view('front.post.createOrEdit.multiSteps.create.post', compact('postInput'));
	}
	
	/**
	 * Listing's step (POST)
	 *
	 * @param \App\Http\Requests\Front\PostRequest $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function postForm(PostRequest $request): RedirectResponse
	{
		$postInput = $request->except($this->unwantedFields());
		
		// Use unique ID to store post's pictures
		if (session()->has('cfUid')) {
			$this->cfTmpUploadDir = $this->cfTmpUploadDir . '/' . session('cfUid');
		}
		
		// Save uploaded files
		// Get Category's Fields details
		$fields = CategoryField::getFields($request->input('category_id'));
		if ($fields->count() > 0) {
			foreach ($fields as $field) {
				if ($field->type == 'file') {
					if ($request->hasFile('cf.' . $field->id)) {
						// Get the file
						$file = $request->file('cf.' . $field->id);
						
						// Check if the file is valid
						if (!$file->isValid()) {
							continue;
						}
						
						$postInput['cf'][$field->id] = TmpUpload::file($this->cfTmpUploadDir, $file);
					}
				}
			}
		}
		
		session()->put('postInput', $postInput);
		
		// Get the next URL
		$currentStep = $this->getStepByKey(get_class($this));
		$nextUrl = $this->getNextStepUrl($currentStep);
		
		return redirect()->to($nextUrl);
	}
}
