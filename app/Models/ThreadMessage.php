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

use App\Casts\DateTimeCast;
use App\Casts\NullableDateTimeCast;
use App\Helpers\Date;
use App\Http\Controllers\Web\Admin\Panel\Library\Traits\Models\Crud;
use App\Models\Thread\MessageTrait;
use App\Models\Traits\Common\AppendsTrait;
use App\Observers\ThreadMessageObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

#[ObservedBy([ThreadMessageObserver::class])]
class ThreadMessage extends BaseModel
{
	use SoftDeletes, Crud, AppendsTrait, Notifiable, MessageTrait, HasFactory;
	
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'threads_messages';
	
	/**
	 * @var array<int, string>
	 */
	protected $appends = ['created_at_formatted', 'p_recipient'];
	
	/**
	 * The attributes that aren't mass assignable.
	 *
	 * @var array<int, string>
	 */
	protected $guarded = ['id'];
	
	/**
	 * The relationships that should be touched on save.
	 *
	 * @var array<int, string>
	 */
	protected $touches = ['thread'];
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int, string>
	 */
	protected $fillable = [
		'thread_id',
		'user_id',
		'body',
		'file_path',
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
			'created_at' => DateTimeCast::class,
			'updated_at' => DateTimeCast::class,
			'deleted_at' => NullableDateTimeCast::class,
		];
	}
	
	/*
	|--------------------------------------------------------------------------
	| RELATIONS
	|--------------------------------------------------------------------------
	*/
	public function thread(): BelongsTo
	{
		return $this->belongsTo(Thread::class, 'thread_id', 'id');
	}
	
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'user_id');
	}
	
	public function participants(): HasMany
	{
		return $this->hasMany(ThreadParticipant::class, 'thread_id', 'thread_id');
	}
	
	/*
	|--------------------------------------------------------------------------
	| SCOPES
	|--------------------------------------------------------------------------
	*/
	public function scopeNotDeletedByUser(Builder $query, $userId): Builder
	{
		return $query->where(function (Builder $q) use ($userId) {
			$q->where('deleted_by', '!=', $userId)->orWhereNull('deleted_by');
		});
	}
	
	/**
	 * Returns unread messages given the userId.
	 *
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param int $userId
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeUnreadForUser(Builder $query, int $userId): Builder
	{
		$rawTable = $this->getConnection()->getTablePrefix() . $this->getTable();
		$rawCreatedAt = $this->getConnection()->raw($rawTable . '.created_at');
		
		return $query->has('thread')
			->where('user_id', '!=', $userId)
			->whereHas('participants', function (Builder $query) use ($userId, $rawCreatedAt) {
				$query->where('user_id', $userId)
					->where(function (Builder $q) use ($rawCreatedAt) {
						$q->where('last_read', '<', $rawCreatedAt)->orWhereNull('last_read');
					});
			});
	}
	
	/*
	|--------------------------------------------------------------------------
	| ACCESSORS | MUTATORS
	|--------------------------------------------------------------------------
	*/
	protected function createdAtFormatted(): Attribute
	{
		return Attribute::make(
			get: fn () => Date::format($this->created_at ?? null, 'datetime'),
		);
	}
	
	protected function pRecipient(): Attribute
	{
		return Attribute::make(
			get: function () {
				$userId = $this->user_id ?? 0;
				
				return $this->participants()->where('user_id', '!=', $userId)->first();
			},
		);
	}
	
	/*
	|--------------------------------------------------------------------------
	| OTHER PRIVATE METHODS
	|--------------------------------------------------------------------------
	*/
}
