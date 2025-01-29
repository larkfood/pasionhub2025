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

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\VonageMessage;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class UserActivated extends BaseNotification
{
	protected ?object $user;
	
	public function __construct(?object $user)
	{
		$this->user = $user;
	}
	
	protected function shouldSendNotificationWhen($notifiable): bool
	{
		return !empty($this->user);
	}
	
	protected function determineViaChannels($notifiable): array
	{
		// Is email can be sent?
		$emailNotificationCanBeSent = (
			config('settings.mail.confirmation') == '1'
			&& !empty($this->user->email)
			&& !empty($this->user->email_verified_at)
		);
		
		// Is SMS can be sent in addition?
		$smsNotificationCanBeSent = (
			config('settings.sms.enable_phone_as_auth_field') == '1'
			&& config('settings.sms.confirmation') == '1'
			&& isset($this->user->auth_field)
			&& $this->user->auth_field == 'phone'
			&& !empty($this->user->phone)
			&& !empty($this->user->phone_verified_at)
			&& !isDemoDomain()
		);
		
		if ($emailNotificationCanBeSent) {
			return ['mail'];
		}
		
		if ($smsNotificationCanBeSent) {
			if (config('settings.sms.driver') == 'twilio') {
				return [TwilioChannel::class];
			}
			
			return ['vonage'];
		}
		
		return [];
	}
	
	public function toMail($notifiable): MailMessage
	{
		return (new MailMessage)
			->subject(trans('mail.user_activated_title', ['appName' => config('app.name'), 'userName' => $this->user->name]))
			->greeting(trans('mail.user_activated_content_1', ['appName' => config('app.name'), 'userName' => $this->user->name]))
			->line(trans('mail.user_activated_content_2'))
			->line(trans('mail.user_activated_content_3', ['appName' => config('app.name')]))
			->line(trans('mail.user_activated_content_4', ['appName' => config('app.name')]))
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
		$msg = trans('sms.user_activated_content', [
			'appName'  => config('app.name'),
			'userName' => $this->user->name,
		]);
		
		return getAsString($msg);
	}
}
