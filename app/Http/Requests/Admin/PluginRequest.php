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

use App\Rules\PurchaseCodeRule;

class PluginRequest extends Request
{
	protected array|\stdClass|null $plugin = null;
	
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		$rules = [];
		
		$this->plugin = load_plugin($this->segment(3));
		if (!empty($this->plugin)) {
			$pluginId = data_get($this->plugin, 'item_id');
			if (!empty($pluginId)) {
				$rules['purchase_code'] = ['required', new PurchaseCodeRule($pluginId)];
			}
		}
		
		return $rules;
	}
	
	/**
	 * Handle a passed validation attempt.
	 *
	 * @return void
	 */
	protected function passedValidation(): void
	{
		if (empty($this->plugin)) return;
		
		$pluginName = data_get($this->plugin, 'name');
		$purchaseCode = $this->input('purchase_code');
		
		if (empty($pluginName)) return;
		
		$pluginFile = storage_path('framework/plugins/' . $pluginName);
		file_put_contents($pluginFile, $purchaseCode);
	}
}
