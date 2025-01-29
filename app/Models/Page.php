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
use App\Models\Traits\Common\AppendsTrait;
use App\Models\Traits\PageTrait;
use App\Observers\PageObserver;
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

#[ObservedBy([PageObserver::class])]
#[ScopedBy([ActiveScope::class])]
class Page extends BaseModel
{
	use Crud, AppendsTrait, HasFactory, Sluggable, SluggableScopeHelpers, HasTranslations;
	use PageTrait;
	
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'pages';
	
	/**
	 * @var array<int, string>
	 */
	protected $appends = ['image_url'];
	
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
		'type',
		'name',
		'slug',
		'image_path',
		'title',
		'content',
		'external_link',
		'name_color',
		'title_color',
		'target_blank',
		'seo_title',
		'seo_description',
		'seo_keywords',
		'excluded_from_footer',
		'active',
		'lft',
		'rgt',
		'depth',
	];
	
	/**
	 * @var array<int, string>
	 */
	public array $translatable = ['name', 'title', 'content', 'seo_title', 'seo_description', 'seo_keywords'];
	
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
			'created_at' => 'datetime',
			'updated_at' => 'datetime',
		];
	}
	
	/*
	|--------------------------------------------------------------------------
	| RELATIONS
	|--------------------------------------------------------------------------
	*/
	public function parent(): BelongsTo
	{
		return $this->belongsTo(Page::class, 'parent_id');
	}
	
	/*
	|--------------------------------------------------------------------------
	| SCOPES
	|--------------------------------------------------------------------------
	*/
	public function scopeType(Builder $builder, $type): Builder
	{
		return $builder->where('type', $type)->orderByDesc('id');
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
	
	protected function title(): Attribute
	{
		return Attribute::make(
			get: function ($value) {
				if (isset($this->attributes['title']) && !isJson($this->attributes['title'])) {
					return $this->attributes['title'];
				}
				
				return $value;
			},
			set: function ($value) {
				$name = $this->name ?? null;
				
				return empty($value) ? $name : $value;
			},
		);
	}
	
	protected function content(): Attribute
	{
		return Attribute::make(
			get: function ($value) {
				if (isset($this->attributes['content']) && !isJson($this->attributes['content'])) {
					return $this->attributes['content'];
				}
				
				return $value;
			},
		);
	}
	
	protected function imagePath(): Attribute
	{
		return Attribute::make(
			get: function ($value, $attributes) {
				if (empty($value)) {
					$value = $attributes['image_path'] ?? null;
				}
				
				if (empty($value)) {
					return null;
				}
				
				$disk = StorageDisk::getDisk();
				if (!$disk->exists($value)) {
					$value = null;
				}
				
				return $value;
			},
			set: function ($value) {
				// Generate the page's image thumbnails
				GenerateThumbnail::dispatchSync($value, false, 'bg-header');
				
				return $value;
			},
		);
	}
	
	protected function imageUrl(): Attribute
	{
		return Attribute::make(
			get: function () {
				$filePath = $this->image_path ?? null;
				$resizeOptionsName = 'bg-header';
				
				// Add the page's image thumbnails generation in queue
				GenerateThumbnail::dispatch($filePath, false, $resizeOptionsName);
				
				return thumbParam($filePath, false)->setOption($resizeOptionsName)->url();
			},
		);
	}
	
	/*
	|--------------------------------------------------------------------------
	| OTHER PRIVATE METHODS
	|--------------------------------------------------------------------------
	*/
}
