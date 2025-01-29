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

class FormSent extends BaseNotification
{
	protected ?object $msg;
	
	public function __construct(?object $convertedInput)
	{
		$this->msg = $convertedInput;
	}
	
	protected function shouldSendNotificationWhen($notifiable): bool
	{
		if (isDemoDomain()) return false;
		
		return !empty($this->msg);
	}
	
	protected function determineViaChannels($notifiable): array
	{
		return ['mail'];
	}
	
	public function toMail($notifiable): MailMessage
	{
		$mailMessage = (new MailMessage)
			->replyTo($this->msg->email, $this->msg->first_name . ' ' . $this->msg->last_name)
			->subject(trans('mail.contact_form_title', ['country' => $this->msg->country_name, 'appName' => config('app.name')]))
			->line(t('country') . ': <a href="' . url('/?country=' . $this->msg->country_code) . '">' . $this->msg->country_name . '</a>')
			->line(t('first_name') . ': ' . $this->msg->first_name)
			->line(t('last_name') . ': ' . $this->msg->last_name)
			->line(t('email_address') . ': ' . $this->msg->email);
		
		if (isset($this->msg->company_name) && $this->msg->company_name != '') {
			$mailMessage->line(t('company_name') . ': ' . $this->msg->company_name);
		}
		
		$mailMessage->line(nl2br($this->msg->message))
			->salutation(trans('mail.footer_salutation', ['appName' => config('app.name')]));
		
		return $mailMessage;
	}
}
