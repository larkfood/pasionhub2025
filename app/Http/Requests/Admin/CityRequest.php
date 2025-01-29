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

class CityRequest extends Request
{
	/**
	 * Prepare the data for validation.
	 *
	 * @return void
	 */
	protected function prepareForValidation(): void
	{
		$input = $this->all();
		
		// population
		if ($this->filled('population')) {
			$population = $this->input('population');
			
			$population = preg_replace('/[^0-9]/', '', $population);
			$input['population'] = !empty($population) ? $population : 0;
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
		$rules = [
			'name'       => ['required', 'min:2', 'max:255'],
			'latitude'   => ['required'],
			'longitude'  => ['required'],
			'time_zone'  => ['required'],
			'population' => ['nullable', 'integer'],
		];
		
		if (in_array($this->method(), ['POST', 'CREATE'])) {
			$rules['country_code'] = ['required', 'min:2', 'max:2'];
			$rules['subadmin1_code'] = ['required'];
		}
		
		return $rules;
	}
	
	/**
	 * Get custom attributes for validator errors.
	 *
	 * @return array
	 */
	public function attributes(): array
	{
		$attributes = [];
		
		$attributes['name'] = trans('admin.Name');
		$attributes['latitude'] = trans('admin.Latitude');
		$attributes['longitude'] = trans('admin.Longitude');
		$attributes['time_zone'] = trans('admin.time_zone_label');
		$attributes['population'] = trans('admin.Population');
		$attributes['country_code'] = trans('admin.Country Code');
		$attributes['subadmin1_code'] = trans('admin.Admin1 Code');
		
		return $attributes;
	}
}
