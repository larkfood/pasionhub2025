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

use App\Helpers\Files\Storage\StorageDisk;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\VonageMessage;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class SellerContacted extends BaseNotification
{
	protected ?object $post;
	
	// CAUTION: Conflict between the Model Message $message and the Laravel Mail Message (Mailable) objects.
	// NOTE: No problem with Laravel Notification.
	protected array $messageArray;
	
	public function __construct(?object $post, ?array $messageArray)
	{
		$this->post = $post;
		$this->messageArray = is_array($messageArray) ? $messageArray : [];
	}
	
	protected function shouldSendNotificationWhen($notifiable): bool
	{
		return (!empty($this->post) && !empty($this->messageArray));
	}
	
	protected function determineViaChannels($notifiable): array
	{
		// Is email can be sent?
		$emailNotificationCanBeSent = (
			!empty($this->post->email)
			&& !empty($this->messageArray['email'])
			&& !isDemoDomain()
		);
		
		// Is SMS can be sent in addition?
		$smsNotificationCanBeSent = (
			config('settings.sms.enable_phone_as_auth_field') == '1'
			&& config('settings.sms.messenger_notifications') == '1'
			&& isset($this->post->auth_field)
			&& $this->post->auth_field == 'phone'
			&& !empty($this->post->phone)
			&& !isDemoDomain()
		);
		
		/*
		if ($emailNotificationCanBeSent && $smsNotificationCanBeSent) {
			if (config('settings.sms.driver') == 'twilio') {
				return ['mail', TwilioChannel::class];
			}
			
			return ['mail', 'vonage'];
		}
		*/
		
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
		
		$mailMessage = (new MailMessage)
			->replyTo($this->messageArray['email'], $this->messageArray['name'])
			->subject(trans('mail.post_seller_contacted_title', [
				'title'   => $this->post->title,
				'appName' => config('app.name'),
			]))
			->line(nl2br($this->messageArray['body']))
			->line(trans('mail.post_seller_contacted_content_1', [
				'name'  => $this->messageArray['name'],
				'email' => $this->messageArray['email'] ?? '',
				'phone' => $this->messageArray['phone'] ?? '',
			]))
			->line(trans('mail.post_seller_contacted_content_2', [
				'title'   => $this->post->title,
				'postUrl' => $postUrl,
				'appUrl'  => url('/'),
				'appName' => config('app.name'),
			]))
			->line('<br>')
			->line(trans('mail.post_seller_contacted_content_3'))
			->line(trans('mail.post_seller_contacted_content_4'))
			->line(trans('mail.post_seller_contacted_content_5'))
			->line(trans('mail.post_seller_contacted_content_6'))
			->line('<br>')
			->line(trans('mail.post_seller_contacted_content_7'))
			->salutation(trans('mail.footer_salutation', ['appName' => config('app.name')]));
		
		// Check & get attached file
		$fileData = null;
		$filename = null;
		if (!empty($this->messageArray['file_path'])) {
			if (!empty($this->messageArray['file_data'])) {
				// Get file's content (from uploaded file)
				$fileData = base64_decode($this->messageArray['file_data']);
				$filename = $this->messageArray['file_path'];
			} else {
				// Get file's content (from DB column)
				$disk = StorageDisk::getDisk();
				if ($disk->exists($this->messageArray['file_path'])) {
					$fileData = $disk->get($this->messageArray['file_path']);
				}
				
				// Get file's short name
				$filename = basename($this->messageArray['file_path']);
			}
		}
		
		// Attachment
		if (!empty($fileData) && !empty($filename)) {
			$mailMessage->attachData($fileData, $filename);
		}
		
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
		$msg = trans('sms.post_seller_contacted_content', [
			'appName' => config('app.name'),
			'title'   => $this->post->title,
			'message' => str(strip_tags($this->messageArray['body']))->limit(20),
		]);
		
		return getAsString($msg);
	}
}
