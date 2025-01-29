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

use Illuminate\Notifications\Messages\VonageMessage;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

/*
 * Note: Implementing "Illuminate\Contracts\Queue\ShouldQueue"
 * allows Laravel to save mail sending as Queue in the database
 */

class ExampleSms extends BaseNotification
{
	private ?string $driver;
	
	public function __construct(?string $driver = null)
	{
		$this->driver = !empty($driver) ? $driver : config('settings.sms.driver');
	}
	
	protected function shouldSendNotificationWhen($notifiable): bool
	{
		return !isDemoDomain();
	}
	
	protected function determineViaChannels($notifiable): array
	{
		if ($this->driver == 'twilio') {
			return [TwilioChannel::class];
		}
		
		return ['vonage'];
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
		$msg = trans('sms.example_content', ['appName' => config('app.name')]);
		
		return getAsString($msg);
	}
}
