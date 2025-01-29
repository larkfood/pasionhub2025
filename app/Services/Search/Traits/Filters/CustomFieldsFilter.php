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

namespace App\Services\Search\Traits\Filters;

use App\Models\Field;

trait CustomFieldsFilter
{
	protected function applyCustomFieldsFilter(): void
	{
		if (!(isset($this->posts) && isset($this->having))) {
			return;
		}
		
		$inputFields = [];
		if (request()->filled('cf')) {
			$inputFields = request()->input('cf');
		}
		
		if (!(is_array($inputFields) && count($inputFields) > 0)) {
			return;
		}
		
		foreach ($inputFields as $fieldId => $postValue) {
			// Get the Field object
			$field = Field::find($fieldId);
			if (empty($field)) {
				continue;
			}
			
			if (is_array($postValue)) {
				// 'checkbox_multiple' field type
				foreach ($postValue as $optionId => $optionValue) {
					if (is_array($optionValue)) continue;
					if (!is_array($optionValue) && trim($optionValue) == '') continue;
					
					$this->posts->whereHas('postValues', function ($query) use ($field, $optionId, $optionValue) {
						$query->where('field_id', $field->id)
							->where('option_id', $optionId)
							->where('value', $optionValue);
					});
					
				}
			} else {
				// Other fields
				if (trim($postValue) == '') {
					continue;
				}
				
				// Date Value ('date', 'date_time')
				if (in_array($field->type, ['date', 'date_time'])) {
					$postValue = date('Y-m-d', strtotime($postValue));
					
					$this->posts->whereHas('postValues', function ($query) use ($field, $postValue) {
						$query->where('field_id', $field->id)
							->whereRaw('DATE(value) = ?', [$postValue]);
					});
				}
				
				// Dates Range Value ('date_range')
				if ($field->type == 'date_range') {
					/*
					 * Date Range Format: YYYY/MM/DD - ZZZZ/YY/XX
					 * SUBSTR(field, 1, 10) => YYYY/MM/DD
					 * SUBSTR(field, 14, 23) => ZZZZ/YY/XX
					 */
					
					$tmp = explode('-', $postValue);
					$tmp = array_map('trim', $tmp);
					
					if (!isset($tmp[0]) || !isset($tmp[1])) {
						continue;
					}
					
					$startDate = date('Y-m-d', strtotime($tmp[0]));
					$endDate = date('Y-m-d', strtotime($tmp[1]));
					
					$this->posts->whereHas('postValues', function ($query) use ($field, $startDate, $endDate) {
						$query->where('field_id', $field->id)
							->whereRaw('DATE(SUBSTR(value, 1, 10)) >= ?', [$startDate])
							->whereRaw('DATE(SUBSTR(value, 14, 23)) <= ?', [$endDate]);
					});
				}
				
				// Integer Value ('checkbox', 'select', 'radio', 'number')
				if (in_array($field->type, ['checkbox', 'select', 'radio', 'number'])) {
					$this->posts->whereHas('postValues', function ($query) use ($field, $postValue) {
						$query->where('field_id', $field->id)
							->where('value', 'LIKE', $postValue);
					});
				}
				
				// Text Value ('text', 'textarea', 'url')
				if (in_array($field->type, ['text', 'textarea', 'url'])) {
					$this->posts->whereHas('postValues', function ($query) use ($field, $postValue) {
						$query->where('field_id', $field->id)
							->where('value', 'LIKE', '%' . $postValue . '%');
					});
				}
			}
		}
	}
}
