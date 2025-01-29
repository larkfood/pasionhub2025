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

namespace App\Http\Requests\Admin\SettingRequest;

use App\Http\Requests\Admin\Request;
use App\Rules\SupportedFilesRule;

/*
 * Use request() instead of $this since this form request can be called from another
 */

class UploadRequest extends Request
{
	/**
	 * Prepare the data for validation (Custom)
	 * Note: This method need to be public
	 *
	 * @param array $input
	 * @return array
	 */
	public function customPrepareForValidation(array $input): array
	{
		// file_types
		if (array_key_exists('file_types', $input)) {
			$input['file_types'] = normalizeSeparatedList($input['file_types']);
		}
		
		// image_types
		if (array_key_exists('image_types', $input)) {
			$input['image_types'] = normalizeSeparatedList($input['image_types']);
		}
		
		// client_image_types
		if (array_key_exists('client_image_types', $input)) {
			$input['client_image_types'] = normalizeSeparatedList($input['client_image_types']);
		}
		
		return $input;
	}
	
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		// $request = request();
		
		return [
			'file_types'                => ['required', 'string', new SupportedFilesRule()],
			'min_file_size'             => ['integer'],
			'max_file_size'             => ['integer', 'gte:min_file_size'],
			'image_types'               => ['required', 'string', new SupportedFilesRule(typeGroup: 'image')],
			'image_quality'             => ['integer', 'min:10'],
			'client_image_types'        => ['required', 'string', new SupportedFilesRule(typeGroup: 'image', client: true)],
			'min_image_size'            => ['integer'],
			'max_image_size'            => ['integer', 'gte:min_image_size'],
			'img_resize_default_width'  => ['integer', 'min:300'],
			'img_resize_default_height' => ['integer', 'min:300'],
		];
	}
	
	/**
	 * @return array
	 */
	public function messages(): array
	{
		$messages = [];
		
		return array_merge(parent::messages(), $messages);
	}
	
	/**
	 * @return array
	 */
	public function attributes(): array
	{
		$attributes = [
			'file_types'                => trans('admin.file_types_label'),
			'min_file_size'             => trans('admin.min_file_size_label'),
			'max_file_size'             => trans('admin.max_file_size_label'),
			'image_types'               => trans('admin.image_types_label'),
			'image_quality'             => trans('admin.image_quality_label'),
			'min_image_size'            => trans('admin.min_image_size_label'),
			'max_image_size'            => trans('admin.max_image_size_label'),
			'img_resize_default_width'  => trans('admin.img_resize_width_label'),
			'img_resize_default_height' => trans('admin.img_resize_height_label'),
		];
		
		return array_merge(parent::attributes(), $attributes);
	}
}
