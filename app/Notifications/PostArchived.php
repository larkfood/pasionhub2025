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

class PostArchived extends BaseNotification
{
	protected ?object $post;
	protected int $archivedPostsExpiration;
	
	protected string $todayDateFormatted;
	protected ?string $willBeDeletedAtFormatted = null;
	
	public function __construct(?object $post, int|string|null $archivedPostsExpiration)
	{
		$this->post = $post;
		$this->archivedPostsExpiration = (int)$archivedPostsExpiration;
		
		// Get timezone
		$tz = Date::getAppTimeZone();
		
		// Get today date
		$this->todayDateFormatted = Date::format(now($tz));
		
		// Get delete date
		if (isset($this->post->archived_at)) {
			$willBeDeletedAt = $this->post->archived_at->addDays($this->archivedPostsExpiration);
			$this->willBeDeletedAtFormatted = Date::format($willBeDeletedAt);
		}
	}
	
	protected function shouldSendNotificationWhen($notifiable): bool
	{
		return !empty($this->post);
	}
	
	protected function determineViaChannels($notifiable): array
	{
		// Is email can be sent?
		$emailNotificationCanBeSent = (
			config('settings.mail.confirmation') == '1'
			&& !empty($this->post->email)
			&& !empty($this->post->archived_at)
		);
		
		// Is SMS can be sent in addition?
		$smsNotificationCanBeSent = (
			config('settings.sms.enable_phone_as_auth_field') == '1'
			&& config('settings.sms.confirmation') == '1'
			&& isset($this->post->auth_field)
			&& $this->post->auth_field == 'phone'
			&& !empty($this->post->phone)
			&& !empty($this->post->archived_at)
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
		// Get Repost URL
		$path = 'account/posts/archived/' . $this->post->id . '/repost';
		$repostUrl = (config('plugins.domainmapping.installed'))
			? dmUrl($this->post->country_code, $path)
			: url($path);
		
		return (new MailMessage)
			->subject(trans('mail.post_archived_title', ['title' => $this->post->title]))
			->greeting(trans('mail.post_archived_content_1'))
			->line(trans('mail.post_archived_content_2', [
				'title'   => $this->post->title,
				'now'     => $this->todayDateFormatted,
				'appName' => config('app.name'),
			]))
			->line(trans('mail.post_archived_content_3', ['repostUrl' => $repostUrl]))
			->line(trans('mail.post_archived_content_4', [
				'willBeDeletedAt' => $this->willBeDeletedAtFormatted,
				'dateDel'         => $this->willBeDeletedAtFormatted, // @note: need to be removed
			]))
			->line(trans('mail.post_archived_content_5'))
			->line('<br>')
			->line(trans('mail.post_archived_content_6'))
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
		$msg = trans('sms.post_archived_content', [
			'appName'         => config('app.name'),
			'title'           => $this->post->title,
			'willBeDeletedAt' => $this->willBeDeletedAtFormatted,
			'dateDel'         => $this->willBeDeletedAtFormatted, // @note: need to be removed
		]);
		
		return getAsString($msg);
	}
}
