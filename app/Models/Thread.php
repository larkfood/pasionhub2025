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
use App\Models\Thread\ThreadTrait;
use App\Models\Traits\Common\AppendsTrait;
use App\Observers\ThreadObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

#[ObservedBy([ThreadObserver::class])]
class Thread extends BaseModel
{
	use SoftDeletes, Crud, AppendsTrait, Notifiable, ThreadTrait, HasFactory;
	
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'threads';
	
	/**
	 * @var array<int, string>
	 */
	protected $appends = ['created_at_formatted', 'p_is_unread', 'p_creator', 'p_is_important'];
	
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
		'subject',
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
	public function post(): BelongsTo
	{
		return $this->belongsTo(Post::class, 'post_id');
	}
	
	public function messages(): HasMany
	{
		return $this->hasMany(ThreadMessage::class, 'thread_id', 'id')->orderByDesc('id');
	}
	
	public function participants(): HasMany
	{
		return $this->hasMany(ThreadParticipant::class, 'thread_id', 'id');
	}
	
	public function users(): BelongsToMany
	{
		return $this->belongsToMany(User::class, (new ThreadParticipant)->getTable(), 'thread_id', 'user_id');
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
	 * Returns threads that the user is associated with.
	 *
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param $userId
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeForUser(Builder $query, $userId): Builder
	{
		$participantsTable = (new ThreadParticipant)->getTable();
		$threadsTable = $this->getTable();
		
		return $query->notDeletedByUser($userId)
			->join($participantsTable, $this->getQualifiedKeyName(), '=', $participantsTable . '.thread_id')
			->where($participantsTable . '.user_id', $userId)
			->whereNull($participantsTable . '.deleted_at')
			->select($threadsTable . '.*', $participantsTable . '.last_read', $participantsTable . '.is_important');
	}
	
	/**
	 * Returns threads with new messages that the user is associated with.
	 *
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param int $userId
	 * @return mixed
	 */
	public function scopeForUserWithNewMessages(Builder $query, int $userId)
	{
		$participantsTable = (new ThreadParticipant)->getTable();
		$threadsTable = $this->getTable();
		
		return $query->notDeletedByUser($userId)
			->join($participantsTable, $this->getQualifiedKeyName(), '=', $participantsTable . '.thread_id')
			->where($participantsTable . '.user_id', $userId)
			->whereNull($participantsTable . '.deleted_at')
			->where(function (Builder $query) use ($participantsTable, $threadsTable) {
				$query->where(
					$threadsTable . '.updated_at',
					'>',
					$this->getConnection()->raw($this->getConnection()->getTablePrefix() . $participantsTable . '.last_read')
				)->orWhereNull($participantsTable . '.last_read');
			})
			->select($threadsTable . '.*', $participantsTable . '.last_read', $participantsTable . '.is_important');
	}
	
	public function scopeWithoutTimestamps()
	{
		$this->timestamps = false;
		
		return $this;
	}
	
	/**
	 * Returns threads between given user ids.
	 *
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param array $participants
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeBetween(Builder $query, array $participants): Builder
	{
		return $query->whereHas('participants', function (Builder $q) use ($participants) {
			$q->whereIn('user_id', $participants)
				->select($this->getConnection()->raw('DISTINCT(thread_id)'))
				->groupBy('thread_id')
				->havingRaw('COUNT(thread_id)=' . count($participants));
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
	
	protected function pIsUnread(): Attribute
	{
		return Attribute::make(
			get: function () {
				if (!isset($this->updated_at) || !($this->updated_at instanceof Carbon)) {
					return false;
				}
				
				if (!isset($this->last_read) || $this->last_read === null) {
					return true;
				}
				
				try {
					if ($this->updated_at->gt($this->last_read)) {
						return true;
					}
				} catch (\Throwable $e) {
				}
				
				return false;
			},
		);
	}
	
	protected function pCreator(): Attribute
	{
		return Attribute::make(
			get: function () {
				$firstMessage = $this->messages()->withTrashed()->oldest()->first();
				
				return !empty($firstMessage) ? $firstMessage->user->toArray() : [];
			},
		);
	}
	
	
	protected function pIsImportant(): Attribute
	{
		return Attribute::make(
			get: function () {
				try {
					return (isset($this->is_important) && $this->is_important == 1);
				} catch (\Throwable $e) {
				}
				
				return false;
			},
		);
	}
	
	/*
	|--------------------------------------------------------------------------
	| OTHER PRIVATE METHODS
	|--------------------------------------------------------------------------
	*/
}
