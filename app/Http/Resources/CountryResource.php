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

namespace App\Http\Resources;

use App\Enums\Continent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return array
	 */
	public function toArray(Request $request): array
	{
		if (!isset($this->code)) return [];
		
		$entity = [
			'code' => $this->code,
		];
		
		$columns = $this->getFillable();
		foreach ($columns as $column) {
			$entity[$column] = $this->{$column} ?? null;
		}
		
		$entity['icode'] = $this->icode ?? null;
		$entity['flag_url'] = $this->flag_url ?? null;
		$entity['flag16_url'] = $this->flag16_url ?? null;
		$entity['flag24_url'] = $this->flag24_url ?? null;
		$entity['flag32_url'] = $this->flag32_url ?? null;
		$entity['flag48_url'] = $this->flag48_url ?? null;
		$entity['flag64_url'] = $this->flag64_url ?? null;
		$entity['background_image_url'] = $this->background_image_url ?? null;
		
		$embed = explode(',', request()->input('embed'));
		
		if (in_array('currency', $embed)) {
			$entity['currency'] = new CurrencyResource($this->whenLoaded('currency'));
		}
		if (in_array('continent', $embed)) {
			if (!empty($this->continent_code)) {
				$entity['continent'] = Continent::find($this->continent_code);
			}
		}
		
		return $entity;
	}
}
