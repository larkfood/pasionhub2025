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

use App\Helpers\Date;
use Illuminate\Notifications\Messages\MailMessage;

class UserNotification extends BaseNotification
{
	protected ?object $user;
	protected string $todayDateFormatted;
	protected string $todayTimeFormatted;
	
	public function __construct(?object $user)
	{
		$this->user = $user;
		
		// Get timezone
		$tz = Date::getAppTimeZone();
		
		// Get today date & time
		$this->todayDateFormatted = Date::format(now($tz));
		$this->todayTimeFormatted = now($tz)->format('H:i');
	}
	
	protected function shouldSendNotificationWhen($notifiable): bool
	{
		return !empty($this->user);
	}
	
	protected function determineViaChannels($notifiable): array
	{
		return ['mail'];
	}
	
	public function toMail($notifiable): MailMessage
	{
		return (new MailMessage)
			->subject(trans('mail.user_notification_title'))
			->greeting(trans('mail.user_notification_content_1'))
			->line(trans('mail.user_notification_content_2', ['name' => $this->user->name]))
			->line(trans('mail.user_notification_content_3', [
				'now'       => $this->todayDateFormatted,
				'time'      => $this->todayTimeFormatted,
				'authField' => $this->user->auth_field ?? '-',
				'email'     => !empty($this->user->email) ? $this->user->email : '-',
				'phone'     => !empty($this->user->phone) ? $this->user->phone : '-',
			]))
			->salutation(trans('mail.footer_salutation', ['appName' => config('app.name')]));
	}
}
