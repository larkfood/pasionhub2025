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

trait CityTrait
{
	// ===| ADMIN PANEL METHODS |===
	
	public function getAdmin2Html()
	{
		return (!empty($this->subAdmin2))
			? $this->subAdmin2->name
			: ($this->subadmin2_code ?? null);
	}
	
	public function getAdmin1Html()
	{
		return (!empty($this->subAdmin1))
			? $this->subAdmin1->name
			: ($this->subadmin1_code ?? null);
	}
	
	// ===| OTHER METHODS |===
}
