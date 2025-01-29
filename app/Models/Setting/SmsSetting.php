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

namespace App\Models\Setting;

/*
 * settings.sms.option
 */

class SmsSetting
{
	public static function getValues($value, $disk)
	{
		$enablePhoneAsAuthField = $value['enable_phone_as_auth_field'] ?? '0';
		$phoneVerification = $value['phone_verification'] ?? '0';
		$smsConfirmationReceiving = $value['confirmation'] ?? '0';
		$smsMessageReceiving = $value['messenger_notifications'] ?? '0';
		
		$smsSendingIsRequired = (
			$enablePhoneAsAuthField == '1'
			&& ($phoneVerification == '1' || $smsConfirmationReceiving == '1' || $smsMessageReceiving == '1')
		);
		$phoneValidator = $smsSendingIsRequired ? 'isPossibleMobileNumber' : 'isPossiblePhoneNumber';
		
		if (empty($value)) {
			
			$value['enable_phone_as_auth_field'] = '0';
			$value['default_auth_field'] = 'email';
			$value['phone_of_countries'] = 'local';
			$value['phone_validator'] = $phoneValidator;
			
			$value['vonage_key'] = env('VONAGE_KEY', '');
			$value['vonage_secret'] = env('VONAGE_SECRET', '');
			$value['vonage_application_id'] = env('VONAGE_APPLICATION_ID', '');
			$value['vonage_from'] = env('VONAGE_SMS_FROM', '');
			
			$value['twilio_username'] = env('TWILIO_USERNAME', '');
			$value['twilio_password'] = env('TWILIO_PASSWORD', '');
			$value['twilio_auth_token'] = env('TWILIO_AUTH_TOKEN', '');
			$value['twilio_account_sid'] = env('TWILIO_ACCOUNT_SID', '');
			$value['twilio_from'] = env('TWILIO_FROM', '');
			$value['twilio_alpha_sender'] = env('TWILIO_ALPHA_SENDER', '');
			$value['twilio_sms_service_sid'] = env('TWILIO_SMS_SERVICE_SID', '');
			$value['twilio_debug_to'] = env('TWILIO_DEBUG_TO', '');
			
			$value['phone_verification'] = '1';
			
		} else {
			
			if (!array_key_exists('enable_phone_as_auth_field', $value)) {
				$value['enable_phone_as_auth_field'] = '0';
			}
			if (!array_key_exists('default_auth_field', $value)) {
				$value['default_auth_field'] = 'email';
			}
			if (!array_key_exists('phone_of_countries', $value)) {
				$value['phone_of_countries'] = 'local';
			}
			if (!array_key_exists('phone_validator', $value)) {
				$value['phone_validator'] = $phoneValidator;
			}
			
			if (!array_key_exists('enable_phone_as_auth_field', $value)) {
				$value['enable_phone_as_auth_field'] = env('DISABLE_PHONE') ? '0' : '1'; // from old method
			}
			if (!array_key_exists('vonage_key', $value)) {
				$value['vonage_key'] = env('VONAGE_KEY', '');
			}
			if (!array_key_exists('vonage_secret', $value)) {
				$value['vonage_secret'] = env('VONAGE_SECRET', '');
			}
			if (!array_key_exists('vonage_application_id', $value)) {
				$value['vonage_application_id'] = env('VONAGE_APPLICATION_ID', '');
			}
			if (!array_key_exists('vonage_from', $value)) {
				$value['vonage_from'] = env('VONAGE_SMS_FROM', '');
			}
			
			if (!array_key_exists('twilio_username', $value)) {
				$value['twilio_username'] = env('TWILIO_USERNAME', '');
			}
			if (!array_key_exists('twilio_password', $value)) {
				$value['twilio_password'] = env('TWILIO_PASSWORD', '');
			}
			if (!array_key_exists('twilio_auth_token', $value)) {
				$value['twilio_auth_token'] = env('TWILIO_AUTH_TOKEN', '');
			}
			if (!array_key_exists('twilio_account_sid', $value)) {
				$value['twilio_account_sid'] = env('TWILIO_ACCOUNT_SID', '');
			}
			if (!array_key_exists('twilio_from', $value)) {
				$value['twilio_from'] = env('TWILIO_FROM', '');
			}
			if (!array_key_exists('twilio_alpha_sender', $value)) {
				$value['twilio_alpha_sender'] = env('TWILIO_ALPHA_SENDER', '');
			}
			if (!array_key_exists('twilio_sms_service_sid', $value)) {
				$value['twilio_sms_service_sid'] = env('TWILIO_SMS_SERVICE_SID', '');
			}
			if (!array_key_exists('twilio_debug_to', $value)) {
				$value['twilio_debug_to'] = env('TWILIO_DEBUG_TO', '');
			}
			
			if (!array_key_exists('phone_verification', $value)) {
				$value['phone_verification'] = '1';
			}
			
		}
		
		return $value;
	}
	
