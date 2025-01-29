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

namespace App\Notifications;

use Carbon\CarbonInterval;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\VonageMessage;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class ResetPasswordNotification extends BaseNotification
{
	protected ?object $user;
	protected ?string $token;
	protected ?string $field;
	
	protected ?string $expireTimeString = null;
	
	public function __construct(?object $user, ?string $token, ?string $field)
	{
		$this->user = $user;
		$this->token = $token;
		$this->field = $field;
		
		// Password Timeout String
		// Convert seconds into days hours minutes
		$passwordTimeout = (int)config('auth.password_timeout', 10800);
		$passwordTimeout = ($passwordTimeout < 1) ? 1 : $passwordTimeout;
		try {
			$this->expireTimeString = CarbonInterval::seconds($passwordTimeout)->cascade()->forHumans();
		} catch (\Throwable $e) {
			$this->expireTimeString = $passwordTimeout . ' minute(s)';
		}
	}
	
	protected function determineViaChannels($notifiable): array
	{
		if ($this->field == 'phone') {
			if (config('settings.sms.driver') == 'twilio') {
				return [TwilioChannel::class];
			}
			
			return ['vonage'];
		} else {
			return ['mail'];
		}
	}
	
	public function toMail($notifiable): MailMessage
	{
		$path = 'password/reset/' . $this->token;
		$resetPasswordUrl = (config('plugins.domainmapping.installed'))
			? dmUrl(config('country.code'), $path)
			: url($path);
		
		return (new MailMessage)
			->subject(trans('mail.reset_password_title'))
			->line(trans('mail.reset_password_content_1'))
			->action(trans('mail.reset_password_action'), $resetPasswordUrl)
			->line(trans('mail.reset_password_content_2', ['expireTimeString' => $this->expireTimeString]))
			->line(trans('mail.reset_password_content_3'))
			->salutation(trans('mail.footer_salutation', ['appName' => config('app.name')]));
	}
	
	public function toVonage($notifiable): VonageMessage
	{
		return (new VonageMessage())->content($this->getSmsMessage())->unicode();
	}
	
	public function toTwilio($notifiable): TwilioSmsMessage|\NotificationChannels\Twilio\TwilioMessage
	{
		return (new TwilioSmsMessage())->content($this->getSmsMessage());
	}
	
	// PRIVATE
	
	private function getSmsMessage(): string
	{
		$msg = trans('sms.reset_password_content', [
			'appName'          => config('app.name'),
			'token'            => $this->token,
			'expireTimeString' => $this->expireTimeString,
		]);
		
		return getAsString($msg);
	}
}
