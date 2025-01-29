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

class EmailVerification extends BaseNotification
{
	protected ?object $entity;
	protected ?array $entityRef;
	
	public function __construct(object|int|string $entity, ?array $entityRef)
	{
		if (!is_object($entity)) {
			if (isset($entityRef['namespace'], $entityRef['scopes'])) {
				$object = $entityRef['namespace']::query()
					->withoutGlobalScopes($entityRef['scopes'])
					->find($entity);
				
				$entity = !empty($object) ? $object : null;
			} else {
				$entity = null;
			}
		}
		
		$this->entity = $entity;
		$this->entityRef = $entityRef;
	}
	
	protected function shouldSendNotificationWhen($notifiable): bool
	{
		if (empty($this->entity) || empty($this->entityRef)) {
			return false;
		}
		
		if (!isset($this->entityRef['name'])) {
			return false;
		}
		
		return (empty($this->entity->email_verified_at) && !empty($this->entity->email_token));
	}
	
	protected function determineViaChannels($notifiable): array
	{
		return ['mail'];
	}
	
	public function toMail($notifiable): MailMessage
	{
		$path = $this->entityRef['slug'] . '/verify/email/' . $this->entity->email_token;
		$verificationUrl = (config('plugins.domainmapping.installed'))
			? dmUrl($this->entity->country_code, $path)
			: url($path);
		
		return (new MailMessage)
			->subject(trans('mail.email_verification_title'))
			->greeting(trans('mail.email_verification_content_1', ['userName' => $this->entity->{$this->entityRef['name']},]))
			->line(trans('mail.email_verification_content_2'))
			->action(trans('mail.email_verification_action'), $verificationUrl)
			->line(trans('mail.email_verification_content_3', ['appName' => config('app.name')]))
			->salutation(trans('mail.footer_salutation', ['appName' => config('app.name')]));
	}
}
