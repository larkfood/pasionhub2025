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

namespace App\Models\Traits;

trait SubAdmin1Trait
{
	// ===| ADMIN PANEL METHODS |===
	
	public function getNameHtml(): string
	{
		$currentUrl = preg_replace('#/(search)$#', '', url()->current());
		$editUrl = $currentUrl . '/' . $this->code . '/edit';
		
		return '<a href="' . $editUrl . '">' . $this->name . '</a>';
	}
	
	public function adminDivisions2Button($xPanel = false): string
	{
		$url = admin_url('admins1/' . $this->code . '/admins2');
		
		$msg = trans('admin.Admin Divisions 2 of admin1', ['admin_division1' => $this->name]);
		$toolTip = ' data-bs-toggle="tooltip" title="' . $msg . '"';
		
		$out = '<a class="btn btn-xs btn-light" href="' . $url . '"' . $toolTip . '>';
		$out .= '<i class="fa-regular fa-eye"></i> ';
		$out .= mb_ucfirst(trans('admin.admin divisions 2'));
		$out .= '</a>';
		
		return $out;
	}
	
	public function citiesButton($xPanel = false): string
	{
		$url = admin_url('admins1/' . $this->code . '/cities');
		
		$msg = trans('admin.Cities of admin1', ['admin_division1' => $this->name]);
		$toolTip = ' data-bs-toggle="tooltip" title="' . $msg . '"';
		
		$out = '<a class="btn btn-xs btn-light" href="' . $url . '"' . $toolTip . '>';
		$out .= '<i class="fa-regular fa-eye"></i> ';
		$out .= mb_ucfirst(trans('admin.cities'));
		$out .= '</a>';
		
		return $out;
	}
	
	// ===| OTHER METHODS |===
}
