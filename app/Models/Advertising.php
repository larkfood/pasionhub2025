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

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Traits\Common\AppendsTrait;
use App\Observers\AdvertisingObserver;
use App\Http\Controllers\Web\Admin\Panel\Library\Traits\Models\Crud;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;

#[ObservedBy([AdvertisingObserver::class])]
#[ScopedBy([ActiveScope::class])]
class Advertising extends BaseModel
{
	use Crud, AppendsTrait;
	
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'advertising';
	
	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var boolean
	 */
	public $timestamps = false;
	
	/**
	 * The attributes that aren't mass assignable.
	 *
	 * @var array<int, string>
	 */
	protected $guarded = ['id', 'integration', 'slug'];
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int, string>
	 */
	protected $fillable = [
		'is_responsive',
		'provider_name',
		'description',
		'tracking_code_large',
		'tracking_code_medium',
		'tracking_code_small',
		'active',
	];
	
	/*
	|--------------------------------------------------------------------------
	| FUNCTIONS
	|--------------------------------------------------------------------------
	*/
	
	/*
	|--------------------------------------------------------------------------
	| RELATIONS
	|--------------------------------------------------------------------------
	*/
	
	/*
	|--------------------------------------------------------------------------
	| SCOPES
	|--------------------------------------------------------------------------
	*/
	
	/*
	|--------------------------------------------------------------------------
	| ACCESSORS | MUTATORS
	|--------------------------------------------------------------------------
	*/
	protected function trackingCodeLarge(): Attribute
	{
		return Attribute::make(
			get: fn ($value) => $this->checkAndTransformCode($value),
		);
	}
	
	protected function trackingCodeMedium(): Attribute
	{
		return Attribute::make(
			get: fn ($value) => $this->checkAndTransformCode($value),
		);
	}
	
	protected function trackingCodeSmall(): Attribute
	{
		return Attribute::make(
			get: fn ($value) => $this->checkAndTransformCode($value),
		);
	}
	
	/*
	|--------------------------------------------------------------------------
	| OTHER PRIVATE METHODS
	|--------------------------------------------------------------------------
	*/
	private function checkAndTransformCode($value)
	{
		// If the code is from Google Adsense
		if (str_contains($value, 'adsbygoogle.js')) {
			$patten = '/class="adsbygoogle"/ui';
			$replace = 'class="adsbygoogle ads-slot-responsive"';
			$value = preg_replace($patten, $replace, $value);
			
			$value = preg_replace('/data-ad-format="[^"]*"/ui', '', $value);
		}
		
		return $value;
	}
}
