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

namespace App\Providers\AppService\ConfigTrait;

use App\Models\Permission;
use App\Models\User;
use App\Notifications\ExampleSms;
use Illuminate\Support\Facades\Notification;

trait SmsConfig
{
	private function updateSmsConfig(?array $settings = [], ?string $appName = null): void
	{
		if (empty($settings)) {
			return;
		}
		
		// SMS
		$driver = $settings['driver'] ?? null;
		config()->set('settings.sms.driver', $driver);
		
		// Vonage
		if ($driver == 'vonage') {
			$apiKey = $settings['vonage_key'] ?? null;
			$apiSecret = $settings['vonage_secret'] ?? null;
			$applicationId = $settings['vonage_application_id'] ?? null;
			$smsFrom = $settings['vonage_from'] ?? null;
			$appName = $appName ?? config('app.name');
			$version = config('version.app');
			
			config()->set('vonage.api_key', $apiKey);
			config()->set('vonage.api_secret', $apiSecret);
			config()->set('vonage.application_id', $applicationId);
			config()->set('vonage.sms_from', $smsFrom);
			config()->set('vonage.app.name', env('VONAGE_APP_NAME', $appName));
			config()->set('vonage.app.version', env('VONAGE_APP_VERSION', $version));
		}
		
		// Twilio
		if ($driver == 'twilio') {
			$username = $settings['twilio_username'] ?? null;
			$password = $settings['twilio_password'] ?? null;
			$authToken = $settings['twilio_auth_token'] ?? null;
			$accountSid = $settings['twilio_account_sid'] ?? null;
			$from = $settings['twilio_from'] ?? null;
			$alphanumericSender = $settings['twilio_alpha_sender'] ?? null;
			$smsServiceSid = $settings['twilio_sms_service_sid'] ?? null;
			$debugTo = $settings['twilio_debug_to'] ?? null;
			
			config()->set('twilio-notification-channel.username', $username);
			config()->set('twilio-notification-channel.password', $password);
			config()->set('twilio-notification-channel.auth_token', $authToken);
			config()->set('twilio-notification-channel.account_sid', $accountSid);
			config()->set('twilio-notification-channel.from', $from);
			config()->set('twilio-notification-channel.alphanumeric_sender', $alphanumericSender);
			config()->set('twilio-notification-channel.sms_service_sid', $smsServiceSid);
			config()->set('twilio-notification-channel.debug_to', $debugTo);
		}
	}
	
	/**
	 * @param bool $isTestEnabled
	 * @param string|null $smsTo
	 * @param array|null $settings
	 * @param bool $fallbackSmsToAdminUsers
	 * @return string|null
	 */
	private function testSmsConfig(bool $isTestEnabled, ?string $smsTo, ?array $settings = [], bool $fallbackSmsToAdminUsers = false): ?string
	{
		if (!$isTestEnabled) {
			return null;
		}
		
		// Apply updated config
		$this->updateSmsConfig($settings);
		
		// Get the test recipient
		$smsTo = !empty($smsTo) ? $smsTo : config('settings.app.phone_number');
		
		/*
		 * Send Example SMS
		 */
		$driver = config('settings.sms.driver');
		$message = null;
		try {
			if (!empty($smsTo)) {
				Notification::route($driver, $smsTo)->notify(new ExampleSms());
			} else {
				if ($fallbackSmsToAdminUsers) {
					$admins = User::permission(Permission::getStaffPermissions())->get();
					if ($admins->count() > 0) {
						Notification::send($admins, new ExampleSms());
					}
				} else {
					$message = trans('admin.sms_to_missing');
				}
			}
		} catch (\Throwable $e) {
			$message = $e->getMessage();
		}
		
		if (!empty($message)) {
			$exceptionMessageFormat = ' ERROR: <span class="fw-bold">%s</span>';
			$message = sprintf($exceptionMessageFormat, $message);
			$message = trans('admin.sms_sending_error', ['driver' => $driver]) . $message;
		}
		
		return $message;
	}
}
