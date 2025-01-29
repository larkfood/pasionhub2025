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

namespace App\Http\Requests\Admin\SettingRequest\MailRequest;

use App\Http\Requests\Request;
use App\Providers\AppService\ConfigTrait\MailConfig;

/*
 * Use request() instead of $this since this form request can be called from another
 */

class MailDriverRequest extends Request
{
	use MailConfig;
	
	private ?string $appName;
	private ?string $mailTo;
	private array $settings;
	
	private string $inputPrefix;
	private ?string $validDriverParamsRequiredMessage = null;
	
	public function __construct(?string $appName = null, ?string $mailTo = null, array $settings = [])
	{
		parent::__construct();
		
		$this->appName = $appName;
		$this->mailTo = $mailTo;
		$this->settings = $settings;
		
		$this->inputPrefix = isFromInstallProcess() ? 'settings.mail.' : '';
	}
	
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize(): bool
	{
		$guard = getAuthGuard();
		
		return isFromInstallProcess() || auth($guard)->check();
	}
	
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		$request = request();
		
		$rules = [
			$this->inputPrefix . 'driver' => ['nullable', 'string'],
		];
		
		// Is mail driver need to be validated?
		$isMailDriverTestEnabled = ($request->input($this->inputPrefix . 'driver_test') == '1');
		
		// Get selected mail driver
		$mailDriver = $request->input($this->inputPrefix . 'driver');
		if (empty($mailDriver)) {
			return $rules;
		}
		
		// Mail driver's rules
		if ($mailDriver == 'sendmail') {
			if ($isMailDriverTestEnabled) {
				$rules = array_merge($rules, [
					$this->inputPrefix . 'sendmail_path' => ['required'],
				]);
			}
		}
		
		if ($mailDriver == 'smtp') {
			$rules = array_merge($rules, [
				$this->inputPrefix . 'smtp_host'       => ['required'],
				$this->inputPrefix . 'smtp_port'       => ['required'],
				$this->inputPrefix . 'smtp_username'   => ['nullable'],
				$this->inputPrefix . 'smtp_password'   => ['nullable'],
				$this->inputPrefix . 'smtp_encryption' => ['nullable'],
			]);
		}
		
		if ($mailDriver == 'mailgun') {
			$rules = array_merge($rules, [
				$this->inputPrefix . 'mailgun_domain'     => ['required'],
				$this->inputPrefix . 'mailgun_secret'     => ['required'],
				$this->inputPrefix . 'mailgun_host'       => ['required'],
				$this->inputPrefix . 'mailgun_port'       => ['required'],
				$this->inputPrefix . 'mailgun_username'   => ['required'],
				$this->inputPrefix . 'mailgun_password'   => ['required'],
				$this->inputPrefix . 'mailgun_encryption' => ['required'],
			]);
		}
		
		if ($mailDriver == 'postmark') {
			$rules = array_merge($rules, [
				$this->inputPrefix . 'postmark_token'      => ['required'],
				$this->inputPrefix . 'postmark_host'       => ['required'],
				$this->inputPrefix . 'postmark_port'       => ['required'],
				$this->inputPrefix . 'postmark_username'   => ['required'],
				$this->inputPrefix . 'postmark_password'   => ['required'],
				$this->inputPrefix . 'postmark_encryption' => ['required'],
			]);
		}
		
		if ($mailDriver == 'ses') {
			$rules = array_merge($rules, [
				$this->inputPrefix . 'ses_key'        => ['required'],
				$this->inputPrefix . 'ses_secret'     => ['required'],
				$this->inputPrefix . 'ses_region'     => ['required'],
				$this->inputPrefix . 'ses_token'      => ['nullable'],
				$this->inputPrefix . 'ses_host'       => ['required'],
				$this->inputPrefix . 'ses_port'       => ['required'],
				$this->inputPrefix . 'ses_username'   => ['required'],
				$this->inputPrefix . 'ses_password'   => ['required'],
				$this->inputPrefix . 'ses_encryption' => ['required'],
			]);
		}
		
		if ($mailDriver == 'sparkpost') {
			$rules = array_merge($rules, [
				$this->inputPrefix . 'sparkpost_secret'     => ['required'],
				$this->inputPrefix . 'sparkpost_host'       => ['required'],
				$this->inputPrefix . 'sparkpost_port'       => ['required'],
				$this->inputPrefix . 'sparkpost_username'   => ['required'],
				$this->inputPrefix . 'sparkpost_password'   => ['required'],
				$this->inputPrefix . 'sparkpost_encryption' => ['required'],
			]);
		}
		
		if ($mailDriver == 'resend') {
			$rules = array_merge($rules, [
				$this->inputPrefix . 'resend_api_key' => ['required'],
			]);
		}
		
		if ($mailDriver == 'mailersend') {
			$rules = array_merge($rules, [
				$this->inputPrefix . 'mailersend_api_key' => ['required'],
			]);
		}
		
