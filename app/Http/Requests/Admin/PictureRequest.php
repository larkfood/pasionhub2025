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

namespace App\Http\Requests\Admin;

use Illuminate\Support\Number;

class PictureRequest extends Request
{
	/**
	 * Prepare the data for validation.
	 *
	 * @return void
	 */
	protected function prepareForValidation(): void
	{
		$input = $this->all();
		
		// file_path
		if ($this->filled('file_path')) {
			$input['file_path'] = $this->input('file_path');
			
			if (str_contains($input['file_path'], config('larapen.media.picture'))) {
				$input['file_path'] = null;
			}
		}
		
		request()->merge($input); // Required!
		$this->merge($input);
	}
	
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		$rules = [];
		
		// Require 'pictures' if exists
		if ($this->file('file_path')) {
			$serverAllowedImageFormats = collect(getServerAllowedImageFormats())->join(',');
			$rules['file_path'] = [
				'file',
				'mimes:' . $serverAllowedImageFormats,
				'min:' . (int)config('settings.upload.min_image_size', 0),
				'max:' . (int)config('settings.upload.max_image_size', 1000),
			];
		} else {
			$rules['file_path'][] = 'required';
		}
		
		return $rules;
	}
	
	/**
	 * Get custom attributes for validator errors.
	 *
	 * @return array
	 */
	public function attributes(): array
	{
		$attributes = [];
		
		$attributes['file_path'] = trans('validation.attributes.picture');
		
		return $attributes;
	}
	
	/**
	 * Get custom messages for validator errors.
	 *
	 * @return array
	 */
	public function messages(): array
	{
		$messages = [];
		
		if ($this->file('file_path')) {
			// $files = $this->file('file_path');
			// uploaded
			$maxSize = (int)config('settings.upload.max_image_size', 1000); // In KB
			$maxSize = $maxSize * 1024; // Convert KB to Bytes
			$msg = t('large_file_uploaded_error', [
				'field'   => t('picture'),
				'maxSize' => Number::fileSize($maxSize),
			]);
			
			$uploadMaxFilesizeStr = @ini_get('upload_max_filesize');
			$postMaxSizeStr = @ini_get('post_max_size');
			if (!empty($uploadMaxFilesizeStr) && !empty($postMaxSizeStr)) {
				$uploadMaxFilesize = forceToInt($uploadMaxFilesizeStr);
				$postMaxSize = forceToInt($postMaxSizeStr);
				
				$serverMaxSize = min($uploadMaxFilesize, $postMaxSize);
				$serverMaxSize = $serverMaxSize * 1024 * 1024; // Convert MB to KB to Bytes
				if ($serverMaxSize < $maxSize) {
					$msg = t('large_file_uploaded_error_system', [
						'field'   => t('picture'),
						'maxSize' => Number::fileSize($serverMaxSize),
					]);
				}
			}
			
			$messages['file_path.uploaded'] = $msg;
		}
		
		return $messages;
	}
}
