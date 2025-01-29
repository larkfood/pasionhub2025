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
use App\Http\Requests\Traits\HasCaptchaInput;
use App\Http\Requests\Traits\HasEmailInput;
use App\Http\Requests\Traits\HasPasswordInput;
use App\Http\Requests\Traits\HasPhoneInput;
use App\Rules\BetweenRule;
use App\Rules\UsernameIsAllowedRule;
use App\Rules\UsernameIsValidRule;
use Illuminate\Support\Number;
use Illuminate\Validation\Rule;

class UserRequest extends Request
{
	use HasEmailInput, HasPhoneInput, HasPasswordInput, HasCaptchaInput;
	
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize(): bool
	{
		if (in_array($this->method(), ['POST', 'CREATE'])) {
			return true;
		} else {
			$guard = getAuthGuard();
			
			return auth($guard)->check();
		}
	}
	
	/**
	 * Prepare the data for validation.
	 *
	 * @return void
	 */
	protected function prepareForValidation(): void
	{
		// Don't apply this to the Admin Panel
		if (isAdminPanel()) {
			return;
		}
		
		$input = $this->all();
		
		// name
		if ($this->filled('name')) {
			$input['name'] = singleLineStringCleaner($this->input('name'));
			$input['name'] = preventStringContainingOnlyNumericChars($input['name']);
		}
		
		// auth_field
		$input['auth_field'] = getAuthField();
		
		// phone
		$input = $this->preparePhoneForValidation($this, $input);
		
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
		
		$authFields = array_keys(getAuthFields());
		
		// CREATE
		if (in_array($this->method(), ['POST', 'CREATE'])) {
			$rules = $this->storeRules($authFields);
		}
		
		// UPDATE
		if (in_array($this->method(), ['PUT', 'PATCH', 'UPDATE'])) {
			$rules = $this->updateRules($authFields);
		}
		
		// photo_path
		if ($this->hasFile('photo_path')) {
			$serverAllowedImageFormats = collect(getServerAllowedImageFormats())->join(',');
			$rules['photo_path'] = [
				'file',
				'mimes:' . $serverAllowedImageFormats,
				'min:' . (int)config('settings.upload.min_image_size', 0),
				'max:' . (int)config('settings.upload.max_image_size', 1000),
			];
		}
		
		return $rules;
	}
	
	/**
	 * @param array $authFields
	 * @return array
	 */
	private function storeRules(array $authFields): array
	{
		$rules = [
			'name'          => ['required', new BetweenRule(2, 200)],
			'country_code'  => ['sometimes', 'required', 'not_in:0'],
			'auth_field'    => ['required', Rule::in($authFields)],
			'phone'         => ['max:30'],
			'phone_country' => ['required_with:phone'],
			'password'      => ['required', 'confirmed'],
			'accept_terms'  => ['accepted'],
		];
		
		$phoneIsEnabledAsAuthField = (config('settings.sms.enable_phone_as_auth_field') == '1');
		$phoneNumberIsRequired = ($phoneIsEnabledAsAuthField && $this->input('auth_field') == 'phone');
		
		// email
		$emailIsRequired = (!$phoneNumberIsRequired);
		if ($emailIsRequired) {
			$rules['email'][] = 'required';
		}
		$rules = $this->emailRules($rules);
		if ($this->filled('email')) {
			if (isDemoEnv()) {
				$rules['email'][] = Rule::notIn(getDemoEmailAddresses());
			}
			$rules['email'][] = 'unique:users,email';
		}
		
		// phone
		if ($phoneNumberIsRequired) {
			$rules['phone'][] = 'required';
		}
		$rules = $this->phoneRules($rules);
		if ($this->filled('phone')) {
			$rules['phone'][] = 'unique:users,phone';
		}
		
		// username
		$usernameIsEnabled = !config('larapen.core.disable.username');
		if ($usernameIsEnabled) {
			if ($this->filled('username')) {
				$rules['username'] = [
					'between:3,50',
					'unique:users,username',
					new UsernameIsValidRule(),
					new UsernameIsAllowedRule(),
				];
			}
		}
		
		// password
		$rules = $this->passwordRules($rules);
		
		return $this->captchaRules($rules);
	}
	
	/**
	 * @param array $authFields
	 * @return array
	 */
	private function updateRules(array $authFields): array
	{
		$guard = getAuthGuard();
		$authUser = auth($guard)->user();
		
		$rules = [
			'name'          => ['required', 'max:100'],
			'auth_field'    => ['required', Rule::in($authFields)],
			'phone'         => ['max:30'],
			'phone_country' => ['required_with:phone'],
			'username'      => [new UsernameIsValidRule()],
		];
		
		// Check if these fields have changed
		$emailChanged = ($this->filled('email') && $this->input('email') != $authUser->email);
		$phoneChanged = ($this->filled('phone') && $this->input('phone') != $authUser->phone);
		$usernameChanged = ($this->filled('username') && $this->input('username') != $authUser->username);
		
		$phoneIsEnabledAsAuthField = (config('settings.sms.enable_phone_as_auth_field') == '1');
		$phoneNumberIsRequired = ($phoneIsEnabledAsAuthField && $this->input('auth_field') == 'phone');
		
		// email
		$emailIsRequired = (!$phoneNumberIsRequired);
		if ($emailIsRequired) {
			$rules['email'][] = 'required';
		}
		$rules = $this->emailRules($rules);
		if ($emailChanged) {
			$rules['email'][] = 'unique:users,email';
		}
		
		// phone
		if ($phoneNumberIsRequired) {
			$rules['phone'][] = 'required';
		}
		$rules = $this->phoneRules($rules);
		if ($phoneChanged) {
			$rules['phone'][] = 'unique:users,phone';
		}
		
		// username
		if ($this->filled('username')) {
			$rules['username'][] = 'between:3,50';
		}
		if ($usernameChanged) {
			$rules['username'][] = 'required';
			$rules['username'][] = new UsernameIsAllowedRule();
			$rules['username'][] = 'unique:users,username';
		}
		
		// password
		$rules = $this->passwordRules($rules);
		if ($this->filled('password')) {
			$rules['password'][] = 'confirmed';
		}
		
		if ($this->filled('user_accept_terms') && $this->input('user_accept_terms') != 1) {
			$rules['accept_terms'] = ['accepted'];
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
		
		if ($this->hasFile('photo_path')) {
			$attributes['photo_path'] = strtolower(t('Photo'));
		}
		
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
		
		if ($this->hasFile('photo_path')) {
			// uploaded
			$maxSize = (int)config('settings.upload.max_image_size', 1000); // In KB
			$maxSize = $maxSize * 1024;                                     // Convert KB to Bytes
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
		}
		
		return $messages;
	}
}
