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
use App\Jobs\GenerateThumbnail;
use App\Models\Scopes\ActiveScope;
use App\Models\Traits\CategoryTrait;
use App\Models\Traits\Common\AppendsTrait;
use App\Observers\CategoryObserver;
use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Http\Controllers\Web\Admin\Panel\Library\Traits\Models\Crud;
use App\Http\Controllers\Web\Admin\Panel\Library\Traits\Models\SpatieTranslatable\HasTranslations;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([CategoryObserver::class])]
#[ScopedBy([ActiveScope::class])]
class Category extends BaseModel
{
	use Crud, AppendsTrait, HasFactory, Sluggable, SluggableScopeHelpers, HasTranslations;
	use CategoryTrait;
	
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'categories';
	
	/**
	 * @var array<int, string>
	 */
	protected $appends = ['image_url'];
	
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
		'parent_id',
		'name',
		'slug',
		'description',
		'hide_description',
		'image_path',
		'icon_class',
		'seo_title',
		'seo_description',
		'seo_keywords',
		'lft',
		'rgt',
		'depth',
		'type',
		'is_for_permanent',
		'active',
	];
	
	/**
	 * @var array<int, string>
	 */
	public array $translatable = ['name', 'description', 'seo_title', 'seo_description', 'seo_keywords'];
	
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
	public function posts(): HasMany
	{
		$hasMany = $this->hasMany(Post::class, 'category_id');
		if (!isAdminPanel()) {
			$hasMany->where('country_code', '=', config('country.code'));
		}
		
		return $hasMany;
	}
	
	public function children(): HasMany
	{
		return $this->hasMany(Category::class, 'parent_id')
			->with('children')
			->orderBy('lft');
	}
	
	public function childrenClosure(): HasMany
	{
		return $this->hasMany(Category::class, 'parent_id')
			->orderBy('lft');
	}
	
	public function parent(): BelongsTo
	{
		return $this->belongsTo(Category::class, 'parent_id')
			->with('parent');
	}
	
	public function parentClosure(): BelongsTo
	{
		return $this->belongsTo(Category::class, 'parent_id');
	}
	
	public function fields(): BelongsToMany
	{
		return $this->belongsToMany(Field::class, 'category_field', 'category_id', 'field_id');
	}
	
	/*
	|--------------------------------------------------------------------------
	| SCOPES
	|--------------------------------------------------------------------------
	*/
	// root()
	public function scopeRoot(Builder $builder)
	{
		return $builder->columnIsEmpty('parent_id');
	}
	
	// childrenOf()
	public function scopeChildrenOf(Builder $builder, $parentId): Builder
	{
		return $builder->where('parent_id', $parentId);
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
				if (isset($this->attributes['name']) && !isJson($this->attributes['name'])) {
					return $this->attributes['name'];
				}
				
				return $value;
			},
		);
	}
	
	protected function iconClass(): Attribute
	{
		return Attribute::make(
			get: function ($value) {
				$defaultIconClass = 'fa-solid fa-folder';
				
				if (empty($value)) {
					return $defaultIconClass;
				}
				
				$defaultFontIconSet = config('larapen.core.defaultFontIconSet', 'bootstrap');
				
				// This part will be removed at: 2022-10-14
				$filePath = config('larapen.core.fontIconSet.' . $defaultFontIconSet . '.path');
				$buffer = file_get_contents($filePath);
				
				$ifVersion = config('larapen.core.fontIconSet.' . $defaultFontIconSet . '.version');
				$ifVersion = str_replace('.', '\.', $ifVersion);
				
				$matches = [];
				preg_match('#version:[^\']+\'' . $ifVersion . '\',[^i]+icons:[^\[]*\[([^]]+)]#s', $buffer, $matches);
				$iClasses = $matches[1] ?? '';
				$iClasses = str_replace("'", '', $iClasses);
				$iClasses = preg_replace('#[\n\t]*#', '', $iClasses);
				
				$iClassesArray = array_map('trim', explode(',', $iClasses));
				
				if (!empty($iClassesArray)) {
					if (!in_array($value, $iClassesArray)) {
						return $defaultIconClass;
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
				if (isset($this->attributes['description']) && !isJson($this->attributes['description'])) {
					return $this->attributes['description'];
				}
				
				return $value;
			},
		);
	}
	
	protected function type(): Attribute
	{
		return Attribute::make(
			get: function ($value) {
				if (empty($value)) {
					if (
						isset($this->parent)
						&& $this->parent->type
						&& !empty($this->parent->type)
					) {
						$value = $this->parent->type;
					}
					if (empty($value)) {
						$value = 'classified';
					}
				}
				
				return $value;
			},
		);
	}
	
	protected function imagePath(): Attribute
	{
		return Attribute::make(
			get: fn ($value, $attributes) => $this->getImage($value, $attributes),
			set: function ($value) {
				// Generate the category's image thumbnails
				GenerateThumbnail::dispatchSync($value, false, 'cat');
				
				return $value;
			},
		);
	}
	
	protected function imageUrl(): Attribute
	{
		return Attribute::make(
			get: function () {
				return thumbService($this->image_path ?? null)->resize('cat')->url();
			},
		);
	}
	
	/*
	|--------------------------------------------------------------------------
	| OTHER PRIVATE METHODS
	|--------------------------------------------------------------------------
	*/
	private function getImage($value, $attributes)
	{
		// OLD PATH
		$oldValue = $this->getImageFromOriginPath($value);
		if (!empty($oldValue)) {
			return $oldValue;
		}
		
		// NEW PATH
		if (empty($value)) {
			$value = $attributes['image_path'] ?? null;
		}
		
		$disk = StorageDisk::getDisk();
		
		$defaultIcon = 'app/default/categories/fa-folder-default.png';
		$skin = getFrontSkin(request()->input('skin'));
		$defaultSkinnedIcon = 'app/default/categories/fa-folder-' . $skin . '.png';
		
		// File path is empty
		if (empty($value)) {
			if ($disk->exists($defaultSkinnedIcon)) {
				return $defaultSkinnedIcon;
			}
			
			return $defaultIcon;
		}
		
		// File not found
		if (!$disk->exists($value)) {
			if ($disk->exists($defaultSkinnedIcon)) {
				return $defaultSkinnedIcon;
			}
			
			return $defaultIcon;
		}
		
		// If the Category contains a skinnable icon,
		// Change it by the selected skin icon.
		if (str_contains($value, 'app/categories/') && !str_contains($value, '/custom/')) {
			$pattern = '/app\/categories\/[^\/]+\//iu';
			$replacement = 'app/categories/' . $skin . '/';
			$value = preg_replace($pattern, $replacement, $value);
		}
		
		// (Optional)
		// If the Category contains a skinnable default icon,
		// Change it by the selected skin default icon.
		if (str_contains($value, 'app/default/categories/fa-folder-')) {
			$pattern = '/app\/default\/categories\/fa-folder-[^\.]+\./iu';
			$replacement = 'app/default/categories/fa-folder-' . $skin . '.';
			$value = preg_replace($pattern, $replacement, $value);
		}
		
		if (!$disk->exists($value)) {
			if ($disk->exists($defaultSkinnedIcon)) {
				return $defaultSkinnedIcon;
			}
			
			return $defaultIcon;
		}
		
		return $value;
	}
	
	/**
	 * Category icons images from original version
	 * Only the file name is set in Category 'image_path' field
	 * Example: fa-car.png
	 *
	 * @param $value
	 * @return string|null
	 */
	private function getImageFromOriginPath($value): ?string
	{
		// Fix path
		$skin = config('settings.style.skin', 'default');
		$value = 'app/categories/' . $skin . '/' . $value;
		
		$disk = StorageDisk::getDisk();
		if (!$disk->exists($value)) {
			return null;
		}
		
		return $value;
	}
}
