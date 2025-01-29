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

trait SectionTrait
{
	// ===| ADMIN PANEL METHODS |===
	
	public function resetHomepageReOrderButton($xPanel = false): string
	{
		$url = admin_url('sections/reset/all/reorder');
		
		$msg = trans('admin.Reset the homepage sections reorder');
		$tooltip = ' data-bs-toggle="tooltip" title="' . $msg . '"';
		
		// Button
		$out = '<a class="btn btn-warning text-white shadow" href="' . $url . '"' . $tooltip . '>';
		$out .= '<i class="fa-solid fa-arrow-up-wide-short"></i> ';
		$out .= trans('admin.Reset sections reorganization');
		$out .= '</a>';
		
		return $out;
	}
	
	public function resetHomepageSettingsButton($xPanel = false): string
	{
		$url = admin_url('sections/reset/all/options');
		
		$msg = trans('admin.Reset all the homepage settings');
		$tooltip = ' data-bs-toggle="tooltip" title="' . $msg . '"';
		
		// Button
		$out = '<a class="btn btn-danger shadow" href="' . $url . '"' . $tooltip . '>';
		$out .= '<i class="fa-solid fa-industry"></i> ';
		$out .= trans('admin.Return to factory settings');
		$out .= '</a>';
		
		return $out;
	}
	
	public function getNameHtml(): string
	{
		$currentUrl = preg_replace('#/(search)$#', '', url()->current());
		$url = $currentUrl . '/' . $this->getKey() . '/edit';
		
		return '<a href="' . $url . '">' . $this->name . '</a>';
	}
	
	public function configureButton($xPanel = false): string
	{
		$url = admin_url('sections/' . $this->id . '/edit');
		
		$msg = trans('admin.configure_entity', ['entity' => $this->name]);
		$tooltip = ' data-bs-toggle="tooltip" title="' . $msg . '"';
		
		$out = '<a class="btn btn-xs btn-primary" href="' . $url . '"' . $tooltip . '>';
		$out .= '<i class="fa-solid fa-gear"></i> ';
		$out .= mb_ucfirst(trans('admin.Configure'));
		$out .= '</a>';
		
		return $out;
	}
	
	// ===| OTHER METHODS |===
	
	public static function optionsThatNeedToBeHidden(): array
	{
		return [];
	}
}
