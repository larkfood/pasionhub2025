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

class SendPasswordAndVerificationInfo extends BaseNotification
{
	protected ?object $user;
	protected ?string $randomPassword;
	
	public function __construct(?object $user, ?string $randomPassword)
	{
		$this->user = $user;
		$this->randomPassword = $randomPassword;
	}
	
	protected function shouldSendNotificationWhen($notifiable): bool
	{
		return (!empty($this->user) && !empty($this->randomPassword));
	}
	
	protected function determineViaChannels($notifiable): array
	{
		$authField = $this->user->auth_field ?? getAuthField();
		
		if ($authField == 'phone') {
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
		$path = 'users/verify/email/' . $this->user->email_token;
		$verificationUrl = (config('plugins.domainmapping.installed'))
			? dmUrl($this->user->country_code, $path)
			: url($path);
		
		$loginUrl = urlGen()->login();
		
		$mailMessage = (new MailMessage)
			->subject(trans('mail.generated_password_title'))
			->greeting(trans('mail.generated_password_content_1', ['userName' => $this->user->name]))
			->line(trans('mail.generated_password_content_2'));
		
		if (!isVerifiedUser($this->user)) {
			$mailMessage->line(trans('mail.generated_password_verify_content_3'))
				->action(trans('mail.generated_password_verify_action'), $verificationUrl);
		}
		
		$mailMessage->line(trans('mail.generated_password_content_4', ['randomPassword' => $this->randomPassword]));
		
		if (isVerifiedUser($this->user)) {
			$mailMessage->action(trans('mail.generated_password_login_action'), $loginUrl);
		}
		
		$mailMessage->line(trans('mail.generated_password_content_6', ['appName' => config('app.name')]))
			->salutation(trans('mail.footer_salutation', ['appName' => config('app.name')]));
		
		return $mailMessage;
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
		$token = $this->user->phone_token;
		
		$path = 'users/verify/phone/' . $token;
		$tokenUrl = (config('plugins.domainmapping.installed'))
			? dmUrl($this->user->country_code, $path)
			: url($path);
		
		$msg = trans('sms.generated_password_content', [
			'appName'        => config('app.name'),
			'randomPassword' => $this->randomPassword,
			'token'          => $token,
			'tokenUrl'       => $tokenUrl,
		]);
		
		return getAsString($msg);
	}
}