	public static function setValues($value, $setting)
	{
		return $value;
	}
	
	public static function getFields($diskName): array
	{
		// Get Drivers List
		$smsDrivers = (array)config('larapen.options.sms');
		
		// Get the drivers selectors list as JS objects
		$smsDriversSelectorsJson = collect($smsDrivers)
			->keys()
			->mapWithKeys(fn ($item) => [$item => '.' . $item])
			->toJson();
		
		$fields = [
			[
				'name'              => 'enable_phone_as_auth_field',
				'label'             => trans('admin.enable_phone_as_auth_field_label'),
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.card_light_inverse', [
					'content' => trans('admin.enable_phone_as_auth_field_hint', [
						'phone_verification_label' => trans('admin.phone_verification_label'),
					]),
				]),
				'wrapperAttributes' => [
					'class' => 'col-md-6 mt-3',
				],
			],
			[
				'name'              => 'default_auth_field',
				'label'             => trans('admin.default_auth_field_label'),
				'type'              => 'select2_from_array',
				'options'           => [
					'email' => t('email_address'),
					'phone' => t('phone_number'),
				],
				'default'           => 'email',
				'hint'              => trans('admin.card_light_inverse', [
					'content' => trans('admin.default_auth_field_hint', [
						'enable_phone_as_auth_field_label' => trans('admin.enable_phone_as_auth_field_label'),
						'email'                            => t('email_address'),
						'phone'                            => t('phone_number'),
					]),
				]),
				'wrapperAttributes' => [
					'class' => 'col-md-6 auth-field-el',
				],
				'newline'           => true,
			],
		];
		
		$phoneOfCountriesOptions = [
			'local'     => trans('admin.phone_of_countries_op_1'),
			'activated' => trans('admin.phone_of_countries_op_2'),
			'all'       => trans('admin.phone_of_countries_op_3'),
		];
		$phoneValidatorOptions = [
			'none'                   => trans('admin.phone_validator_op_0'),
			'isValidMobileNumber'    => trans('admin.phone_validator_op_1'),
			'isPossibleMobileNumber' => trans('admin.phone_validator_op_2'),
			'isValidPhoneNumber'     => trans('admin.phone_validator_op_3'),
			'isPossiblePhoneNumber'  => trans('admin.phone_validator_op_4'),
		];
		$fields = array_merge($fields, [
			[
				'name'              => 'phone_of_countries',
				'label'             => trans('admin.phone_of_countries_label'),
				'type'              => 'select2_from_array',
				'options'           => $phoneOfCountriesOptions,
				'hint'              => trans('admin.card_light_inverse', [
					'content' => trans('admin.phone_of_countries_hint', $phoneOfCountriesOptions),
				]),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'phone_validator',
				'label'             => trans('admin.phone_validator_label'),
				'type'              => 'select2_from_array',
				'options'           => $phoneValidatorOptions,
				'hint'              => trans('admin.card_light_inverse', [
					'content' => trans('admin.phone_validator_hint', array_merge($phoneValidatorOptions, [
						'enable_phone_as_auth_field_label' => trans('admin.enable_phone_as_auth_field_label'),
					])),
				]),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			
			[
				'name'              => 'default_auth_field_sep',
				'type'              => 'custom_html',
				'value'             => '<hr style="border: 1px dashed #EFEFEF;" class="my-3">',
				'wrapperAttributes' => [
					'class' => 'col-12',
				],
			],
		]);
		
		// driver
		$fields = array_merge($fields, [
			[
				'name'              => 'driver',
				'label'             => trans('admin.SMS Driver'),
				'type'              => 'select2_from_array',
				'options'           => $smsDrivers,
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
				'newline'           => true,
			],
		]);
		
		// vonage
		if (array_key_exists('vonage', $smsDrivers)) {
			$fields = array_merge($fields, [
				[
					'name'              => 'driver_vonage_title',
					'type'              => 'custom_html',
					'value'             => trans('admin.driver_vonage_title'),
					'wrapperAttributes' => [
						'class' => 'col-md-12 vonage',
					],
				],
				[
					'name'              => 'driver_vonage_info',
					'type'              => 'custom_html',
					'value'             => trans('admin.driver_vonage_info'),
					'wrapperAttributes' => [
						'class' => 'col-md-12 vonage',
					],
				],
				[
					'name'              => 'vonage_key',
					'label'             => trans('admin.Vonage Key'),
					'type'              => 'text',
					'required'          => true,
					'wrapperAttributes' => [
						'class' => 'col-md-6 vonage',
					],
				],
				[
					'name'              => 'vonage_secret',
					'label'             => trans('admin.Vonage Secret'),
					'type'              => 'text',
					'required'          => true,
					'wrapperAttributes' => [
						'class' => 'col-md-6 vonage',
					],
				],
				[
					'name'              => 'vonage_application_id',
					'label'             => trans('admin.vonage_application_id'),
					'type'              => 'text',
					'required'          => true,
					'wrapperAttributes' => [
						'class' => 'col-md-6 vonage',
					],
				],
				[
					'name'              => 'vonage_from',
					'label'             => trans('admin.Vonage From'),
					'type'              => 'text',
					'required'          => true,
					'wrapperAttributes' => [
						'class' => 'col-md-6 vonage',
					],
				],
			]);
		}
		
		// twilio
		if (array_key_exists('twilio', $smsDrivers)) {
			$fields = array_merge($fields, [
				[
					'name'              => 'driver_twilio_title',
					'type'              => 'custom_html',
					'value'             => trans('admin.driver_twilio_title'),
					'wrapperAttributes' => [
						'class' => 'col-md-12 twilio',
					],
				],
				[
					'name'              => 'driver_twilio_info',
					'type'              => 'custom_html',
					'value'             => trans('admin.driver_twilio_info'),
					'wrapperAttributes' => [
						'class' => 'col-md-12 twilio',
					],
				],
				[
					'name'              => 'twilio_username',
					'label'             => trans('admin.twilio_username_label'),
					'type'              => 'text',
					'required'          => true,
					'hint'              => trans('admin.twilio_username_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 twilio',
					],
				],
				[
					'name'              => 'twilio_password',
					'label'             => trans('admin.twilio_password_label'),
					'type'              => 'text',
					'required'          => true,
					'hint'              => trans('admin.twilio_password_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 twilio',
					],
				],
				[
					'name'              => 'twilio_account_sid',
					'label'             => trans('admin.twilio_account_sid_label'),
					'type'              => 'text',
					'required'          => true,
					'hint'              => trans('admin.twilio_account_sid_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 twilio',
					],
				],
				[
					'name'              => 'twilio_auth_token',
					'label'             => trans('admin.twilio_auth_token_label'),
					'type'              => 'text',
					'required'          => true,
					'hint'              => trans('admin.twilio_auth_token_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 twilio',
					],
				],
				[
					'name'              => 'twilio_from',
					'label'             => trans('admin.twilio_from_label'),
					'type'              => 'text',
					'required'          => true,
					'hint'              => trans('admin.twilio_from_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 twilio',
					],
				],
				[
					'name'              => 'twilio_alpha_sender',
					'label'             => trans('admin.twilio_alpha_sender_label'),
					'type'              => 'text',
					'hint'              => trans('admin.twilio_alpha_sender_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 twilio',
					],
				],
				[
					'name'              => 'twilio_sms_service_sid',
					'label'             => trans('admin.twilio_sms_service_sid_label'),
					'type'              => 'text',
					'required'          => true,
					'hint'              => trans('admin.twilio_sms_service_sid_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 twilio',
					],
				],
				[
					'name'              => 'twilio_debug_to',
					'label'             => trans('admin.twilio_debug_to_label'),
					'type'              => 'text',
					'hint'              => trans('admin.twilio_debug_to_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 twilio',
					],
				],
			]);
		}
		
		$fields = array_merge($fields, [
			[
				'name'  => 'driver_test_title',
				'type'  => 'custom_html',
				'value' => trans('admin.driver_test_title'),
			],
			[
				'name'  => 'driver_test_info',
				'type'  => 'custom_html',
				'value' => trans('admin.card_light', [
					'content' => trans('admin.sms_driver_test_info', ['smsTo' => trans('admin.sms_to_label')]),
				]),
			],
			[
				'name'              => 'driver_test',
				'label'             => trans('admin.driver_test_label'),
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.sms_driver_test_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6 mt-2',
				],
			],
			[
				'name'              => 'sms_to',
				'label'             => trans('admin.sms_to_label'),
				'type'              => 'tel',
				'default'           => config('settings.app.phone_number'),
				'required'          => true,
				'hint'              => trans('admin.sms_to_hint', ['option' => trans('admin.driver_test_label')]),
				'wrapperAttributes' => [
					'class' => 'col-md-6 driver-test',
				],
			],
		]);
		
		if (config('settings.optimization.queue_driver') != 'sync') {
			$fields = array_merge($fields, [
				[
					'name'  => 'queue_notifications',
					'type'  => 'custom_html',
					'value' => trans('admin.card_light_warning', [
						'content' => trans('admin.queue_notifications', ['queueOptionUrl' => admin_url('settings/find/optimization')]),
					]),
				]
			]);
		}
		
		$fields = array_merge($fields, [
			[
				'name'  => 'sms_notification_types_title',
				'type'  => 'custom_html',
				'value' => trans('admin.sms_notification_types_title'),
			],
			[
				'name'  => 'phone_verification',
				'label' => trans('admin.phone_verification_label'),
				'type'  => 'checkbox_switch',
				'hint'  => trans('admin.phone_verification_hint', [
						'email_verification_label' => trans('admin.email_verification_label'),
					]) . '<br>' . trans('admin.sms_sending_requirements'),
			],
			[
				'name'  => 'confirmation',
				'label' => trans('admin.settings_sms_confirmation_label'),
				'type'  => 'checkbox_switch',
				'hint'  => trans('admin.settings_sms_confirmation_hint') . '<br>' . trans('admin.sms_sending_requirements'),
			],
			[
				'name'  => 'messenger_notifications',
				'label' => trans('admin.messenger_notifications_label'),
				'type'  => 'checkbox_switch',
				'hint'  => trans('admin.messenger_notifications_hint') . '<br>' . trans('admin.sms_sending_requirements'),
			],
		]);
		
		return addOptionsGroupJavaScript(__NAMESPACE__, __CLASS__, $fields, [
			'smsDriversSelectorsJson' => $smsDriversSelectorsJson,
		]);
	}
}
