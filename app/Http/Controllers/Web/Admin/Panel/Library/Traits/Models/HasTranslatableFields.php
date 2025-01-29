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

/*
|--------------------------------------------------------------------------
| Methods for working with translatable models.
|--------------------------------------------------------------------------
*/
trait HasTranslatableFields
{
	/**
	 * Get the attributes that were casted in the model.
	 * Used for translations because Spatie/Laravel-Translatable
	 * overwrites the getCasts() method.
	 *
	 * @return self
	 */
	public function getCastedAttributes()
	{
		return parent::getCasts();
	}
	
	/**
	 * Check if a model is translatable.
	 * All translation adaptors must have the translationEnabledForModel() method.
	 *
	 * @return bool
	 */
	public function translationEnabled()
	{
		if (method_exists($this, 'translationEnabledForModel')) {
			return $this->translationEnabledForModel();
		}
		
		return false;
	}
}
