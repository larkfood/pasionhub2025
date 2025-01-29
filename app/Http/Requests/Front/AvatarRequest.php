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

namespace App\Http\Requests\Front;

use App\Http\Requests\Request;
use Illuminate\Support\Number;

class AvatarRequest extends Request
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize(): bool
	{
		$guard = getAuthGuard();
		
		return auth($guard)->check();
	}
	
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		$rules = [];
		
		$serverAllowedImageFormats = collect(getServerAllowedImageFormats())->join(',');
		$rules['photo_path'] = [
			'required',
			'file',
			'mimes:' . $serverAllowedImageFormats,
			'min:' . (int)config('settings.upload.min_image_size', 0),
			'max:' . (int)config('settings.upload.max_image_size', 1000),
		];
		
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
		
		$attributes['photo_path'] = strtolower(t('Photo'));
		
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
		
		// uploaded
		$maxSize = (int)config('settings.upload.max_image_size', 1000); // In KB
		$maxSize = $maxSize * 1024; // Convert KB to Bytes
		$msg = t('large_file_uploaded_error', [
			'field'   => strtolower(t('Photo')),
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
					'field'   => strtolower(t('Photo')),
					'maxSize' => Number::fileSize($serverMaxSize),
				]);
			}
		}
		
		$messages['photo_path.uploaded'] = $msg;
		
		return $messages;
	}
}
