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

namespace App\Http\Controllers\Web\Admin\Panel\Library\Traits\Models;

trait Crud
{
	use HasRelationshipFields;
	use HasUploadFields;
	use HasFakeFields;
	use HasTranslatableFields;
	
	/*
    |--------------------------------------------------------------------------
    | Translation Methods
    |--------------------------------------------------------------------------
    */
	
	
	/*
	|--------------------------------------------------------------------------
	| Methods for ALL models
	|--------------------------------------------------------------------------
	*/
	
	/**
	 * @param bool $xPanel
	 * @return string
	 */
	public function bulkDeletionButton($xPanel = false): string
	{
		$tooltip = ' data-bs-toggle="tooltip" title="' . trans('admin.delete_selection') . '"';
		
		// Button
		$out = '<button name="deletion" class="bulk-action btn btn-danger shadow"' . $tooltip . '>';
		$out .= '<i class="fa-solid fa-xmark"></i> ';
		$out .= trans('admin.delete');
		$out .= '</button>';
		
		return $out;
	}
	
	/**
	 * @param $xPanel
	 * @return string|null
	 */
	public function bulkActivationButton($xPanel = false): ?string
	{
		if (!isset($xPanel->model) || !in_array('active', $xPanel->model->getFillable())) {
			return null;
		}
		
		$tooltip = ' data-bs-toggle="tooltip" title="' . trans('admin.activate_selection') . '"';
		
		// Button
		$out = '<button name="activation" class="bulk-action btn btn-outline-secondary shadow"' . $tooltip . '>';
		$out .= '<i class="fa-solid fa-toggle-on"></i> ';
		$out .= trans('admin.activate');
		$out .= '</button>';
		
		return $out;
	}
	
	/**
	 * @param $xPanel
	 * @return string|null
	 */
	public function bulkDeactivationButton($xPanel = false): ?string
	{
		if (!isset($xPanel->model) || !in_array('active', $xPanel->model->getFillable())) {
			return null;
		}
		
		$tooltip = ' data-bs-toggle="tooltip" title="' . trans('admin.disable_selection') . '"';
		
		// Button
		$out = '<button name="deactivation" class="bulk-action btn btn-outline-secondary shadow"' . $tooltip . '>';
		$out .= '<i class="fa-solid fa-toggle-off"></i> ';
		$out .= trans('admin.disable');
		$out .= '</button>';
		
		return $out;
	}
	
	/**
	 * @param $xPanel
	 * @return string|null
	 */
	public function bulkApprovalBtn($xPanel = false): ?string
	{
		if (
			!isset($xPanel->model)
			|| !in_array('reviewed_at', $xPanel->model->getFillable())
			|| !config('settings.listing_form.listings_review_activation')
		) {
			return null;
		}
		
		$tooltip = ' data-bs-toggle="tooltip" title="' . trans('admin.approve_selection') . '"';
		
		// Button
		$out = '<button name="approval" class="bulk-action btn btn-outline-secondary shadow"' . $tooltip . '>';
		$out .= '<i class="fa-solid fa-toggle-on"></i> ';
		$out .= trans('admin.approve');
		$out .= '</button>';
		
		return $out;
	}
	
	/**
	 * @param $xPanel
	 * @return string|null
	 */
	public function bulkDisapprovalBtn($xPanel = false): ?string
	{
		if (
			!isset($xPanel->model)
			|| !in_array('reviewed_at', $xPanel->model->getFillable())
			|| !config('settings.listing_form.listings_review_activation')
		) {
			return null;
		}
		
		$tooltip = ' data-bs-toggle="tooltip" title="' . trans('admin.disapprove_selection') . '"';
		
		// Button
		$out = '<button name="disapproval" class="bulk-action btn btn-outline-secondary shadow"' . $tooltip . '>';
		$out .= '<i class="fa-solid fa-toggle-off"></i> ';
		$out .= trans('admin.disapprove');
		$out .= '</button>';
		
		return $out;
	}
}
