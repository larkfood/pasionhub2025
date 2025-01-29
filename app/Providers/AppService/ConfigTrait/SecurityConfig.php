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

trait SecurityConfig
{
	private function updateSecurityConfig(?array $settings = []): void
	{
		// Honeypot
		$enabled = ((int)data_get($settings, 'honeypot_enabled') == 1);
		$nameFieldName = data_get($settings, 'honeypot_name_field_name');
		$randomizeNameFieldName = ((int)data_get($settings, 'honeypot_randomize_name_field_name') == 1);
		$validFromTimestamp = ((int)data_get($settings, 'honeypot_valid_from_timestamp') == 1);
		$validFromFieldName = data_get($settings, 'honeypot_valid_from_field_name');
		$amountOfSeconds = data_get($settings, 'honeypot_amount_of_seconds');
		$respondToSpamWith = data_get($settings, 'honeypot_respond_to_spam_with');
		
		config()->set('honeypot.enabled', env('HONEYPOT_ENABLED', $enabled));
		config()->set('honeypot.name_field_name', env('HONEYPOT_NAME', $nameFieldName));
		config()->set('honeypot.randomize_name_field_name', env('HONEYPOT_RANDOMIZE', $randomizeNameFieldName));
		config()->set('honeypot.valid_from_timestamp', env('HONEYPOT_VALID_FROM_TIMESTAMP', $validFromTimestamp));
		config()->set('honeypot.valid_from_field_name', env('HONEYPOT_VALID_FROM', $validFromFieldName));
		config()->set('honeypot.amount_of_seconds', env('HONEYPOT_SECONDS', $amountOfSeconds));
		config()->set('honeypot.respond_to_spam_with', $respondToSpamWith);
		
		// CAPTCHA
		config()->set('captcha.option', env('CAPTCHA', data_get($settings, 'captcha')));
		if (data_get($settings, 'captcha') == 'custom') {
			if (
				data_get($settings, 'captcha_length')
				&& data_get($settings, 'captcha_length') >= 3
				&& data_get($settings, 'captcha_length') <= 8
			) {
				config()->set('captcha.custom.length', data_get($settings, 'captcha_length'));
			}
			if (
				data_get($settings, 'captcha_width')
				&& data_get($settings, 'captcha_width') >= 100
				&& data_get($settings, 'captcha_width') <= 300
			) {
				config()->set('captcha.custom.width', data_get($settings, 'captcha_width'));
			}
			if (
				data_get($settings, 'captcha_height')
				&& data_get($settings, 'captcha_height') >= 30
				&& data_get($settings, 'captcha_height') <= 150
			) {
				config()->set('captcha.custom.height', data_get($settings, 'captcha_height'));
			}
			if (data_get($settings, 'captcha_quality')) {
				config()->set('captcha.custom.quality', data_get($settings, 'captcha_quality'));
			}
			if (data_get($settings, 'captcha_math')) {
				config()->set('captcha.custom.math', data_get($settings, 'captcha_math'));
			}
			if (data_get($settings, 'captcha_expire')) {
				config()->set('captcha.custom.expire', data_get($settings, 'captcha_expire'));
			}
			if (data_get($settings, 'captcha_encrypt')) {
				config()->set('captcha.custom.encrypt', data_get($settings, 'captcha_encrypt'));
			}
			if (data_get($settings, 'captcha_lines')) {
				config()->set('captcha.custom.lines', data_get($settings, 'captcha_lines'));
			}
			if (data_get($settings, 'captcha_bgImage')) {
				config()->set('captcha.custom.bgImage', data_get($settings, 'captcha_bgImage'));
			}
			if (data_get($settings, 'captcha_bgColor')) {
				config()->set('captcha.custom.bgColor', data_get($settings, 'captcha_bgColor'));
			}
			if (data_get($settings, 'captcha_sensitive')) {
				config()->set('captcha.custom.sensitive', data_get($settings, 'captcha_sensitive'));
			}
			if (data_get($settings, 'captcha_angle')) {
				config()->set('captcha.custom.angle', data_get($settings, 'captcha_angle'));
			}
			if (data_get($settings, 'captcha_sharpen')) {
				config()->set('captcha.custom.sharpen', data_get($settings, 'captcha_sharpen'));
			}
			if (data_get($settings, 'captcha_blur')) {
				config()->set('captcha.custom.blur', data_get($settings, 'captcha_blur'));
			}
			if (data_get($settings, 'captcha_invert')) {
				config()->set('captcha.custom.invert', data_get($settings, 'captcha_invert'));
			}
			if (data_get($settings, 'captcha_contrast')) {
				config()->set('captcha.custom.contrast', data_get($settings, 'captcha_contrast'));
			}
		}
		
		// reCAPTCHA
		if (data_get($settings, 'captcha') == 'recaptcha') {
			$version = data_get($settings, 'recaptcha_version', 'v2');
			$version = env('RECAPTCHA_VERSION', $version);
			
			if ($version == 'v3') {
				$siteKey = data_get($settings, 'recaptcha_v3_site_key');
				$secretKey = data_get($settings, 'recaptcha_v3_secret_key');
			} else {
				$siteKey = data_get($settings, 'recaptcha_v2_site_key');
				$secretKey = data_get($settings, 'recaptcha_v2_secret_key');
			}
			
			$skipIps = env('RECAPTCHA_SKIP_IPS', data_get($settings, 'recaptcha_skip_ips', ''));
			$skipIpsArr = preg_split('#[:,;\s]+#ui', $skipIps);
			$skipIpsArr = array_filter(array_map('trim', $skipIpsArr));
			
			config()->set('recaptcha.version', $version);
			config()->set('recaptcha.site_key', env('RECAPTCHA_SITE_KEY', $siteKey));
			config()->set('recaptcha.secret_key', env('RECAPTCHA_SECRET_KEY', $secretKey));
			config()->set('recaptcha.skip_ip', $skipIpsArr);
		}
	}
}