		if ($mailDriver == 'brevo') {
			$rules = array_merge($rules, [
				$this->inputPrefix . 'brevo_api_key' => ['required'],
			]);
		}
		
		if ($isMailDriverTestEnabled) {
			if (isFromAdminPanel()) {
				$rules['email_always_to'] = ['required'];
			}
		}
		
		// Get the required fields, then check if required fields are not empty in the request
		$emptyRequiredFields = collect($rules)
			->filter(function ($rule) {
				if (is_array($rule)) {
					return in_array('required', $rule);
				} else if (is_string($rule)) {
					return str_contains($rule, 'required');
				}
				
				return false;
			})->filter(fn ($rule, $field) => empty($request->input($field)));
		
		// Check mail sending parameters
		if ($isMailDriverTestEnabled && $emptyRequiredFields->isEmpty()) {
			if (!empty($this->appName)) {
				config()->set('settings.app.name', $this->appName);
			}
			
			$errorMessage = $this->testMailConfig(true, $this->mailTo, $this->settings);
			if (!empty($errorMessage)) {
				$rules = array_merge($rules, [
					$this->inputPrefix . 'valid_driver_params' => 'required',
				]);
				$this->validDriverParamsRequiredMessage = $errorMessage;
			}
		}
		
		return $rules;
	}
	
	/**
	 * @return array
	 */
	public function messages(): array
	{
		$messages = [];
		
		if (!empty($this->validDriverParamsRequiredMessage)) {
			$messages[$this->inputPrefix . 'valid_driver_params.required'] = $this->validDriverParamsRequiredMessage;
		}
		
		return array_merge(parent::messages(), $messages);
	}
	
	/**
	 * @return array
	 */
	public function attributes(): array
	{
		$attributes = [
			$this->inputPrefix . 'driver'               => 'Mail Driver',
			$this->inputPrefix . 'sendmail_path'        => 'Sendmail Path',
			$this->inputPrefix . 'smtp_host'            => 'SMTP Host',
			$this->inputPrefix . 'smtp_port'            => 'SMTP Port',
			$this->inputPrefix . 'smtp_username'        => 'SMTP Username',
			$this->inputPrefix . 'smtp_password'        => 'SMTP Password',
			$this->inputPrefix . 'smtp_encryption'      => 'SMTP Encryption',
			$this->inputPrefix . 'mailgun_domain'       => 'Mailgun Domain',
			$this->inputPrefix . 'mailgun_secret'       => 'Mailgun Secret',
			$this->inputPrefix . 'mailgun_host'         => 'Mailgun Host',
			$this->inputPrefix . 'mailgun_port'         => 'Mailgun Port',
			$this->inputPrefix . 'mailgun_username'     => 'Mailgun Username',
			$this->inputPrefix . 'mailgun_password'     => 'Mailgun Password',
			$this->inputPrefix . 'mailgun_encryption'   => 'Mailgun Encryption',
			$this->inputPrefix . 'postmark_token'       => 'Postmark Token',
			$this->inputPrefix . 'postmark_host'        => 'Postmark Host',
			$this->inputPrefix . 'postmark_port'        => 'Postmark Port',
			$this->inputPrefix . 'postmark_username'    => 'Postmark Username',
			$this->inputPrefix . 'postmark_password'    => 'Postmark Password',
			$this->inputPrefix . 'postmark_encryption'  => 'Postmark Encryption',
			$this->inputPrefix . 'ses_key'              => 'SES Key',
			$this->inputPrefix . 'ses_secret'           => 'SES Secret',
			$this->inputPrefix . 'ses_region'           => 'SES Region',
			$this->inputPrefix . 'ses_host'             => 'SES Host',
			$this->inputPrefix . 'ses_port'             => 'SES Port',
			$this->inputPrefix . 'ses_username'         => 'SES Username',
			$this->inputPrefix . 'ses_password'         => 'SES Password',
			$this->inputPrefix . 'ses_encryption'       => 'SES Encryption',
			$this->inputPrefix . 'sparkpost_secret'     => 'Sparkpost Secret',
			$this->inputPrefix . 'sparkpost_host'       => 'Sparkpost Host',
			$this->inputPrefix . 'sparkpost_port'       => 'Sparkpost Port',
			$this->inputPrefix . 'sparkpost_username'   => 'Sparkpost Username',
			$this->inputPrefix . 'sparkpost_password'   => 'Sparkpost Password',
			$this->inputPrefix . 'sparkpost_encryption' => 'Sparkpost Encryption',
			$this->inputPrefix . 'resend_api_key'       => 'Resend API Key',
			$this->inputPrefix . 'mailersend_api_key'   => 'Mailersend API Key',
			$this->inputPrefix . 'brevo_api_key'        => 'Brevo API Key',
		];
		
		return array_merge(parent::attributes(), $attributes);
	}
}
