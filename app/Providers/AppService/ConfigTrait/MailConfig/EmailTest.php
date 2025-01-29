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

namespace App\Providers\AppService\ConfigTrait\MailConfig;

use App\Models\Permission;
use App\Models\User;
use App\Notifications\ExampleMail;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Email;

/*
 * WARNING: This cannot be used directly,
 * Since it calls the updateMailConfig() method from the 'App\Providers\AppService\ConfigTrait\MailConfig' trait
 * So it needs to be used by using the 'MailConfig' trait
 */

trait EmailTest
{
	/**
	 * @param bool $isTestEnabled
	 * @param string|null $mailTo
	 * @param array|null $settings
	 * @param bool $fallbackMailTo
	 * @return string|null
	 */
	private function testMailConfig(bool $isTestEnabled, ?string $mailTo, ?array $settings = [], bool $fallbackMailTo = false): ?string
	{
		if (!$isTestEnabled) {
			return null;
		}
		
		// Apply updated config
		$this->updateMailConfig($settings);
		
		// Get the test recipient
		$mailTo = !empty($mailTo) ? $mailTo : config('settings.app.email');
		
		/*
		 * Send Example Email
		 *
		 * With the sendmail driver, in local environment,
		 * this test email cannot be found if you have not familiar with the sendmail configuration
		 */
		$driver = config('mail.default');
		$message = null;
		try {
			if (!empty($mailTo)) {
				Notification::route('mail', $mailTo)->notify(new ExampleMail());
			} else {
				if ($fallbackMailTo) {
					$admins = User::permission(Permission::getStaffPermissions())->get();
					if ($admins->count() > 0) {
						Notification::send($admins, new ExampleMail());
					}
				} else {
					$message = trans('admin.mail_to_missing');
				}
			}
		} catch (\Throwable $e) {
			$message = $e->getMessage();
		}
		
		if (!empty($message)) {
			$exceptionMessageFormat = ' ERROR: <span class="fw-bold">%s</span>';
			$message = sprintf($exceptionMessageFormat, $message);
			$message = trans('admin.mail_sending_error', ['driver' => $driver]) . $message;
		}
		
		return $message;
	}
	
	/**
	 * @param bool $isTestEnabled
	 * @param string|null $mailTo
	 * @param array|null $settings
	 * @param bool $fallbackMailTo
	 * @return string|null
	 */
	private function testSmtpConfig(bool $isTestEnabled, ?string $mailTo, ?array $settings = [], bool $fallbackMailTo = false): ?string
	{
		if (!$isTestEnabled) {
			return null;
		}
		
		// Apply updated config
		$this->updateMailConfig($settings);
		
		$testUsingDsn = (bool)config('settings.mail.symfony_dsn', false);
		
		// Get SMTP parameters
		$smtpHost = config('mail.mailers.smtp.host');
		$smtpPort = config('mail.mailers.smtp.port');
		$smtpEncryption = config('mail.mailers.smtp.encryption');
		$smtpUsername = config('mail.mailers.smtp.username');
		$smtpPassword = config('mail.mailers.smtp.password');
		$mailFrom = config('mail.from.address');
		
		// Get the test recipient
		$mailTo = !empty($mailTo) ? $mailTo : config('settings.app.email');
		if ($fallbackMailTo) {
			if (!empty($mailTo)) {
				$firstAdmin = User::permission(Permission::getStaffPermissions())->orderBy('id')->first();
				if ($firstAdmin) {
					$mailTo = $firstAdmin->email ?? null;
				}
			}
		}
		
		$driver = config('mail.default');
		$message = null;
		
		if (empty($mailTo)) {
			return trans('admin.mail_to_missing');
		}
		
		// Get the email test data
		$emailData = $this->getEmailTestData();
		$subject = $emailData['subject'] ?? '';
		$bodyHtml = $emailData['bodyHtml'] ?? '';
		$bodyText = $emailData['bodyText'] ?? '';
		
		if ($testUsingDsn) {
			try {
				$dsn = 'smtp://' . $smtpUsername . ':' . $smtpPassword . '@' . $smtpHost . ':' . $smtpPort;
				$transport = Transport::fromDsn($dsn);
				
				$mailer = new Mailer($transport);
				
				$email = (new Email())
					->from($mailFrom)
					->to($mailTo)
					->subject($subject)
					->text($bodyText)
					->html($bodyHtml);
				
				$mailer->send($email);
				
			} catch (\Throwable $e) {
				$message = $e->getMessage();
				if (!empty($message)) {
					$message .= ' (DNS)';
				}
			}
		} else {
			try {
				$tls = ($smtpEncryption == 'tls');
				$transport = new EsmtpTransport($smtpHost, $smtpPort, $tls);
				$transport->setUsername($smtpUsername);
				$transport->setPassword($smtpPassword);
				
				$mailer = new Mailer($transport);
				
				$email = (new Email())
					->from($mailFrom)
					->to($mailTo)
					->subject($subject)
					->text($bodyText)
					->html($bodyHtml);
				
				$mailer->send($email);
				
			} catch (\Throwable $e) {
				$message = $e->getMessage();
			}
		}
		
		if (!empty($message)) {
			$exceptionMessageFormat = ' ERROR: <span class="fw-bold">%s</span>';
			$message = sprintf($exceptionMessageFormat, $message);
			$message = trans('admin.mail_sending_error', ['driver' => $driver]) . $message;
		}
		
		return $message;
	}
	
	/**
	 * @return array
	 */
	private function getEmailTestData(): array
	{
		$subject = trans('mail.email_example_title', ['appName' => config('app.name')]);
		$body = '<h3>' . trans('mail.email_example_content_1') . '</h3>';
		$body .= '<p>' . trans('mail.email_example_content_2', ['appName' => config('app.name')]) . '</p>';
		$body .= '<p>' . trans('mail.footer_salutation', ['appName' => config('app.name')]) . '</p>';
		
		return [
			'subject'  => $subject,
			'bodyHtml' => $body,
			'bodyText' => strip_tags($body),
		];
	}
}
