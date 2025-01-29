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
use App\Providers\AppService\ConfigTrait\SmsConfig;

/*
 * Use request() instead of $this since this form request can be called from another
 */

class SmsRequest extends Request
{
	use SmsConfig;
	
	private ?string $validDriverParamsRequiredMessage = null;
	
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		$request = request();
		
		$rules = [
			'driver' => ['nullable', 'string'],
		];
		
		// Is SMS driver need to be validated?
		$isSmsDriverTestEnabled = ($request->input('driver_test') == '1');
		
		// Get selected SMS driver
		$smsDriver = $request->input('driver');
		if (empty($smsDriver)) {
			return $rules;
		}
		
		// SMS driver's rules
		if ($smsDriver == 'vonage') {
			$rules = array_merge($rules, [
				'vonage_key'            => ['required'],
				'vonage_secret'         => ['required'],
				'vonage_application_id' => ['required'],
				'vonage_from'           => ['required'],
			]);
		}
		
		if ($smsDriver == 'twilio') {
			$rules = array_merge($rules, [
				'twilio_username'        => ['required'],
				'twilio_password'        => ['required'],
				'twilio_account_sid'     => ['required'],
				'twilio_auth_token'      => ['required'],
				'twilio_from'            => ['required'],
				'twilio_alpha_sender'    => ['nullable'],
				'twilio_sms_service_sid' => ['required'],
				'twilio_debug_to'        => ['nullable'],
			]);
			
			if ($request->filled('twilio_auth_token')) {
				$rules['twilio_username'] = [];
				$rules['twilio_password'] = [];
			}
			if ($request->filled('twilio_username') && $request->filled('twilio_password')) {
				$rules['twilio_auth_token'] = [];
			}
		}
		
		if ($isSmsDriverTestEnabled) {
			$rules['sms_to'] = ['required'];
		}
		
		// Get the required fields, then check if required fields are not empty in the request
		$emptyRequiredFields = collect($rules)
			->filter(function ($rule) {
				if (is_array($rule)) {
					return in_array('required', $rule);
				} else if (is_string($rule)) {
					return str_contains($rule, 'required');
				}
				
				return false;
			})->filter(fn ($rule, $field) => empty($request->input($field)));
		
		// Check SMS sending parameters
		if ($isSmsDriverTestEnabled && $emptyRequiredFields->isEmpty()) {
			if (!empty($this->appName)) {
				config()->set('settings.app.name', $this->appName);
			}
			$smsTo = $request->input('sms_to');
			$settings = $request->all();
			
			$errorMessage = $this->testSmsConfig(true, $smsTo, $settings);
			if (!empty($errorMessage)) {
				$rules = array_merge($rules, [
					'valid_driver_params' => 'required',
				]);
				$this->validDriverParamsRequiredMessage = $errorMessage;
			}
		}
		
		return $rules;
	}
	
	/**
	 * @return array
	 */
	public function messages(): array
	{
		$messages = [];
		
		if (!empty($this->validDriverParamsRequiredMessage)) {
			$messages['valid_driver_params.required'] = $this->validDriverParamsRequiredMessage;
		}
		
		return array_merge(parent::messages(), $messages);
	}
	
	/**
	 * @return array
	 */
	public function attributes(): array
	{
		$attributes = [
			'driver'                 => trans('admin.SMS Driver'),
			'vonage_key'             => trans('admin.Vonage Key'),
			'vonage_secret'          => trans('admin.Vonage Secret'),
			'vonage_application_id'  => trans('admin.vonage_application_id'),
			'vonage_from'            => trans('admin.Vonage From'),
			'twilio_username'        => trans('admin.twilio_username_label'),
			'twilio_password'        => trans('admin.twilio_password_label'),
			'twilio_account_sid'     => trans('admin.twilio_account_sid_label'),
			'twilio_auth_token'      => trans('admin.twilio_auth_token_label'),
			'twilio_from'            => trans('admin.twilio_from_label'),
			'twilio_alpha_sender'    => trans('admin.twilio_alpha_sender_label'),
			'twilio_sms_service_sid' => trans('admin.twilio_sms_service_sid_label'),
			'twilio_debug_to'        => trans('admin.twilio_debug_to_label'),
		];
		
		return array_merge(parent::attributes(), $attributes);
	}
}
