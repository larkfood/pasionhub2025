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

namespace App\Services\Search\Traits;

use App\Services\Search\Traits\Relations\CategoryRelation;
use App\Services\Search\Traits\Relations\PaymentRelation;

trait Relations
{
	use CategoryRelation, PaymentRelation;
	
	protected function setRelations(): void
	{
		if (!isset($this->posts)) {
			abort(500, 'Fatal Error: Search relations cannot be applied.');
		}
		
		// category
		$this->setCategoryRelation();
		
		// payment
		$this->setPaymentRelation();
		
		// city
		$this->posts->has('city');
		if (!config('settings.listings_list.hide_location')) {
			$this->posts->with('city');
		}
		
		// pictures
		$this->posts->with(['picture', 'pictures']);
		
		// user
		$this->posts->with([
			'user',
			'user.permissions',
			'user.roles',
		]);
		
		// savedByLoggedUser
		$this->posts->with('savedByLoggedUser');
	}
}
