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

trait AutoFocus
{
	public $autoFocusOnFirstField = true;
	
	/**
	 * @return bool
	 */
	public function getAutoFocusOnFirstField(): bool
	{
		return $this->autoFocusOnFirstField;
	}
	
	/**
	 * @param $value
	 * @return bool
	 */
	public function setAutoFocusOnFirstField($value): bool
	{
		return $this->autoFocusOnFirstField = (bool)$value;
	}
	
	/**
	 * @return bool
	 */
	public function enableAutoFocus(): bool
	{
		return $this->setAutoFocusOnFirstField(true);
	}
	
	/**
	 * @return bool
	 */
	public function disableAutoFocus(): bool
	{
		return $this->setAutoFocusOnFirstField(false);
	}
}
