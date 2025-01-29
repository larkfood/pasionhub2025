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

namespace App\Observers;

use App\Helpers\Files\Storage\StorageDisk;
use App\Models\CategoryField;
use App\Models\Field;
use App\Models\FieldOption;
use App\Models\PostValue;

class FieldObserver
{
	/**
	 * Listen to the Entry updating event.
	 *
	 * @param Field $field
	 * @return void
	 */
	public function updating(Field $field)
	{
		// Get the original object values
		$original = $field->getOriginal();
		
		// Get the original field type value
		$originalFieldType = $original['type'] ?? null;
		
		// Check if the field type changed
		$isFieldTypeChanged = (!empty($originalFieldType) && !empty($field->type) && $originalFieldType != $field->type);
		if ($isFieldTypeChanged) {
			// Storage Disk Init.
			$disk = StorageDisk::getDisk();
			
			// Get fields types having options or file upload
			$fieldTypesHavingOptions = ['checkbox_multiple', 'radio', 'select'];
			
			// Check if field type has changed from type with options to type without options
			$isFieldTypeChangedToFieldWithoutOptions = (
				in_array($originalFieldType, $fieldTypesHavingOptions)
				&& !in_array($field->type, $fieldTypesHavingOptions)
			);
			
			if ($isFieldTypeChangedToFieldWithoutOptions) {
				
				// Delete all the Custom Field's options
				$options = FieldOption::where('field_id', $field->id);
				if ($options->count() > 0) {
					foreach ($options->cursor() as $option) {
						$option->delete();
					}
				}
				
				// Delete all Posts Custom Field's Values
				$postValues = PostValue::where('field_id', $field->id)->get();
				if ($postValues->count() > 0) {
					foreach ($postValues as $postValue) {
						$postValue->delete();
					}
				}
				
			} else {
				
				// Delete all Posts Custom Field's Values
				$postValues = PostValue::where('field_id', $field->id)->get();
				if ($postValues->count() > 0) {
					foreach ($postValues as $postValue) {
						// If field is of type 'file', remove files (if exists)
						if ($field->type == 'file') {
							if (!empty($postValue->value)) {
								if ($disk->exists($postValue->value)) {
									$disk->delete($postValue->value);
								}
							}
						}
						// Delete the Post's value for this field
						$postValue->delete();
					}
				}
				
			}
		}
	}
	
	/**
	 * Listen to the Entry deleting event.
	 *
	 * @param Field $field
	 * @return void
	 */
	public function deleting($field)
	{
		// Delete all Categories Custom Fields
		$catFields = CategoryField::where('field_id', $field->id)->get();
		if ($catFields->count() > 0) {
			foreach ($catFields as $catField) {
				$catField->delete();
			}
		}
		
		// Delete all the Custom Field's options
		$fieldOptions = FieldOption::where('field_id', $field->id)->get();
		if ($fieldOptions->count() > 0) {
			foreach ($fieldOptions as $fieldOption) {
				$fieldOption->delete();
			}
		}
		
		// Delete all Posts Custom Field's Values
		$postValues = PostValue::where('field_id', $field->id)->get();
		if ($postValues->count() > 0) {
			foreach ($postValues as $postValue) {
				$postValue->delete();
			}
		}
	}
}
