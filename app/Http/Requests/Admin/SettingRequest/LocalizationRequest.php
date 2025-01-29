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
use App\Providers\AppService\ConfigTrait\LocalizationConfig;

/*
 * Use request() instead of $this since this form request can be called from another
 */

class LocalizationRequest extends Request
{
	use LocalizationConfig;
	
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
			'geoip_driver' => ['nullable', 'string'],
		];
		
		// Is GeoIP driver need to be validated?
		$isGeoipDriverTestEnabled = ($request->input('geoip_driver_test') == '1');
		
		// Get selected GeoIP driver
		$geoipDriver = $request->input('geoip_driver');
		if (empty($geoipDriver)) {
			return $rules;
		}
		
		$isGeoipEnabled = ($request->input('geoip_activation') == '1');
		if ($isGeoipEnabled) {
			// When a default country is specified, the geolocation feature need to be disabled
			if ($request->filled('default_country_code')) {
				$rules = array_merge($rules, [
					'empty_default_country' => ['required'],
				]);
			}
		} else {
			// When the geolocation feature is disabled a default country need to be specified
			// If a default country is not specified, users will be redirected to the countries list page
			if (!$request->filled('default_country_code')) {
				$rules = array_merge($rules, [
					'filled_default_country' => ['required'],
				]);
			}
		}
		
		// GeoIP driver's rules
		if ($geoipDriver == 'ipinfo') {
			$rules = array_merge($rules, [
				'ipinfo_token' => ['nullable'],
			]);
		}
		
		if ($geoipDriver == 'dbip') {
			$rules = array_merge($rules, [
				'dbip_api_key' => ['nullable'],
			]);
			if ($request->input('dbip_pro') == '1') {
				$rules['dbip_api_key'] = ['required'];
			}
		}
		
		if ($geoipDriver == 'ipbase') {
			$rules = array_merge($rules, [
				'ipbase_api_key' => ['required'],
			]);
		}
		
		if ($geoipDriver == 'ip2location') {
			$rules = array_merge($rules, [
				'ip2location_api_key' => ['required'],
			]);
		}
		
		if ($geoipDriver == 'ipapi') {
			//...
		}
		
		if ($geoipDriver == 'ipapico') {
			//...
		}
		
		if ($geoipDriver == 'ipgeolocation') {
			$rules = array_merge($rules, [
				'ipgeolocation_api_key' => ['required'],
			]);
		}
		
		if ($geoipDriver == 'iplocation') {
			$rules = array_merge($rules, [
				'iplocation_api_key' => ['nullable'],
			]);
			if ($request->input('iplocation_pro') == '1') {
				$rules['iplocation_api_key'] = ['required'];
			}
		}
		
		if ($geoipDriver == 'ipstack') {
			$rules = array_merge($rules, [
				'ipstack_access_key' => ['nullable'],
			]);
			if ($request->input('ipstack_pro') == '1') {
				$rules['ipstack_access_key'] = ['required'];
			}
		}
		
		if ($geoipDriver == 'maxmind_api') {
			$rules = array_merge($rules, [
				'maxmind_api_account_id'  => ['required'],
				'maxmind_api_license_key' => ['required'],
			]);
		}
		
		if ($geoipDriver == 'maxmind_database') {
			$rules = array_merge($rules, [
				'maxmind_database_license_key' => ['nullable'],
			]);
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
		
		// Check GeoIP fetching parameters
		if ($isGeoipDriverTestEnabled && $emptyRequiredFields->isEmpty()) {
			$settings = $request->all();
			
			$errorMessage = $this->testGeoIPConfig(true, $settings);
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
		$messages = [
			'empty_default_country.required'  => trans('admin.activating_geolocation_validator'),
			'filled_default_country.required' => trans('admin.disabling_geolocation_validator'),
		];
		
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
			'geoip_activation'             => trans('admin.geoip_activation_label'),
			'geoip_driver'                 => trans('admin.geoip_driver_label'),
			'ipinfo_token'                 => trans('admin.ipinfo_token_label'),
			'dbip_pro'                     => trans('admin.geoip_driver_pro_label'),
			'dbip_api_key'                 => trans('admin.dbip_api_key_label'),
			'ipbase_api_key'               => trans('admin.ipbase_api_key_label'),
			'ip2location_api_key'          => trans('admin.ip2location_api_key_label'),
			'ipapi_pro'                    => trans('admin.geoip_driver_pro_label'),
			'ipgeolocation_api_key'        => trans('admin.ipgeolocation_api_key_label'),
			'iplocation_api_key'           => trans('admin.iplocation_api_key_label'),
			'ipstack_access_key'           => trans('admin.geoip_driver_pro_label'),
			'ipstack_pro'                  => trans('admin.ipstack_access_key_label'),
			'maxmind_api_account_id'       => trans('admin.maxmind_api_account_id_label'),
			'maxmind_api_license_key'      => trans('admin.maxmind_api_license_key_label'),
			'maxmind_database_license_key' => trans('admin.maxmind_database_license_key_label'),
		];
		
		return array_merge(parent::attributes(), $attributes);
	}
}
