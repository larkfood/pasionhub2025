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

use App\Providers\AppService\ConfigTrait\MailConfig\EmailTest;
use Illuminate\Support\Facades\Mail;

trait MailConfig
{
	use EmailTest;
	
	private function updateMailConfig(?array $settings = [], ?string $appName = null): void
	{
		if (empty($settings)) {
			return;
		}
		
		// Mail
		$driver = $settings['driver'] ?? null;
		$driver = env('MAIL_DRIVER', $driver);
		$driver = env('MAIL_MAILER', $driver);
		$fromName = config('settings.app.name', $appName ?? 'Site Name');
		
		config()->set('mail.default', $driver);
		config()->set('mail.from.name', env('MAIL_FROM_NAME', $fromName));
		
		// Default Mail Sender
		$mailSender = config('settings.app.email');
		
		// SMTP
		if ($driver == 'smtp') {
			$host = $settings['smtp_host'] ?? null;
			$port = $settings['smtp_port'] ?? null;
			$encryption = $settings['smtp_encryption'] ?? null;
			$username = $settings['smtp_username'] ?? null;
			$password = $settings['smtp_password'] ?? null;
			$verifyPeer = $settings['smtp_verify_peer'] ?? '0';
			$verifyPeer = ($verifyPeer == '1');
			$address = $settings['smtp_email_sender'] ?? ($mailSender ?? null);
			
			config()->set('mail.mailers.smtp.host', env('MAIL_HOST', $host));
			config()->set('mail.mailers.smtp.port', env('MAIL_PORT', $port));
			config()->set('mail.mailers.smtp.encryption', env('MAIL_ENCRYPTION', $encryption));
			config()->set('mail.mailers.smtp.username', env('MAIL_USERNAME', $username));
			config()->set('mail.mailers.smtp.password', env('MAIL_PASSWORD', $password));
			config()->set('mail.mailers.smtp.verify_peer', env('MAIL_VERIFY_PEER', $verifyPeer));
			config()->set('mail.from.address', env('MAIL_FROM_ADDRESS', $address));
		}
		
		// Sendmail
		if ($driver == 'sendmail') {
			$path = $settings['sendmail_path'] ?? null;
			$address = $settings['sendmail_email_sender'] ?? ($mailSender ?? null);
			
			config()->set('mail.mailers.sendmail.path', env('MAIL_SENDMAIL', $path));
			config()->set('mail.from.address', env('MAIL_FROM_ADDRESS', $address));
		}
		
		// Mailgun
		if ($driver == 'mailgun') {
			$domain = $settings['mailgun_domain'] ?? null;
			$secret = $settings['mailgun_secret'] ?? null;
			$endpoint = $settings['mailgun_endpoint'] ?? ('api.mailgun.net' ?? null);
			$host = $settings['mailgun_host'] ?? null;
			$port = $settings['mailgun_port'] ?? null;
			$encryption = $settings['mailgun_encryption'] ?? null;
			$username = $settings['mailgun_username'] ?? null;
			$password = $settings['mailgun_password'] ?? null;
			$address = $settings['mailgun_email_sender'] ?? ($mailSender ?? null);
			
			config()->set('services.mailgun.domain', env('MAILGUN_DOMAIN', $domain));
			config()->set('services.mailgun.secret', env('MAILGUN_SECRET', $secret));
			config()->set('services.mailgun.endpoint', env('MAILGUN_ENDPOINT', $endpoint));
			config()->set('mail.mailers.smtp.host', env('MAIL_HOST', $host));
			config()->set('mail.mailers.smtp.port', env('MAIL_PORT', $port));
			config()->set('mail.mailers.smtp.encryption', env('MAIL_ENCRYPTION', $encryption));
			config()->set('mail.mailers.smtp.username', env('MAIL_USERNAME', $username));
			config()->set('mail.mailers.smtp.password', env('MAIL_PASSWORD', $password));
			config()->set('mail.from.address', env('MAIL_FROM_ADDRESS', $address));
		}
		
		// Postmark
		if ($driver == 'postmark') {
			$token = $settings['postmark_token'] ?? null;
			$host = $settings['postmark_host'] ?? null;
			$port = $settings['postmark_port'] ?? null;
			$encryption = $settings['postmark_encryption'] ?? null;
			$username = $settings['postmark_username'] ?? null;
			$password = $settings['postmark_password'] ?? null;
			$address = $settings['postmark_email_sender'] ?? ($mailSender ?? null);
			
			config()->set('services.postmark.token', env('POSTMARK_TOKEN', $token));
			config()->set('mail.mailers.smtp.host', env('MAIL_HOST', $host));
			config()->set('mail.mailers.smtp.port', env('MAIL_PORT', $port));
			config()->set('mail.mailers.smtp.encryption', env('MAIL_ENCRYPTION', $encryption));
			config()->set('mail.mailers.smtp.username', env('MAIL_USERNAME', $username));
			config()->set('mail.mailers.smtp.password', env('MAIL_PASSWORD', $password));
			config()->set('mail.from.address', env('MAIL_FROM_ADDRESS', $address));
		}
		
		// Amazon SES
		if ($driver == 'ses') {
			$key = $settings['ses_key'] ?? null;
			$secret = $settings['ses_secret'] ?? null;
			$region = $settings['ses_region'] ?? null;
			$token = $settings['ses_token'] ?? null;
			$host = $settings['ses_host'] ?? null;
			$port = $settings['ses_port'] ?? null;
			$encryption = $settings['ses_encryption'] ?? null;
			$username = $settings['ses_username'] ?? null;
			$password = $settings['ses_password'] ?? null;
			$address = $settings['ses_email_sender'] ?? ($mailSender ?? null);
			
			config()->set('services.ses.key', env('SES_KEY', $key));
			config()->set('services.ses.secret', env('SES_SECRET', $secret));
			config()->set('services.ses.region', env('SES_REGION', $region));
			config()->set('services.ses.token', env('SES_SESSION_TOKEN', $token));
			config()->set('mail.mailers.smtp.host', env('MAIL_HOST', $host));
			config()->set('mail.mailers.smtp.port', env('MAIL_PORT', $port));
			config()->set('mail.mailers.smtp.encryption', env('MAIL_ENCRYPTION', $encryption));
			config()->set('mail.mailers.smtp.username', env('MAIL_USERNAME', $username));
			config()->set('mail.mailers.smtp.password', env('MAIL_PASSWORD', $password));
			config()->set('mail.from.address', env('MAIL_FROM_ADDRESS', $address));
		}
		
		// Sparkpost
		if ($driver == 'sparkpost') {
			$secret = $settings['sparkpost_secret'] ?? null;
			$host = $settings['sparkpost_host'] ?? null;
			$port = $settings['sparkpost_port'] ?? null;
			$encryption = $settings['sparkpost_encryption'] ?? null;
			$username = $settings['sparkpost_username'] ?? null;
			$password = $settings['sparkpost_password'] ?? null;
			$address = $settings['sparkpost_email_sender'] ?? ($mailSender ?? null);
			
			config()->set('services.sparkpost.secret', env('SPARKPOST_SECRET', $secret));
			config()->set('mail.mailers.smtp.host', env('MAIL_HOST', $host));
			config()->set('mail.mailers.smtp.port', env('MAIL_PORT', $port));
			config()->set('mail.mailers.smtp.encryption', env('MAIL_ENCRYPTION', $encryption));
			config()->set('mail.mailers.smtp.username', env('MAIL_USERNAME', $username));
			config()->set('mail.mailers.smtp.password', env('MAIL_PASSWORD', $password));
			config()->set('mail.from.address', env('MAIL_FROM_ADDRESS', $address));
		}
		
		// Resend
		if ($driver == 'resend') {
			$apiKey = $settings['resend_api_key'] ?? null;
			$address = $settings['resend_email_sender'] ?? ($mailSender ?? null);
			
			config()->set('services.resend.key', env('RESEND_API_KEY', $apiKey));
			config()->set('mail.from.address', env('MAIL_FROM_ADDRESS', $address));
		}
		
		// MailerSend
		if ($driver == 'mailersend') {
			$apiKey = $settings['mailersend_api_key'] ?? null;
			$apiKey = env('MAILERSEND_API_KEY', $apiKey);
			$address = $settings['mailersend_email_sender'] ?? ($mailSender ?? null);
			
			config()->set('services.mailersend.api_key', $apiKey);
			config()->set('mailersend-driver.api_key', $apiKey);
			config()->set('mail.from.address', env('MAIL_FROM_ADDRESS', $address));
			
			/*
			$host = $settings['mailersend_host'] ?? 'api.mailersend.com';
			$protocol = $settings['mailersend_protocol'] ?? 'https';
			$apiPath = $settings['mailersend_api_path'] ?? 'v1';
			
			config()->set('mailersend-driver.host', env('MAILERSEND_API_HOST', $host));
			config()->set('mailersend-driver.protocol', env('MAILERSEND_API_PROTO', $protocol));
			config()->set('mailersend-driver.api_path', env('MAILERSEND_API_PATH', $apiPath));
			*/
		}
		
		// Brevo
		if ($driver == 'brevo') {
			$apiKey = $settings['brevo_api_key'] ?? null;
			$address = $settings['brevo_email_sender'] ?? ($mailSender ?? null);
			
			config()->set('services.brevo.key', env('BREVO_API_KEY', $apiKey));
			config()->set('mail.from.address', env('MAIL_FROM_ADDRESS', $address));
		}
	}
	
	/**
	 * Send Mails Always To
	 *
	 * @return void
	 */
	private function setupMailsAlwaysTo(): void
	{
		if (request()->isMethod('put') && request()->has('email_always_to')) {
			$isDriverTestEnabled = (request()->input('driver_test', '0') == '1');
			$emailAlwaysTo = request()->input('email_always_to');
		} else {
			$isDriverTestEnabled = (config('settings.mail.driver_test', '0') == '1');
			$emailAlwaysTo = config('settings.mail.email_always_to');
		}
		
		$isAlwaysToEnabled = ($isDriverTestEnabled && !empty($emailAlwaysTo) && isValidEmail($emailAlwaysTo));
		if ($isAlwaysToEnabled) {
			Mail::alwaysTo($emailAlwaysTo);
		}
	}
}
