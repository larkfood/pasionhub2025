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
use Illuminate\Notifications\Messages\VonageMessage;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class PostWilBeDeleted extends BaseNotification
{
	protected ?object $post;
	protected int $days;
	protected ?string $willBeDeletedAtFormatted = null;
	
	public function __construct(?object $post, int|string|null $days)
	{
		$this->post = $post;
		$this->days = (int)$days;
		
		// Get delete date
		if (isset($this->post->archived_at)) {
			$willBeDeletedAt = $this->post->archived_at->addDays($this->days);
			$this->willBeDeletedAtFormatted = Date::format($willBeDeletedAt);
		}
	}
	
	protected function shouldSendNotificationWhen($notifiable): bool
	{
		return (!empty($this->post) && $this->days > 0);
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
		$path = 'account/posts/archived/' . $this->post->id . '/repost';
		$repostUrl = (config('plugins.domainmapping.installed'))
			? dmUrl($this->post->country_code, $path)
			: url($path);
		
		return (new MailMessage)
			->subject(trans('mail.post_will_be_deleted_title', [
				'title' => $this->post->title,
				'days'  => $this->days,
			]))
			->greeting(trans('mail.post_will_be_deleted_content_1'))
			->line(trans('mail.post_will_be_deleted_content_2', [
				'title'   => $this->post->title,
				'days'    => $this->days,
				'appName' => config('app.name'),
			]))
			->line(trans('mail.post_will_be_deleted_content_3', ['repostUrl' => $repostUrl]))
			->line(trans('mail.post_will_be_deleted_content_4', [
				'willBeDeletedAt' => $this->willBeDeletedAtFormatted,
				'dateDel'         => $this->willBeDeletedAtFormatted, // @note: need to be removed
			]))
			->line(trans('mail.post_will_be_deleted_content_5'))
			->line('<br>')
			->line(trans('mail.post_will_be_deleted_content_6'))
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
		$msg = trans('sms.post_will_be_deleted_content', [
			'appName'         => config('app.name'),
			'title'           => $this->post->title,
			'days'            => $this->days,
			'willBeDeletedAt' => $this->willBeDeletedAtFormatted,
			'dateDel'         => $this->willBeDeletedAtFormatted, // @note: need to be removed
		]);
		
		return getAsString($msg);
	}
}
