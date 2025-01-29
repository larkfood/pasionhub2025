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

use App\Helpers\Files\Storage\StorageDisk;
use App\Http\Controllers\Web\Admin\Panel\Library\Traits\Models\Crud;
use App\Models\Traits\Common\AppendsTrait;
use App\Models\Traits\SettingTrait;
use App\Observers\SettingObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;

#[ObservedBy([SettingObserver::class])]
class Setting extends BaseModel
{
	use Crud, AppendsTrait;
	use SettingTrait;
	
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'settings';
	
	/**
	 * @var array<int, string>
	 */
	protected $fakeColumns = ['value'];
	
	/**
	 * The primary key for the model.
	 *
	 * @var string
	 */
	protected $primaryKey = 'id';
	
	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var boolean
	 */
	public $timestamps = false;
	
	/**
	 * The primary key for the model.
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
		'key',
		'name',
		'field',
		'value',
		'description',
		'parent_id',
		'lft',
		'rgt',
		'depth',
		'active',
	];
	
	/*
	|--------------------------------------------------------------------------
	| FUNCTIONS
	|--------------------------------------------------------------------------
	*/
	/**
	 * Get the attributes that should be cast.
	 *
	 * @return array<string, string>
	 */
	protected function casts(): array
	{
		return [
			'value' => 'array',
		];
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
	protected function name(): Attribute
	{
		return Attribute::make(
			get: function ($value) {
				if (isset($this->key)) {
					$transKey = 'settings.' . $this->key;
					
					if (trans()->has($transKey)) {
						$value = trans($transKey);
					}
				}
				
				return $value;
			},
		);
	}
	
	protected function description(): Attribute
	{
		return Attribute::make(
			get: function ($value) {
				if (isset($this->key)) {
					$transKey = 'settings.description_' . $this->key;
					
					if (trans()->has($transKey)) {
						$value = trans($transKey);
					}
					
					if (empty($value)) {
						$value = $this->name ?? $this->key;
					}
				}
				
				return $value;
			},
		);
	}
	
	protected function field(): Attribute
	{
		return Attribute::make(
			get: function ($value) {
				$diskName = StorageDisk::getDiskName();
				
				// Get 'field' field value
				$value = jsonToArray($value);
				
				$breadcrumb = trans('admin.Admin panel') . ' &rarr; '
					. mb_ucwords(trans('admin.settings')) . ' &rarr; '
					. mb_ucwords(trans('admin.general_settings')) . ' &rarr; ';
				
				$name = $this->name ?? 'Options';
				$description = mb_ucfirst(trans('settings.setting')) . ': ' . $name;
				$description = $this->description ?? $description;
				$title = !empty($description) ? $description : $name;
				
				$formTitle = [
					[
						'name'  => 'group_title',
						'type'  => 'custom_html',
						'value' => '<h2 class="setting-group-name">' . $title . '</h2>',
					],
					[
						'name'  => 'group_breadcrumb',
						'type'  => 'custom_html',
						'value' => '<p class="setting-group-breadcrumb">' . $breadcrumb . $name . '</p>',
					],
				];
				
				// Handle 'field' field value
				// Get the right Setting
				$settingClass = $this->getSettingClass();
				if (class_exists($settingClass)) {
					if (method_exists($settingClass, 'getFields')) {
						$value = $settingClass::getFields($diskName);
					}
				}
				
				return array_merge($formTitle, $value);
			},
		);
	}
	
	protected function value(): Attribute
	{
		return Attribute::make(
			get: fn ($value) => $this->getValue($value),
			set: fn ($value) => $this->setValue($value),
		);
	}
	
	/*
	|--------------------------------------------------------------------------
	| OTHER PRIVATE METHODS
	|--------------------------------------------------------------------------
	*/
	private function getValue($value)
	{
		// IMPORTANT
		// The line below means that the all Storage providers need to be load before the AppServiceProvider,
		// to prevent all errors during the retrieving of the settings in the AppServiceProvider.
		$disk = StorageDisk::getDisk();
		
		// Get 'value' field value
		$value = jsonToArray($value);
		
		// Handle 'value' field value
		// Get the right Setting
		$settingClass = $this->getSettingClass();
		if (class_exists($settingClass)) {
			if (method_exists($settingClass, 'getValues')) {
				$value = $settingClass::getValues($value, $disk);
			}
		}
		
		// Demo: Secure some Data (Applied for all Entries)
		if (isAdminPanel() && isDemoDomain()) {
			$isPostOrPutMethod = (in_array(strtolower(request()->method()), ['post', 'put']));
			$isNotFromAuthForm = (!in_array(request()->segment(2), ['password', 'login']));
			$value = collect($value)
				->mapWithKeys(function ($v, $k) use ($isPostOrPutMethod, $isNotFromAuthForm) {
					$isOptionNeedToBeHidden = (
						!$isPostOrPutMethod
						&& $isNotFromAuthForm
						&& in_array($k, self::optionsThatNeedToBeHidden())
					);
					if ($isOptionNeedToBeHidden) {
						$v = '************************';
					}
					
					return [$k => $v];
				})->toArray();
		}
		
		return $value;
	}
	
	private function setValue($value)
	{
		// Get value
		$value = jsonToArray($value);
		
		// Handle 'value' field value
		// Get the right Setting
		$settingClass = $this->getSettingClass();
		if (class_exists($settingClass)) {
			if (method_exists($settingClass, 'setValues')) {
				$value = $settingClass::setValues($value, $this);
			}
		}
		
		// Make sure that setting array contains only string, numeric or null elements
		$value = settingArrayElements($value);
		
		return !empty($value) ? json_encode($value) : null;
	}
	
	/**
	 * Get the right Setting class
	 *
	 * @return string
	 */
	private function getSettingClass(): string
	{
		$classKey = $this->key ?? '';
		
		// Get class name
		$className = str($classKey)->camel()->ucfirst()->append('Setting');
		
		// Get class full qualified name (i.e. with namespace)
		$namespace = '\App\Models\Setting\\';
		$class = $className->prepend($namespace)->toString();
		
		// If the class doesn't exist in the core app, try to get it from add-ons
		if (!class_exists($class)) {
			$namespace = plugin_namespace($classKey) . '\app\Models\Setting\\';
			$class = $className->prepend($namespace)->toString();
		}
		
		return $class;
	}
}
