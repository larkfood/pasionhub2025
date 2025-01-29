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

namespace App\Http\Requests;

use App\Http\Requests\Traits\ErrorOutputFormat;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

abstract class Request extends FormRequest
{
	use ErrorOutputFormat;
	
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return true;
	}
	
	/**
	 * Handle a failed validation attempt.
	 *
	 * @param Validator $validator
	 * @throws ValidationException
	 */
	protected function failedValidation(Validator $validator)
	{
		if (isFromApi() || isFromAjax()) {
			// Get Errors
			$errors = (new ValidationException($validator))->errors();
			
			// Add a specific json attributes for 'bootstrap-fileinput' plugin
			$hasFileinputField = (
				str_contains(get_called_class(), 'PhotoRequest')
				|| str_contains(get_called_class(), 'AvatarRequest')
			);
			if ($hasFileinputField) {
				// NOTE: 'bootstrap-fileinput' need 'error' (text) element & the optional 'errorkeys' (array) element
				$data = [
					'error' => $this->fileinputFormatError($errors),
				];
			} else {
				if (isFromApi()) {
					$message = doesRequestIsFromWebClient()
						? $this->webFormatError($errors)
						: $this->apiFormatError($errors);
				} else {
					$message = isFromAjax()
						? $this->simpleFormatError($errors)
						: $this->webFormatError($errors);
				}
				
				$data = [
					'success' => false,
					'message' => $message,
					'errors'  => $errors,
				];
			}
			
			throw new HttpResponseException(response()->json($data, Response::HTTP_UNPROCESSABLE_ENTITY));
		}
		
		parent::failedValidation($validator);
	}
}
