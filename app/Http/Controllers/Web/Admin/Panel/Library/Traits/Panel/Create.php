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

trait Create
{
	/*
	|--------------------------------------------------------------------------
	|                                   CREATE
	|--------------------------------------------------------------------------
	*/
	
	/**
	 * Insert a row in the database.
	 *
	 * @param $data
	 * @return mixed
	 */
	public function create($data)
	{
		$valuesToStore = $this->compactFakeFields($data, 'create');
		$item = $this->model->create($valuesToStore);
		
		// if there are any relationships available, also sync those
		$this->syncPivot($item, $data);
		
		return $item;
	}
	
	/**
	 * Get all fields needed for the ADD NEW ENTRY form.
	 *
	 * @return array
	 */
	public function getCreateFields(): array
	{
		return $this->createFields;
	}
	
	/**
	 * Get all fields with relation set (model key set on field).
	 *
	 * @param string $form
	 * @return array
	 */
	public function getRelationFields(string $form = 'create'): array
	{
		if ($form == 'create') {
			$fields = $this->createFields;
		} else {
			$fields = $this->updateFields;
		}
		
		$relationFields = [];
		
		foreach ($fields as $field) {
			if (isset($field['model'])) {
				$relationFields[] = $field;
			}
			
			if (isset($field['subfields']) &&
				is_array($field['subfields']) &&
				count($field['subfields'])) {
				foreach ($field['subfields'] as $subfield) {
					$relationFields[] = $subfield;
				}
			}
		}
		
		return $relationFields;
	}
	
	/**
	 * @param $model
	 * @param $data
	 * @param string $form
	 * @return void
	 */
	public function syncPivot($model, $data, string $form = 'create'): void
	{
		$fieldsWithRelationships = $this->getRelationFields($form);
		
		foreach ($fieldsWithRelationships as $key => $field) {
			if (isset($field['pivot']) && $field['pivot']) {
				$values = $data[$field['name']] ?? [];
				$model->{$field['name']}()->sync($values);
				
				if (isset($field['pivotFields'])) {
					foreach ($field['pivotFields'] as $pivotField) {
						foreach ($data[$pivotField] as $pivot_id => $field) {
							$model->{$field['name']}()->updateExistingPivot($pivot_id, [$pivotField => $field]);
						}
					}
				}
			}
			
			if (isset($field['morph']) && $field['morph']) {
				$values = $data[$field['name']] ?? [];
				if ($model->{$field['name']}) {
					$model->{$field['name']}()->update($values);
				} else {
					$model->{$field['name']}()->create($values);
				}
			}
		}
	}
}
