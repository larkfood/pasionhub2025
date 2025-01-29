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

namespace App\Http\Controllers\Web\Admin\Traits\InlineRequest;

trait SectionTrait
{
	/**
	 * Update the 'active' column of the sections table
	 *
	 * @param $section
	 * @param $column
	 * @return \Illuminate\Http\JsonResponse
	 */
	protected function updateSectionData($section, $column): \Illuminate\Http\JsonResponse
	{
		$isValidCondition = ($this->table == 'sections' && $column == 'active' && !empty($section));
		if (!$isValidCondition) {
			$error = trans('admin.inline_req_condition', ['table' => $this->table, 'column' => $column]);
			
			return $this->responseError($error, 400);
		}
		
		// Update the 'active' column
		// See the "app/Observers/SectionObserver.php" file for complete operation
		$section->{$column} = ($section->{$column} != 1) ? 1 : 0;
		
		// Update the 'active' option in the 'value' column
		$valueColumnValue = $section->value;
		$valueColumnValue[$column] = $section->{$column};
		$section->value = $valueColumnValue;
		
		$section->save();
		
		return $this->responseSuccess($section, $column);
	}
}
