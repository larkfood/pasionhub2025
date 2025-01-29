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

namespace App\Http\Controllers\Web\Admin\Panel\Library\Traits\Panel;

trait Update
{
	/*
	|--------------------------------------------------------------------------
	|                                   UPDATE
	|--------------------------------------------------------------------------
	*/
	
	/**
	 * Update a row in the database.
	 *
	 * @param $id
	 * @param $data
	 * @return mixed
	 */
	public function update($id, $data)
	{
		$item = $this->model->findOrFail($id);
		$valuesToStore = $this->compactFakeFields($data, 'update');
		$updated = $item->update($valuesToStore);
		
		if ($this->isEnabledSyncPivot()) {
			$this->syncPivot($item, $data, 'update');
		}
		
		return $item;
	}
	
	/**
	 * Get all fields needed for the EDIT ENTRY form.
	 *
	 * @param $id
	 * @return array  The fields with attributes, fake attributes and values.
	 */
	public function getUpdateFields($id): array
	{
		$fields = (array)$this->updateFields;
		$entry = $this->getEntry($id);
		
		foreach ($fields as $key => $field) {
			// set the value
			if (!isset($field['value'])) {
				if (isset($field['subfields'])) {
					$fields[$key]['value'] = [];
					foreach ($field['subfields'] as $k => $subfield) {
						$fields[$key]['value'][] = $entry->{$subfield['name']};
					}
				} else {
					$fields[$key]['value'] = $entry->{$field['name']};
					
					if (isset($entry->value) && is_array($entry->value) && array_key_exists($key, $entry->value)) {
						$fields[$key]['value'] = $entry->value[$key];
					}
				}
			}
		}
		
		// always have a hidden input for the entry id
		$fields['id'] = [
			'name'  => $entry->getKeyName(),
			'value' => $entry->getKey(),
			'type'  => 'hidden',
		];
		
		if ($this->model->translationEnabled()) {
			$fields['locale'] = [
				'name'  => 'locale',
				'type'  => 'hidden',
				'value' => request()->input('locale') ?? app()->getLocale(),
			];
		}
		
		return $fields;
	}
}
