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
use App\Models\Scopes\CompatibleApiScope;
use App\Models\Traits\Common\AppendsTrait;
use App\Models\Traits\PaymentMethodTrait;
use App\Observers\PaymentMethodObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Http\Controllers\Web\Admin\Panel\Library\Traits\Models\Crud;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([PaymentMethodObserver::class])]
#[ScopedBy([ActiveScope::class, CompatibleApiScope::class])]
class PaymentMethod extends BaseModel
{
	use Crud, AppendsTrait, HasFactory;
	use PaymentMethodTrait;
	
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'payment_methods';
	
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
	protected $guarded = ['id'];
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int, string>
	 */
	protected $fillable = [
		'id',
		'name',
		'display_name',
		'description',
		'has_ccbox',
		'is_compatible_api',
		'countries',
		'active',
		'lft',
		'rgt',
		'depth',
		'parent_id',
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
	public function payment(): HasMany
	{
		return $this->hasMany(Payment::class, 'payment_method_id');
	}
	
	/*
	|--------------------------------------------------------------------------
	| SCOPES
	|--------------------------------------------------------------------------
	*/
	public function scopeActive(Builder $builder): Builder
	{
		return $builder->where('active', 1);
	}
	
	/*
	|--------------------------------------------------------------------------
	| ACCESSORS | MUTATORS
	|--------------------------------------------------------------------------
	*/
	protected function description(): Attribute
	{
		return Attribute::make(
			get: function ($value) {
				if (isset($this->name) && $this->name == 'offlinepayment') {
					if (mb_strlen(trans('offlinepayment::messages.payment_method_description')) > 0) {
						$value = trans('offlinepayment::messages.payment_method_description');
					}
				}
				
				return $value;
			},
		);
	}
	
	protected function countries(): Attribute
	{
		return Attribute::make(
			get: function ($value) {
				$value = str_replace(',', ', ', strtoupper($value));
				
				return strtoupper($value);
			},
			set: function ($value) {
				// Get the MySQL right value
				$value = preg_replace('/(,|;)\s*/', ',', $value);
				$value = strtolower($value);
				
				// Check if the entry is removed
				if (empty($value) || $value == strtolower(trans('admin.All'))) {
					$value = null;
				}
				
				return $value;
			},
		);
	}
	
	/*
	|--------------------------------------------------------------------------
	| OTHER PRIVATE METHODS
	|--------------------------------------------------------------------------
	*/
}
