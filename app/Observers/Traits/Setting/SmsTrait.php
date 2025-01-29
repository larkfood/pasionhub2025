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

namespace App\Observers\Traits\Setting;

use App\Helpers\DotenvEditor;

trait SmsTrait
{
	/**
	 * Updating
	 *
	 * @param $setting
	 * @param $original
	 * @return void
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	public function smsUpdating($setting, $original): void
	{
		$this->saveParametersInEnvFile($setting);
	}
	
	/**
	 * Save SMS Settings in the /.env file
	 *
	 * @param $setting
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	private function saveParametersInEnvFile($setting): void
	{
		$envFileHasChanged = false;
		
		if (
			!DotenvEditor::keyExists('VONAGE_KEY')
			&& !DotenvEditor::keyExists('VONAGE_SECRET')
			&& !DotenvEditor::keyExists('VONAGE_APPLICATION_ID')
			&& !DotenvEditor::keyExists('VONAGE_SMS_FROM')
			&& !DotenvEditor::keyExists('TWILIO_USERNAME')
			&& !DotenvEditor::keyExists('TWILIO_PASSWORD')
			&& !DotenvEditor::keyExists('TWILIO_AUTH_TOKEN')
			&& !DotenvEditor::keyExists('TWILIO_ACCOUNT_SID')
			&& !DotenvEditor::keyExists('TWILIO_FROM')
			&& !DotenvEditor::keyExists('TWILIO_ALPHA_SENDER')
			&& !DotenvEditor::keyExists('TWILIO_SMS_SERVICE_SID')
			&& !DotenvEditor::keyExists('TWILIO_DEBUG_TO')
		) {
			DotenvEditor::addEmpty();
			$envFileHasChanged = true;
		}
		
		if (array_key_exists('vonage_key', $setting->value)) {
			if (!empty($setting->value['vonage_key'])) {
				DotenvEditor::setKey('VONAGE_KEY', $setting->value['vonage_key']);
			} else {
				DotenvEditor::deleteKey('VONAGE_KEY');
			}
		}
		if (array_key_exists('vonage_secret', $setting->value)) {
			if (!empty($setting->value['vonage_secret'])) {
				DotenvEditor::setKey('VONAGE_SECRET', $setting->value['vonage_secret']);
			} else {
				DotenvEditor::deleteKey('VONAGE_SECRET');
			}
		}
		if (array_key_exists('vonage_application_id', $setting->value)) {
			if (!empty($setting->value['vonage_application_id'])) {
				DotenvEditor::setKey('VONAGE_APPLICATION_ID', $setting->value['vonage_application_id']);
			} else {
				DotenvEditor::deleteKey('VONAGE_APPLICATION_ID');
			}
		}
		if (array_key_exists('vonage_from', $setting->value)) {
			if (!empty($setting->value['vonage_from'])) {
				DotenvEditor::setKey('VONAGE_SMS_FROM', $setting->value['vonage_from']);
			} else {
				DotenvEditor::deleteKey('VONAGE_SMS_FROM');
			}
		}
		if (array_key_exists('twilio_username', $setting->value)) {
			if (!empty($setting->value['twilio_username'])) {
				DotenvEditor::setKey('TWILIO_USERNAME', $setting->value['twilio_username']);
			} else {
				DotenvEditor::deleteKey('TWILIO_USERNAME');
			}
		}
		if (array_key_exists('twilio_password', $setting->value)) {
			if (!empty($setting->value['twilio_password'])) {
				DotenvEditor::setKey('TWILIO_PASSWORD', $setting->value['twilio_password']);
			} else {
				DotenvEditor::deleteKey('TWILIO_PASSWORD');
			}
		}
		if (array_key_exists('twilio_auth_token', $setting->value)) {
			if (!empty($setting->value['twilio_auth_token'])) {
				DotenvEditor::setKey('TWILIO_AUTH_TOKEN', $setting->value['twilio_auth_token']);
			} else {
				DotenvEditor::deleteKey('TWILIO_AUTH_TOKEN');
			}
		}
		if (array_key_exists('twilio_account_sid', $setting->value)) {
			if (!empty($setting->value['twilio_account_sid'])) {
				DotenvEditor::setKey('TWILIO_ACCOUNT_SID', $setting->value['twilio_account_sid']);
			} else {
				DotenvEditor::deleteKey('TWILIO_ACCOUNT_SID');
			}
		}
		if (array_key_exists('twilio_from', $setting->value)) {
			if (!empty($setting->value['twilio_from'])) {
				DotenvEditor::setKey('TWILIO_FROM', $setting->value['twilio_from']);
			} else {
				DotenvEditor::deleteKey('TWILIO_FROM');
			}
		}
		if (array_key_exists('twilio_alpha_sender', $setting->value)) {
			if (!empty($setting->value['twilio_alpha_sender'])) {
				DotenvEditor::setKey('TWILIO_ALPHA_SENDER', $setting->value['twilio_alpha_sender']);
			} else {
				DotenvEditor::deleteKey('TWILIO_ALPHA_SENDER');
			}
		}
		if (array_key_exists('twilio_sms_service_sid', $setting->value)) {
			if (!empty($setting->value['twilio_sms_service_sid'])) {
				DotenvEditor::setKey('TWILIO_SMS_SERVICE_SID', $setting->value['twilio_sms_service_sid']);
			} else {
				DotenvEditor::deleteKey('TWILIO_SMS_SERVICE_SID');
			}
		}
		if (array_key_exists('twilio_debug_to', $setting->value)) {
			if (!empty($setting->value['twilio_debug_to'])) {
				DotenvEditor::setKey('TWILIO_DEBUG_TO', $setting->value['twilio_debug_to']);
			} else {
				DotenvEditor::deleteKey('TWILIO_DEBUG_TO');
			}
		}
		
		if (
			array_key_exists('vonage_key', $setting->value)
			|| array_key_exists('vonage_secret', $setting->value)
			|| array_key_exists('vonage_from', $setting->value)
			|| array_key_exists('twilio_username', $setting->value)
			|| array_key_exists('twilio_password', $setting->value)
			|| array_key_exists('twilio_auth_token', $setting->value)
			|| array_key_exists('twilio_account_sid', $setting->value)
			|| array_key_exists('twilio_from', $setting->value)
			|| array_key_exists('twilio_alpha_sender', $setting->value)
			|| array_key_exists('twilio_sms_service_sid', $setting->value)
			|| array_key_exists('twilio_debug_to', $setting->value)
		) {
			$envFileHasChanged = true;
		}
		
		// Save the /.env file
		if ($envFileHasChanged) {
			DotenvEditor::save();
			
			// Some time of pause
			sleep(2);
		}
	}
}
