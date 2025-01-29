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

use App\Rules\CurrenciesCodesAreValidRule;

class CountryRequest extends Request
{
	/**
	 * Prepare the data for validation.
	 *
	 * @return void
	 */
	protected function prepareForValidation(): void
	{
		$input = $this->all();
		
		// admin_type
		$adminTypeList = array_keys(enumCountryAdminTypes());
		$adminType = $this->filled('admin_type') ? $this->input('admin_type') : '0';
		$input['admin_type'] = in_array($adminType, $adminTypeList) ? $adminType : '0';
		
		// currencies
		if ($this->filled('currencies')) {
			// Add new data field before it gets sent to the validator
			$currencies = explode(',', $this->input('currencies'));
			$currenciesCodes = collect($currencies)
				->map(fn ($value) => trim($value))
				->reject(fn ($value) => empty($value))
				->toArray();
			
			$input['currencies'] = @implode(',', $currenciesCodes);
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
		return [
			'code'           => ['required', 'min:2', 'max:2'],
			'name'           => ['required', 'min:2', 'max:255'],
			'continent_code' => ['required'],
			'currency_code'  => ['required'],
			'phone'          => ['required'],
			'languages'      => ['required'],
			'currencies'     => ['nullable', new CurrenciesCodesAreValidRule()],
		];
	}
}
