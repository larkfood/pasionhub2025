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

namespace App\Http\Requests\Admin;

class SettingRequest extends Request
{
	protected array $rulesMessages = [];
	protected array $rulesAttributes = [];
	
	/**
	 * Prepare the data for validation.
	 *
	 * @return void
	 */
	protected function prepareForValidation(): void
	{
		$input = $this->all();
		
		// Get the right Setting Request class
		$settingClass = $this->getSettingClass();
		if (class_exists($settingClass)) {
			$formRequest = new $settingClass();
			if (method_exists($formRequest, 'customPrepareForValidation')) {
				$input = $formRequest->customPrepareForValidation($input);
			}
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
		$rules = [];
		
		// Get the right Setting Request class
		$settingClass = $this->getSettingClass();
		
		if (class_exists($settingClass)) {
			$formRequest = new $settingClass();
			$rules = $formRequest->rules();
			$this->rulesMessages = $formRequest->messages();
			$this->rulesAttributes = $formRequest->attributes();
		}
		
		return $rules;
	}
	
	/**
	 * @return array
	 */
	public function messages(): array
	{
		$messages = [];
		
		if (!empty($this->rulesMessages)) {
			$messages = $messages + $this->rulesMessages;
		}
		
		return array_merge(parent::messages(), $messages);
	}
	
	/**
	 * @return array
	 */
	public function attributes(): array
	{
		$attributes = [];
		
		if (!empty($this->rulesAttributes)) {
			$attributes = $attributes + $this->rulesAttributes;
		}
		
		return array_merge(parent::attributes(), $attributes);
	}
	
	/**
	 * Get the right Setting class
	 *
	 * @return string
	 */
	private function getSettingClass(): string
	{
		$setting = $this->getSetting();
		if (empty($setting)) return '';
		
		$classKey = $setting->key ?? '';
		
		// Get class name
		$className = str($classKey)->camel()->ucfirst()->append('Request');
		
		// Get class full qualified name (i.e. with namespace)
		$namespace = '\App\Http\Requests\Admin\SettingRequest\\';
		$class = $className->prepend($namespace)->toString();
		
		// If the class doesn't exist in the core app, try to get it from add-ons
		if (!class_exists($class)) {
			$namespace = plugin_namespace($classKey) . '\app\Http\Requests\Admin\SettingRequest\\';
			$class = $className->prepend($namespace)->toString();
		}
		
		return $class;
	}
	
	/**
	 * Get the setting
	 */
	private function getSetting()
	{
		$setting = null;
		
		// Get right model class & its segment index
		$segmentIndex = 3;
		$model = '\App\Models\Setting';
		if (str_contains(currentRouteAction(), 'DomainSettingController')) {
			$segmentIndex = 5;
			$model = '\extras\plugins\domainmapping\app\Models\DomainSetting';
		}
		
		if (class_exists($model)) {
			// Get the setting's ID
			$settingId = $this->segment($segmentIndex);
			if (!empty($settingId)) {
				/**
				 * Get the setting
				 *
				 * @var \Illuminate\Database\Eloquent\Model $model
				 */
				$setting = $model::find($settingId);
			}
		}
		
		return $setting;
	}
}
