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
use App\Helpers\Files\FileSys;
use App\Jobs\GenerateThumbnail;
use App\Models\Scopes\LocalizedScope;
use App\Models\Scopes\ActiveScope;
use App\Models\Traits\Common\AppendsTrait;
use App\Models\Traits\PictureTrait;
use App\Observers\PictureObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Http\Controllers\Web\Admin\Panel\Library\Traits\Models\Crud;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([PictureObserver::class])]
#[ScopedBy([ActiveScope::class, LocalizedScope::class])]
class Picture extends BaseModel
{
	use Crud, AppendsTrait, HasFactory;
	use PictureTrait;
	
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'pictures';
	
	/**
	 * @var array<int, string>
	 */
	protected $appends = [
		'file_url',
		'file_url_small',
		'file_url_medium',
		'file_url_large',
		'webp_file_url',
		'webp_file_url_small',
		'webp_file_url_medium',
		'webp_file_url_large',
	];
	
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
		'post_id',
		'file_path',
		'mime_type',
		'position',
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
			'created_at' => 'datetime',
			'updated_at' => 'datetime',
		];
	}
	
	/*
	|--------------------------------------------------------------------------
	| RELATIONS
	|--------------------------------------------------------------------------
	*/
	public function post(): BelongsTo
	{
		return $this->belongsTo(Post::class, 'post_id');
	}
	
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
	protected function filePath(): Attribute
	{
		return Attribute::make(
			get: function ($value, $attributes) {
				$value = !empty($value) ? $value : ($attributes['file_path'] ?? null);
				
				// OLD PATH
				$value = $this->getFileFromOldPath($value);
				
				// NEW PATH
				$disk = StorageDisk::getDisk();
				if (empty($value) || !$disk->exists($value)) {
					$value = config('larapen.media.picture');
				}
				
				return $value;
			},
			set: function ($value) {
				$resizeOptionsNames = ['picture-sm', 'picture-md', 'picture-lg'];
				$isWebpFormatEnabled = (config('settings.optimization.webp_format') == '1');
				foreach ($resizeOptionsNames as $resizeOptionsName) {
					// Generate the picture's thumbnails
					GenerateThumbnail::dispatchSync($value, false, $resizeOptionsName);
					
					// Generate the picture's WebP thumbnails
					if ($isWebpFormatEnabled) {
						GenerateThumbnail::dispatchSync($value, false, $resizeOptionsName, true);
					}
				}
				
				return $value;
			},
		);
	}
	
	protected function fileUrl(): Attribute
	{
		return Attribute::make(
			get: function () {
				return $this->getFileUrl();
			},
		);
	}
	
	protected function fileUrlSmall(): Attribute
	{
		return Attribute::make(
			get: function () {
				return $this->getFileUrl('picture-sm');
			},
		);
	}
	
	protected function fileUrlMedium(): Attribute
	{
		return Attribute::make(
			get: function () {
				return $this->getFileUrl('picture-md');
			},
		);
	}
	
	protected function fileUrlLarge(): Attribute
	{
		return Attribute::make(
			get: function () {
				return $this->getFileUrl('picture-lg');
			},
		);
	}
	
	/**
	 * Trigger for WebP file creation of "file_path"
	 *
	 * @return \Illuminate\Database\Eloquent\Casts\Attribute
	 */
	protected function webpFileUrl(): Attribute
	{
		return Attribute::make(
			get: function () {
				return $this->getFileUrl('picture-lg', true);
			},
		);
	}
	
	/**
	 * Trigger for WebP file creation of the "small" version of "file_path"
	 *
	 * @return \Illuminate\Database\Eloquent\Casts\Attribute
	 */
	protected function webpFileUrlSmall(): Attribute
	{
		return Attribute::make(
			get: function () {
				return $this->getFileUrl('picture-sm', true);
			},
		);
	}
	
	/**
	 * Trigger for WebP file creation of the "medium" version of "file_path"
	 *
	 * @return \Illuminate\Database\Eloquent\Casts\Attribute
	 */
	protected function webpFileUrlMedium(): Attribute
	{
		return Attribute::make(
			get: function () {
				return $this->getFileUrl('picture-md', true);
			},
		);
	}
	
	/**
	 * Trigger for WebP file creation of the "large" version of "file_path"
	 *
	 * @return \Illuminate\Database\Eloquent\Casts\Attribute
	 */
	protected function webpFileUrlLarge(): Attribute
	{
		return Attribute::make(
			get: function () {
				return $this->getFileUrl('picture-lg', true);
			},
		);
	}
	
	protected function mimeType(): Attribute
	{
		return Attribute::make(
			get: fn ($value) => $this->getMimeType($value),
			set: fn ($value) => $this->getMimeType($value)
		);
	}
	
	/*
	|--------------------------------------------------------------------------
	| OTHER PRIVATE METHODS
	|--------------------------------------------------------------------------
	*/
	private function getFileFromOldPath($value): ?string
	{
		// Fix path
		$oldBase = 'pictures/';
		$newBase = 'files/';
		if (str_contains($value, $oldBase)) {
			$value = $newBase . last(explode($oldBase, $value));
		}
		
		return $value;
	}
	
	private function getFileUrl($resizeOptionsName = null, bool $webpFormat = false): ?string
	{
		// Get original 'file_path'
		$filePath = $this->file_path ?? null;
		
		// WebP format
		if ($webpFormat) {
			// Don't generate the image WebP version if the option is not allowed
			$isWebpFormatEnabled = (config('settings.optimization.webp_format') == '1');
			if (!$isWebpFormatEnabled) return null;
			
			// Don't generate a WebP of WebP
			$extension = FileSys::getPathInfoExtension($filePath);
			if ($extension == 'webp') return null;
		}
		
		// Provide a fallback resize option (if needed)
		$resizeOptionsName = $resizeOptionsName ?? 'picture-lg';
		
		return thumbParam($filePath)->setOption($resizeOptionsName, $webpFormat)->url();
	}
	
	private function getMimeType($value): ?string
	{
		if (!empty($value)) return $value;
		
		return FileSys::getMimeType($this->file_path ?? null);
	}
}
