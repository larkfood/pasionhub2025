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

namespace App\Http\Requests\Setup\Install;

use App\Http\Requests\Admin\SettingRequest\MailRequest\MailDriverRequest;
use App\Http\Requests\Request;
use App\Rules\NoSpacesRule;
use App\Rules\PurchaseCodeRule;
use Illuminate\Validation\Rules\Password;

class SiteInfoRequest extends Request
{
	protected array $mailDriverRulesMessages = [];
	protected array $mailDriverRulesAttributes = [];
	
	private string $appInput = 'settings.app.';
	private string $localInput = 'settings.localization.';
	private string $mailInput = 'settings.mail.';
	private string $userInput = 'user.';
	
	/**
	 * Prepare the data for validation.
	 *
	 * @return void
	 */
	protected function prepareForValidation(): void
	{
		$input = $this->all();
		
		// Get the email address & the country code
		$email = data_get($input, 'user.email');
		$countryCode = data_get($input, 'settings.localization.default_country_code');
		
		// Fill the user's country
		$input['user']['country_code'] = $countryCode;
		
		// Fill the app's settings' email
		$input['settings']['app']['email'] = $email;
		
		// Use the user email as fallback email address for drivers' sender
		$mailSettings = $input['settings']['mail'] ?? [];
		if (!empty($mailSettings)) {
			$mailSettings['driver'] = $mailSettings['driver'] ?? 'sendmail';
			
			$mailSettings['sendmail_path'] = $mailSettings['sendmail_path'] ?? '/usr/sbin/sendmail -bs';
			$mailSettings['mailgun_endpoint'] = $mailSettings['mailgun_endpoint'] ?? 'api.mailgun.net';
			
			if (!empty($email)) {
				$mailSettings['sendmail_email_sender'] = $mailSettings['sendmail_email_sender'] ?? $email;
				$mailSettings['smtp_email_sender'] = $mailSettings['smtp_email_sender'] ?? $email;
				$mailSettings['mailgun_email_sender'] = $mailSettings['mailgun_email_sender'] ?? $email;
				$mailSettings['postmark_email_sender'] = $mailSettings['postmark_email_sender'] ?? $email;
				$mailSettings['ses_email_sender'] = $mailSettings['ses_email_sender'] ?? $email;
				$mailSettings['sparkpost_email_sender'] = $mailSettings['sparkpost_email_sender'] ?? $email;
				$mailSettings['resend_email_sender'] = $mailSettings['resend_email_sender'] ?? $email;
				$mailSettings['mailersend_email_sender'] = $mailSettings['mailersend_email_sender'] ?? $email;
				$mailSettings['brevo_email_sender'] = $mailSettings['brevo_email_sender'] ?? $email;
			}
			
			$input['settings']['mail'] = $mailSettings;
		}
		
		request()->merge($input); // Required!
		$this->merge($input);
	}
	
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		// Make sure the session is working
		$rules = [
			$this->appInput . 'name'                   => ['required', 'max:100'],
			$this->appInput . 'slogan'                 => ['required', 'max:255'],
			$this->appInput . 'purchase_code'          => ['required', new PurchaseCodeRule(config('larapen.core.item.id'))],
			$this->localInput . 'default_country_code' => ['required'],
			$this->userInput . 'name'                  => ['required', 'string', 'max:100'],
			$this->userInput . 'email'                 => ['required', 'email', 'max:100'],
			$this->userInput . 'password'              => ['required', new NoSpacesRule(), Password::min(6)->max(60)],
		];
		
		// Selected mail driver's rules
		$mailDriver = $this->input($this->mailInput . 'driver');
		if (!empty($mailDriver)) {
			$appName = $this->input($this->appInput . 'name');
			$mailTo = $this->input($this->userInput . 'email');
			$settings = $this->input('settings.mail');
			
			$mailDriverRequest = new MailDriverRequest($appName, $mailTo, $settings);
			$rules = $rules + $mailDriverRequest->rules();
			$this->mailDriverRulesMessages = $mailDriverRequest->messages();
			$this->mailDriverRulesAttributes = $mailDriverRequest->attributes();
		}
		
		return $rules;
	}
	
	/**
	 * @return array
	 */
	public function messages(): array
	{
		$messages = [];
		
		if (!empty($this->mailDriverRulesMessages)) {
			$messages = $messages + $this->mailDriverRulesMessages;
		}
		
		return array_merge(parent::messages(), $messages);
	}
	
	/**
	 * @return array
	 */
	public function attributes(): array
	{
		$attributes = [
			$this->appInput . 'name'                   => mb_strtolower(trans('messages.settings_app_name')),
			$this->appInput . 'slogan'                 => mb_strtolower(trans('messages.settings_app_slogan')),
			$this->appInput . 'purchase_code'          => mb_strtolower(trans('messages.settings_app_purchase_code')),
			$this->localInput . 'default_country_code' => mb_strtolower(trans('messages.settings_localization_default_country_code')),
			$this->userInput . 'name'                  => mb_strtolower(trans('messages.user_name')),
			$this->userInput . 'email'                 => mb_strtolower(trans('messages.user_email')),
			$this->userInput . 'password'              => mb_strtolower(trans('messages.user_password')),
		];
		
		if (!empty($this->mailDriverRulesAttributes)) {
			$attributes = $attributes + $this->mailDriverRulesAttributes;
		}
		
		return array_merge(parent::attributes(), $attributes);
	}
}
