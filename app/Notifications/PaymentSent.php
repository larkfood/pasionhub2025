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

use App\Models\Payment;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\VonageMessage;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class PaymentSent extends BaseNotification
{
	protected ?Payment $payment;
	protected ?object $post;
	
	public function __construct(?Payment $payment, ?object $post)
	{
		$this->payment = $payment;
		$this->post = $post;
	}
	
	protected function shouldSendNotificationWhen($notifiable): bool
	{
		return (!empty($this->payment) && !empty($this->post));
	}
	
	protected function determineViaChannels($notifiable): array
	{
		// Is email can be sent?
		$emailNotificationCanBeSent = (config('settings.mail.confirmation') == '1' && !empty($this->post->email));
		
		// Is SMS can be sent in addition?
		$smsNotificationCanBeSent = (
			config('settings.sms.enable_phone_as_auth_field') == '1'
			&& config('settings.sms.confirmation') == '1'
			&& isset($this->post->auth_field)
			&& $this->post->auth_field == 'phone'
			&& !empty($this->post->phone)
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
		$postUrl = urlGen()->post($this->post);
		
		return (new MailMessage)
			->subject(trans('mail.payment_sent_title'))
			->greeting(trans('mail.payment_sent_content_1'))
			->line(trans('mail.payment_sent_content_2', [
				'postUrl' => $postUrl,
				'title'   => $this->post->title,
			]))
			->line(trans('mail.payment_sent_content_3'))
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
		$msg = trans('sms.payment_sent_content', [
			'appName' => config('app.name'),
			'title'   => $this->post->title,
		]);
		
		return getAsString($msg);
	}
}
