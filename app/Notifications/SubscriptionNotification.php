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

use App\Models\Package;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Notifications\Messages\MailMessage;

class SubscriptionNotification extends BaseNotification
{
	protected ?Payment $payment;
	protected ?object $user;
	protected ?Package $package = null;
	protected ?PaymentMethod $paymentMethod = null;
	
	public function __construct(?Payment $payment, ?object $user)
	{
		$this->payment = $payment;
		$this->user = $user;
		if (isset($payment->package_id)) {
			$this->package = Package::find($payment->package_id);
		}
		if (isset($payment->payment_method_id)) {
			$this->paymentMethod = PaymentMethod::find($payment->payment_method_id);
		}
	}
	
	protected function shouldSendNotificationWhen($notifiable): bool
	{
		return (
			!empty($this->payment)
			&& !empty($this->user)
			&& !empty($this->package)
			&& !empty($this->paymentMethod)
		);
	}
	
	protected function determineViaChannels($notifiable): array
	{
		return ['mail'];
	}
	
	public function toMail($notifiable): MailMessage
	{
		return (new MailMessage)
			->subject(trans('mail.subscription_notification_title'))
			->greeting(trans('mail.subscription_notification_content_1'))
			->line(trans('mail.subscription_notification_content_2', [
				'userName'    => $this->user->name,
				'packageName' => !empty($this->package->short_name)
					? $this->package->short_name
					: $this->package->name,
			]))
			->line('<br>')
			->line(trans('mail.subscription_notification_content_3', [
				'packageName'       => !empty($this->package->short_name)
					? $this->package->short_name
					: $this->package->name,
				'userName'          => $this->user->name,
				'userId'            => $this->user->id,
				'amount'            => $this->package->price,
				'currency'          => $this->package->currency_code,
				'paymentMethodName' => $this->paymentMethod->display_name,
			]))
			->salutation(trans('mail.footer_salutation', ['appName' => config('app.name')]));
	}
}
