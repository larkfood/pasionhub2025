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
 * settings.mail.option
 */

class MailSetting
{
	public static function getValues($value, $disk)
	{
		if (empty($value)) {
			
			$value['sendmail_path'] = env('MAIL_SENDMAIL_PATH');
			
		} else {
			
			if (!array_key_exists('sendmail_path', $value)) {
				$value['sendmail_path'] = env('MAIL_SENDMAIL_PATH');
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
		$mailDrivers = (array)config('larapen.options.mail');
		
		// Get the drivers selectors list as JS objects
		$mailDriversSelectorsJson = collect($mailDrivers)
			->keys()
			->mapWithKeys(fn ($item) => [$item => '.' . $item])
			->toJson();
		
		$fields = [
			[
				'name'              => 'driver',
				'label'             => trans('admin.mail_driver_label'),
				'type'              => 'select2_from_array',
				'options'           => $mailDrivers,
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
				'newline'           => true,
			],
		];
		
		// sendmail
		if (array_key_exists('sendmail', $mailDrivers)) {
			$fields = array_merge($fields, [
				[
					'name'              => 'driver_sendmail_title',
					'type'              => 'custom_html',
					'value'             => trans('admin.driver_sendmail_title'),
					'wrapperAttributes' => [
						'class' => 'col-md-12 sendmail',
					],
				],
				[
					'name'              => 'driver_sendmail_info',
					'type'              => 'custom_html',
					'value'             => trans('admin.driver_sendmail_info'),
					'wrapperAttributes' => [
						'class' => 'col-md-12 sendmail',
					],
				],
				[
					'name'              => 'sendmail_path',
					'label'             => trans('admin.sendmail_path_label'),
					'type'              => 'text',
					'hint'              => trans('admin.sendmail_path_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-12 sendmail',
					],
				],
				[
					'name'              => 'sendmail_email_sender',
					'label'             => trans('admin.mail_email_sender_label'),
					'type'              => 'email',
					'default'           => config('settings.app.email'),
					'required'          => true,
					'hint'              => trans('admin.mail_email_sender_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 sendmail',
					],
				],
			]);
		}
		
		// smtp
		if (array_key_exists('smtp', $mailDrivers)) {
			$fields = array_merge($fields, [
				[
					'name'              => 'driver_smtp_title',
					'type'              => 'custom_html',
					'value'             => trans('admin.driver_smtp_title'),
					'wrapperAttributes' => [
						'class' => 'col-md-12 smtp',
					],
				],
				[
					'name'              => 'driver_smtp_info',
					'type'              => 'custom_html',
					'value'             => trans('admin.driver_smtp_info'),
					'wrapperAttributes' => [
						'class' => 'col-md-12 smtp',
					],
				],
				[
					'name'              => 'smtp_host',
					'label'             => trans('admin.mail_smtp_host_label'),
					'type'              => 'text',
					'required'          => true,
					'hint'              => trans('admin.mail_smtp_host_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 smtp',
					],
				],
				[
					'name'              => 'smtp_port',
					'label'             => trans('admin.mail_smtp_port_label'),
					'type'              => 'number',
					'required'          => true,
					'hint'              => trans('admin.mail_smtp_port_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 smtp',
					],
				],
				[
					'name'              => 'smtp_username',
					'label'             => trans('admin.mail_smtp_username_label'),
					'type'              => 'text',
					'hint'              => trans('admin.mail_smtp_username_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 smtp',
					],
				],
				[
					'name'              => 'smtp_password',
					'label'             => trans('admin.mail_smtp_password_label'),
					'type'              => 'text',
					'hint'              => trans('admin.mail_smtp_password_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 smtp',
					],
				],
				[
					'name'              => 'smtp_encryption',
					'label'             => trans('admin.mail_smtp_encryption_label'),
					'type'              => 'text',
					'hint'              => trans('admin.mail_smtp_encryption_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 smtp',
					],
				],
				[
					'name'              => 'smtp_email_sender',
					'label'             => trans('admin.mail_email_sender_label'),
					'type'              => 'email',
					'default'           => config('settings.app.email'),
					'required'          => true,
					'hint'              => trans('admin.mail_email_sender_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 smtp',
					],
				],
				[
					'name'              => 'verify_peer',
					'label'             => trans('admin.verify_peer_label'),
					'type'              => 'checkbox_switch',
					'hint'              => trans('admin.verify_peer_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 mt-2',
					],
				],
			]);
		}
		
		// mailgun
		if (array_key_exists('mailgun', $mailDrivers)) {
			$fields = array_merge($fields, [
				[
					'name'              => 'driver_mailgun_title',
					'type'              => 'custom_html',
					'value'             => trans('admin.driver_mailgun_title'),
					'wrapperAttributes' => [
						'class' => 'col-md-12 mailgun',
					],
				],
				[
					'name'              => 'driver_mailgun_info',
					'type'              => 'custom_html',
					'value'             => trans('admin.driver_mailgun_info'),
					'wrapperAttributes' => [
						'class' => 'col-md-12 mailgun',
					],
				],
				[
					'name'              => 'mailgun_domain',
					'label'             => trans('admin.mail_mailgun_domain_label'),
					'type'              => 'text',
					'required'          => true,
					'wrapperAttributes' => [
						'class' => 'col-md-6 mailgun',
					],
				],
				[
					'name'              => 'mailgun_secret',
					'label'             => trans('admin.mail_mailgun_secret_label'),
					'type'              => 'text',
					'required'          => true,
					'wrapperAttributes' => [
						'class' => 'col-md-6 mailgun',
					],
				],
				[
					'name'              => 'mailgun_endpoint',
					'label'             => trans('admin.mail_mailgun_endpoint_label'),
					'type'              => 'text',
					'default'           => 'api.mailgun.net',
					'required'          => true,
					'wrapperAttributes' => [
						'class' => 'col-md-6 mailgun',
					],
				],
				[
					'name'              => 'mailgun_host',
					'label'             => trans('admin.mail_smtp_host_label'),
					'type'              => 'text',
					'required'          => true,
					'hint'              => trans('admin.mail_smtp_host_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 mailgun',
					],
				],
				[
					'name'              => 'mailgun_port',
					'label'             => trans('admin.mail_smtp_port_label'),
					'type'              => 'number',
					'required'          => true,
					'hint'              => trans('admin.mail_smtp_port_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 mailgun',
					],
				],
				[
					'name'              => 'mailgun_username',
					'label'             => trans('admin.mail_smtp_username_label'),
					'type'              => 'text',
					'required'          => true,
					'hint'              => trans('admin.mail_smtp_username_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 mailgun',
					],
				],
				[
					'name'              => 'mailgun_password',
					'label'             => trans('admin.mail_smtp_password_label'),
					'type'              => 'text',
					'required'          => true,
					'hint'              => trans('admin.mail_smtp_password_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 mailgun',
					],
				],
				[
					'name'              => 'mailgun_encryption',
					'label'             => trans('admin.mail_smtp_encryption_label'),
					'type'              => 'text',
					'required'          => true,
					'hint'              => trans('admin.mail_smtp_encryption_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 mailgun',
					],
				],
				[
					'name'              => 'mailgun_email_sender',
					'label'             => trans('admin.mail_email_sender_label'),
					'type'              => 'email',
					'default'           => config('settings.app.email'),
					'required'          => true,
					'hint'              => trans('admin.mail_email_sender_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 mailgun',
					],
				],
			]);
		}
		
		// postmark
		if (array_key_exists('postmark', $mailDrivers)) {
			$fields = array_merge($fields, [
				[
					'name'              => 'driver_postmark_title',
					'type'              => 'custom_html',
					'value'             => trans('admin.driver_postmark_title'),
					'wrapperAttributes' => [
						'class' => 'col-md-12 postmark',
					],
				],
				[
					'name'              => 'driver_postmark_info',
					'type'              => 'custom_html',
					'value'             => trans('admin.driver_postmark_info'),
					'wrapperAttributes' => [
						'class' => 'col-md-12 postmark',
					],
				],
				[
					'name'              => 'postmark_token',
					'label'             => trans('admin.mail_postmark_token_label'),
					'type'              => 'text',
					'required'          => true,
					'wrapperAttributes' => [
						'class' => 'col-md-6 postmark',
					],
				],
				[
					'name'              => 'postmark_host',
					'label'             => trans('admin.mail_smtp_host_label'),
					'type'              => 'text',
					'required'          => true,
					'hint'              => trans('admin.mail_smtp_host_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 postmark',
					],
				],
				[
					'name'              => 'postmark_port',
					'label'             => trans('admin.mail_smtp_port_label'),
					'type'              => 'number',
					'required'          => true,
					'hint'              => trans('admin.mail_smtp_port_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 postmark',
					],
				],
				[
					'name'              => 'postmark_username',
					'label'             => trans('admin.mail_smtp_username_label'),
					'type'              => 'text',
					'required'          => true,
					'hint'              => trans('admin.mail_smtp_username_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 postmark',
					],
				],
				[
					'name'              => 'postmark_password',
					'label'             => trans('admin.mail_smtp_password_label'),
					'type'              => 'text',
					'required'          => true,
					'hint'              => trans('admin.mail_smtp_password_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 postmark',
					],
				],
				[
					'name'              => 'postmark_encryption',
					'label'             => trans('admin.mail_smtp_encryption_label'),
					'type'              => 'text',
					'required'          => true,
					'hint'              => trans('admin.mail_smtp_encryption_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 postmark',
					],
				],
				[
					'name'              => 'postmark_email_sender',
					'label'             => trans('admin.mail_email_sender_label'),
					'type'              => 'email',
					'default'           => config('settings.app.email'),
					'required'          => true,
					'hint'              => trans('admin.mail_email_sender_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 postmark',
					],
				],
			]);
		}
		
		// ses
		if (array_key_exists('ses', $mailDrivers)) {
			$fields = array_merge($fields, [
				[
					'name'              => 'driver_ses_title',
					'type'              => 'custom_html',
					'value'             => trans('admin.driver_ses_title'),
					'wrapperAttributes' => [
						'class' => 'col-md-12 ses',
					],
				],
				[
					'name'              => 'driver_ses_info',
					'type'              => 'custom_html',
					'value'             => trans('admin.driver_ses_info'),
					'wrapperAttributes' => [
						'class' => 'col-md-12 ses',
					],
				],
				[
					'name'              => 'ses_key',
					'label'             => trans('admin.mail_ses_key_label'),
					'type'              => 'text',
					'required'          => true,
					'wrapperAttributes' => [
						'class' => 'col-md-6 ses',
					],
				],
				[
					'name'              => 'ses_secret',
					'label'             => trans('admin.mail_ses_secret_label'),
					'type'              => 'text',
					'required'          => true,
					'wrapperAttributes' => [
						'class' => 'col-md-6 ses',
					],
				],
				[
					'name'              => 'ses_region',
					'label'             => trans('admin.mail_ses_region_label'),
					'type'              => 'text',
					'required'          => true,
					'wrapperAttributes' => [
						'class' => 'col-md-6 ses',
					],
				],
				[
					'name'              => 'ses_token',
					'label'             => trans('admin.mail_ses_token_label'),
					'type'              => 'text',
					'required'          => false,
					'hint'              => trans('admin.mail_ses_token_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 ses',
					],
				],
				[
					'name'              => 'ses_host',
					'label'             => trans('admin.mail_smtp_host_label'),
					'type'              => 'text',
					'required'          => true,
					'hint'              => trans('admin.mail_smtp_host_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 ses',
					],
				],
				[
					'name'              => 'ses_port',
					'label'             => trans('admin.mail_smtp_port_label'),
					'type'              => 'number',
					'required'          => true,
					'hint'              => trans('admin.mail_smtp_port_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 ses',
					],
				],
				[
					'name'              => 'ses_username',
					'label'             => trans('admin.mail_smtp_username_label'),
					'type'              => 'text',
					'required'          => true,
					'hint'              => trans('admin.mail_smtp_username_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 ses',
					],
				],
				[
					'name'              => 'ses_password',
					'label'             => trans('admin.mail_smtp_password_label'),
					'type'              => 'text',
					'required'          => true,
					'hint'              => trans('admin.mail_smtp_password_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 ses',
					],
				],
				[
					'name'              => 'ses_encryption',
					'label'             => trans('admin.mail_smtp_encryption_label'),
					'type'              => 'text',
					'required'          => true,
					'hint'              => trans('admin.mail_smtp_encryption_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 ses',
					],
				],
				[
					'name'              => 'ses_email_sender',
					'label'             => trans('admin.mail_email_sender_label'),
					'type'              => 'email',
					'default'           => config('settings.app.email'),
					'required'          => true,
					'hint'              => trans('admin.mail_email_sender_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 ses',
					],
				],
			]);
		}
		
		// sparkpost
		if (array_key_exists('sparkpost', $mailDrivers)) {
			$fields = array_merge($fields, [
				[
					'name'              => 'driver_sparkpost_title',
					'type'              => 'custom_html',
					'value'             => trans('admin.driver_sparkpost_title'),
					'wrapperAttributes' => [
						'class' => 'col-md-12 sparkpost',
					],
				],
				[
					'name'              => 'driver_sparkpost_info',
					'type'              => 'custom_html',
					'value'             => trans('admin.driver_sparkpost_info'),
					'wrapperAttributes' => [
						'class' => 'col-md-12 sparkpost',
					],
				],
				[
					'name'              => 'sparkpost_secret',
					'label'             => trans('admin.mail_sparkpost_secret_label'),
					'type'              => 'text',
					'required'          => true,
					'wrapperAttributes' => [
						'class' => 'col-md-6 sparkpost',
					],
				],
				[
					'name'              => 'sparkpost_host',
					'label'             => trans('admin.mail_smtp_host_label'),
					'type'              => 'text',
					'required'          => true,
					'hint'              => trans('admin.mail_smtp_host_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 sparkpost',
					],
				],
				[
					'name'              => 'sparkpost_port',
					'label'             => trans('admin.mail_smtp_port_label'),
					'type'              => 'number',
					'required'          => true,
					'hint'              => trans('admin.mail_smtp_port_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 sparkpost',
					],
				],
				[
					'name'              => 'sparkpost_username',
					'label'             => trans('admin.mail_smtp_username_label'),
					'type'              => 'text',
					'required'          => true,
					'hint'              => trans('admin.mail_smtp_username_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 sparkpost',
					],
				],
				[
					'name'              => 'sparkpost_password',
					'label'             => trans('admin.mail_smtp_password_label'),
					'type'              => 'text',
					'required'          => true,
					'hint'              => trans('admin.mail_smtp_password_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 sparkpost',
					],
				],
				[
					'name'              => 'sparkpost_encryption',
					'label'             => trans('admin.mail_smtp_encryption_label'),
					'type'              => 'text',
					'required'          => true,
					'hint'              => trans('admin.mail_smtp_encryption_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 sparkpost',
					],
				],
				[
					'name'              => 'sparkpost_email_sender',
					'label'             => trans('admin.mail_email_sender_label'),
					'type'              => 'email',
					'default'           => config('settings.app.email'),
					'required'          => true,
					'hint'              => trans('admin.mail_email_sender_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 sparkpost',
					],
				],
			]);
		}
		
		// resend
		if (array_key_exists('resend', $mailDrivers)) {
			$fields = array_merge($fields, [
				[
					'name'              => 'driver_resend_title',
					'type'              => 'custom_html',
					'value'             => trans('admin.driver_resend_title'),
					'wrapperAttributes' => [
						'class' => 'col-md-12 resend',
					],
				],
				[
					'name'              => 'driver_resend_info',
					'type'              => 'custom_html',
					'value'             => trans('admin.driver_resend_info'),
					'wrapperAttributes' => [
						'class' => 'col-md-12 resend',
					],
				],
				[
					'name'              => 'resend_api_key',
					'label'             => trans('admin.mail_resend_api_key_label'),
					'type'              => 'text',
					'required'          => true,
					'hint'              => trans('admin.mail_resend_api_key_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 resend',
					],
				],
				[
					'name'              => 'resend_email_sender',
					'label'             => trans('admin.mail_email_sender_label'),
					'type'              => 'email',
					'default'           => config('settings.app.email'),
					'required'          => true,
					'hint'              => trans('admin.mail_email_sender_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 resend',
					],
				],
			]);
		}
		
		// mailersend
		if (array_key_exists('mailersend', $mailDrivers)) {
			$fields = array_merge($fields, [
				[
					'name'              => 'driver_mailersend_title',
					'type'              => 'custom_html',
					'value'             => trans('admin.driver_mailersend_title'),
					'wrapperAttributes' => [
						'class' => 'col-md-12 mailersend',
					],
				],
				[
					'name'              => 'driver_mailersend_info',
					'type'              => 'custom_html',
					'value'             => trans('admin.driver_mailersend_info'),
					'wrapperAttributes' => [
						'class' => 'col-md-12 mailersend',
					],
				],
				[
					'name'              => 'mailersend_api_key',
					'label'             => trans('admin.mail_mailersend_api_key_label'),
					'type'              => 'text',
					'required'          => true,
					'hint'              => trans('admin.mail_mailersend_api_key_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 mailersend',
					],
				],
				[
					'name'              => 'mailersend_email_sender',
					'label'             => trans('admin.mail_email_sender_label'),
					'type'              => 'email',
					'default'           => config('settings.app.email'),
					'required'          => true,
					'hint'              => trans('admin.mail_email_sender_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 mailersend',
					],
				],
			]);
		}
		
		// brevo
		if (array_key_exists('brevo', $mailDrivers)) {
			$fields = array_merge($fields, [
				[
					'name'              => 'driver_brevo_title',
					'type'              => 'custom_html',
					'value'             => trans('admin.driver_brevo_title'),
					'wrapperAttributes' => [
						'class' => 'col-md-12 brevo',
					],
				],
				[
					'name'              => 'driver_brevo_info',
					'type'              => 'custom_html',
					'value'             => trans('admin.driver_brevo_info'),
					'wrapperAttributes' => [
						'class' => 'col-md-12 brevo',
					],
				],
				[
					'name'              => 'brevo_api_key',
					'label'             => trans('admin.mail_brevo_api_key_label'),
					'type'              => 'text',
					'required'          => true,
					'hint'              => trans('admin.mail_brevo_api_key_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 brevo',
					],
				],
				[
					'name'              => 'brevo_email_sender',
					'label'             => trans('admin.mail_email_sender_label'),
					'type'              => 'email',
					'default'           => config('settings.app.email'),
					'required'          => true,
					'hint'              => trans('admin.mail_email_sender_hint'),
					'wrapperAttributes' => [
						'class' => 'col-md-6 brevo',
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
					'content' => trans('admin.mail_driver_test_info', ['alwaysTo' => trans('admin.email_always_to_label')]),
				]),
			],
			[
				'name'              => 'driver_test',
				'label'             => trans('admin.driver_test_label'),
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.mail_driver_test_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6 mt-2',
				],
			],
			[
				'name'              => 'email_always_to',
				'label'             => trans('admin.email_always_to_label'),
				'type'              => 'email',
				'default'           => config('settings.app.email'),
				'required'          => true,
				'hint'              => trans('admin.email_always_to_hint', ['option' => trans('admin.driver_test_label')]),
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
				'name'  => 'mail_notification_types_title',
				'type'  => 'custom_html',
				'value' => trans('admin.mail_notification_types_title'),
			],
			[
				'name'  => 'email_verification',
				'label' => trans('admin.email_verification_label'),
				'type'  => 'checkbox_switch',
				'hint'  => trans('admin.email_verification_hint'),
			],
			[
				'name'  => 'confirmation',
				'label' => trans('admin.settings_mail_confirmation_label'),
				'type'  => 'checkbox_switch',
				'hint'  => trans('admin.settings_mail_confirmation_hint'),
			],
			[
				'name'  => 'admin_notification',
				'label' => trans('admin.settings_mail_admin_notification_label'),
				'type'  => 'checkbox_switch',
				'hint'  => trans('admin.settings_mail_admin_notification_hint'),
			],
			[
				'name'  => 'payment_notification',
				'label' => trans('admin.settings_mail_payment_notification_label'),
				'type'  => 'checkbox_switch',
				'hint'  => trans('admin.settings_mail_payment_notification_hint'),
			],
		]);
		
		return addOptionsGroupJavaScript(__NAMESPACE__, __CLASS__, $fields, [
			'mailDriversSelectorsJson' => $mailDriversSelectorsJson,
		]);
	}
}
