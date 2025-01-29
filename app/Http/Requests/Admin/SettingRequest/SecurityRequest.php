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

namespace App\Http\Requests\Admin\SettingRequest;

use App\Helpers\DBTool;
use App\Http\Requests\Admin\Request;
use Illuminate\Support\Facades\Schema;

/*
 * Use request() instead of $this since this form request can be called from another
 */

class SecurityRequest extends Request
{
	private ?string $validHoneypotNameFieldNameMessage;
	private ?string $honeypotValidFromFieldNameMessage;
	private ?string $intlExtensionInstallationMessage;
	
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		$request = request();
		
		$rules = [
			'honeypot_name_field_name'       => ['required'],
			'honeypot_valid_from_field_name' => ['required'],
		];
		
		// Honeypot
		$allFormsFields = $this->getAllFormsFields();
		
		$nameFieldName = $request->input('honeypot_name_field_name');
		if (!empty($nameFieldName)) {
			if (in_array($nameFieldName, $allFormsFields)) {
				$rules['valid_honeypot_name_field_name'] = ['required'];
				$this->validHoneypotNameFieldNameMessage = trans('admin.honeypot_reserved_field_name_error_message', [
					'attribute'      => trans('admin.honeypot_name_field_name_label'),
					'attributeValue' => $nameFieldName,
				]);
			}
		}
		
		$validFromFieldName = $request->input('honeypot_valid_from_field_name');
		if (!empty($validFromFieldName)) {
			if (in_array($validFromFieldName, $allFormsFields)) {
				$rules['valid_honeypot_valid_from_field_name'] = ['required'];
				$this->honeypotValidFromFieldNameMessage = trans('admin.honeypot_reserved_field_name_error_message', [
					'attribute'      => trans('admin.honeypot_valid_from_field_name_label'),
					'attributeValue' => $validFromFieldName,
				]);
			}
		}
		
		// Password validator
		$rules['password_min_length'] = ['required', 'integer', 'min:4', 'lte:password_max_length'];
		$rules['password_max_length'] = ['required', 'integer', 'max:100', 'gte:password_min_length'];
		
		// Email address validator
		if (
			(
				$request->filled('email_validator_dns')
				|| $request->filled('email_validator_spoof')
			)
			&& !extension_loaded('intl')
		) {
			$rules['intl_extension_installation'] = ['required'];
			$this->intlExtensionInstallationMessage = trans('admin.intl_extension_missing_error_message_for_email_validation');
		}
		
		return $rules;
	}
	
	/**
	 * @return array
	 */
	public function messages(): array
	{
		$messages = [];
		
		if (!empty($this->validHoneypotNameFieldNameMessage)) {
			$messages['valid_honeypot_name_field_name'] = $this->validHoneypotNameFieldNameMessage;
		}
		
		if (!empty($this->honeypotValidFromFieldNameMessage)) {
			$messages['valid_honeypot_valid_from_field_name'] = $this->honeypotValidFromFieldNameMessage;
		}
		
		if (!empty($this->intlExtensionInstallationMessage)) {
			$messages['intl_extension_installation'] = $this->intlExtensionInstallationMessage;
		}
		
		return array_merge(parent::messages(), $messages);
	}
	
	/**
	 * @return array
	 */
	public function attributes(): array
	{
		$attributes = [
			'honeypot_name_field_name'       => trans('admin.honeypot_name_field_name_label'),
			'honeypot_valid_from_field_name' => trans('admin.honeypot_valid_from_field_name_label'),
			'password_min_length'            => trans('admin.password_min_length_label'),
			'password_max_length'            => trans('admin.password_max_length_label'),
			'email_validator_dns'            => trans('admin.email_validator_dns_label'),
			'email_validator_spoof'          => trans('admin.email_validator_spoof_label'),
		];
		
		return array_merge(parent::attributes(), $attributes);
	}
	
	// PRIVATE
	
	private function getAllFormsFields(): array
	{
		$fields = [];
		
		try {
			$dbColumns = $this->getAllDbColumns();
			$contactFields = ['first_name', 'last_name', 'company_name', 'email', 'message'];
			$reportFields = ['report_type_id', 'email', 'message', 'post_id', 'abuseForm'];
			$sendByEmailFields = ['recipient_email', 'post_id', 'sendByEmailForm'];
			$otherFields = ['_method', '_token', 'captcha', 'g-recaptcha-response'];
			
			$fields = array_merge($fields, $dbColumns);
			$fields = array_merge($fields, $contactFields);
			$fields = array_merge($fields, $reportFields);
			$fields = array_merge($fields, $sendByEmailFields);
			$fields = array_merge($fields, $otherFields);
			
			$fields = collect($fields)->unique()->toArray();
		} catch (\Throwable $e) {
		}
		
		return $fields;
	}
	
	private function getAllDbColumns(): array
	{
		$columns = [];
		
		$tables = DBTool::getDatabaseTables(withPrefix: false);
		foreach ($tables as $table) {
			$tableColumns = Schema::getColumnListing($table);
			if (is_array($tableColumns)) {
				$columns = array_merge($columns, $tableColumns);
			}
		}
		
		return collect($columns)->unique()->toArray();
	}
}
