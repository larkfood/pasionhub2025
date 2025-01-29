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

namespace App\Models\Post;

trait ReviewsPlugin
{
	private string $postHelperClass = '\extras\plugins\reviews\app\Helpers\Post';
	
	/*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
	public function recalculateRating(): void
	{
		if (config('plugins.reviews.installed')) {
			if (class_exists($this->postHelperClass)) {
				$this->postHelperClass::recalculateRating($this);
			}
		}
	}
	
	public function userRating()
	{
		if (config('plugins.reviews.installed')) {
			if (class_exists($this->postHelperClass)) {
				return $this->postHelperClass::getUserRating($this);
			}
		}
		
		return null;
	}
	
	public function countUserRatings()
	{
		if (config('plugins.reviews.installed')) {
			if (class_exists($this->postHelperClass)) {
				return $this->postHelperClass::getCountUserRatings($this);
			}
		}
		
		return null;
	}
	
	/*
	|--------------------------------------------------------------------------
	| RELATIONS
	|--------------------------------------------------------------------------
	*/
	public function reviews()
	{
		if (config('plugins.reviews.installed')) {
			if (class_exists($this->postHelperClass)) {
				return $this->postHelperClass::reviews($this);
			}
		}
		
		return $this;
	}
	
	/*
	|--------------------------------------------------------------------------
	| ACCESSORS | MUTATORS
	|--------------------------------------------------------------------------
	*/
}
