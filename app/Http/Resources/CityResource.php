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

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return array
	 */
	public function toArray(Request $request): array
	{
		if (!isset($this->id)) return [];
		
		$entity = [
			'id' => $this->id,
		];
		
		$columns = $this->getFillable();
		foreach ($columns as $column) {
			$entity[$column] = $this->{$column} ?? null;
		}
		
		$entity['posts_count'] = $this->posts_count ?? 0;
		
		$embed = explode(',', request()->input('embed'));
		
		if (in_array('country', $embed)) {
			$entity['country'] = new CountryResource($this->whenLoaded('country'));
		}
		if (in_array('subAdmin1', $embed)) {
			$entity['subAdmin1'] = new SubAdmin1Resource($this->whenLoaded('subAdmin1'));
		}
		if (in_array('subAdmin2', $embed)) {
			$entity['subAdmin2'] = new SubAdmin2Resource($this->whenLoaded('subAdmin2'));
		}
		
		return $entity;
	}
}
