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
use App\Casts\EmailCast;
use App\Casts\NullableDateTimeCast;
use App\Casts\PhoneCast;
use App\Casts\PhoneCountryCast;
use App\Casts\PhoneNationalCast;
use App\Helpers\Date;
use App\Http\Controllers\Web\Admin\Panel\Library\Traits\Models\Crud;
use App\Jobs\GenerateThumbnail;
use App\Models\Scopes\LocalizedScope;
use App\Models\Scopes\StrictActiveScope;
use App\Models\Scopes\ValidPeriodScope;
use App\Models\Traits\Common\AppendsTrait;
use App\Models\Traits\Common\HasCountryCodeColumn;
use App\Models\Traits\UserTrait;
use App\Notifications\ResetPasswordNotification;
use App\Observers\UserObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[ObservedBy([UserObserver::class])]
#[ScopedBy([LocalizedScope::class])]
class User extends BaseUser
{
	use Crud, AppendsTrait, HasRoles, HasCountryCodeColumn, HasApiTokens, Notifiable, HasFactory;
	use UserTrait;
	
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'users';
	
	/**
	 * @var array<int, string>
	 */
	protected $appends = [
		'phone_intl',
		'created_at_formatted',
		'photo_url',
		'original_updated_at',
		'original_last_activity',
		'p_is_online',
		'country_flag_url',
		'remaining_posts',
	];
	
	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var boolean
	 */
	public $timestamps = true;
	
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
		'country_code',
		'language_code',
		'user_type_id',
		'gender_id',
		'name',
		'photo_path',
		'about',
		'auth_field',
		'email',
		'phone',
		'phone_national',
		'phone_country',
		'phone_hidden',
		'username',
		'password',
		'remember_token',
		'can_be_impersonate',
		'disable_comments',
		'create_from_ip',
		'latest_update_ip',
		'provider',
		'provider_id',
		'email_token',
		'phone_token',
		'email_verified_at',
		'phone_verified_at',
		'accept_terms',
		'accept_marketing_offers',
		'dark_mode',
		'time_zone',
		'featured',
		'blocked',
		'closed',
		'last_activity',
	];
	
	/**
	 * The attributes that should be hidden for arrays
	 *
	 * @var array<int, string>
	 */
	protected $hidden = ['password', 'remember_token'];
	
	/**
	 * @param array $attributes
	 */
	public function __construct(array $attributes = [])
	{
		if (isFromInstallOrUpgradeProcess() || isAdminPanel()) {
			$this->fillable[] = 'is_admin';
		}
		
		parent::__construct($attributes);
	}
	
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
			'email'             => EmailCast::class,
			'phone_country'     => PhoneCountryCast::class,
			'phone'             => PhoneCast::class,
			'phone_national'    => PhoneNationalCast::class,
			'email_verified_at' => NullableDateTimeCast::class,
			'phone_verified_at' => NullableDateTimeCast::class,
			'created_at'        => DateTimeCast::class,
			'updated_at'        => DateTimeCast::class,
			'deleted_at'        => NullableDateTimeCast::class,
			'last_activity'     => NullableDateTimeCast::class,
			'last_login_at'     => NullableDateTimeCast::class,
		];
	}
	
	public function routeNotificationForMail()
	{
		return $this->email;
	}
	
	public function routeNotificationForVonage()
	{
		$phone = phoneE164($this->phone, $this->phone_country);
		
		return setPhoneSign($phone, 'vonage');
	}
	
	public function routeNotificationForTwilio()
	{
		$phone = phoneE164($this->phone, $this->phone_country);
		
		return setPhoneSign($phone, 'twilio');
	}
	
	/**
	 * Send the password reset notification.
	 * Note: Overrides the Laravel official version
	 *
	 * @param string $token
	 * @return void
	 */
	public function sendPasswordResetNotification($token)
	{
		// Get the right auth field
		$authField = request()->filled('auth_field') ? request()->input('auth_field') : null;
		$authField = (empty($authField)) ? ($this->auth_field ?? null) : $authField;
		$authField = (empty($authField) && request()->filled('email')) ? 'email' : $authField;
		$authField = (empty($authField) && request()->filled('phone')) ? 'phone' : $authField;
		$authField = (empty($authField)) ? getAuthField() : $authField;
		
		// Send the reset password notification
		try {
			$this->notify(new ResetPasswordNotification($this, $token, $authField));
		} catch (\Throwable $e) {
			if (!isFromApi()) {
				flash($e->getMessage())->error();
			} else {
				abort(500, $e->getMessage());
			}
		}
	}
	
	/*
	|--------------------------------------------------------------------------
	| RELATIONS
	|--------------------------------------------------------------------------
	*/
	public function posts(): HasMany
	{
		return $this->hasMany(Post::class, 'user_id')->orderByDesc('created_at');
	}
	
	public function postsInCountry()
	{
		return $this->hasMany(Post::class, 'user_id')->inCountry()->orderByDesc('created_at');
	}
	
	public function receivedThreads(): HasManyThrough
	{
		return $this->hasManyThrough(
			Thread::class,
			Post::class,
			'user_id', // Foreign key on the Listing table...
			'post_id', // Foreign key on the Thread table...
			'id',      // Local key on the User table...
			'id'       // Local key on the Listing table...
		);
	}
	
	public function threads(): HasManyThrough
	{
		return $this->hasManyThrough(
			Thread::class,
			ThreadMessage::class,
			'user_id', // Foreign key on the ThreadMessage table...
			'post_id', // Foreign key on the Thread table...
			'id',      // Local key on the User table...
			'id'       // Local key on the ThreadMessage table...
		);
	}
	
	public function savedPosts(): BelongsToMany
	{
		return $this->belongsToMany(Post::class, 'saved_posts', 'user_id', 'post_id');
	}
	
	public function savedSearch(): HasMany
	{
		return $this->hasMany(SavedSearch::class, 'user_id');
	}
	
	/*
	 * The first valid payment (Covers the validity period).
	 * Its activation will be checked programmably.
	 * NOTE: By sorting the ID by ASC, allows the system to use the first valid payment as the current one.
	 */
	public function possiblePayment(): MorphOne
	{
		return $this->morphOne(Payment::class, 'payable')->withoutGlobalScope(StrictActiveScope::class)->orderBy('id');
	}
	
	/*
	 * The first valid & active subscription (Covers the validity period & is active)
	 * NOTE: By sorting the ID by ASC, allows the system to use the first valid payment as the current one.
	 */
	public function payment(): MorphOne
	{
		return $this->morphOne(Payment::class, 'payable')->orderBy('id');
	}
	
	/*
	 * The first valid & active subscription that is manually created
	 * NOTE: Used in the UsersPurge command in cron job
	 */
	public function subscriptionNotManuallyCreated(): MorphOne
	{
		return $this->morphOne(Payment::class, 'payable')->notManuallyCreated()->orderBy('id');
	}
	
	/*
	 * The ending later valid (or on hold) active payment (Covers the validity period & is active)
	 * This is useful to calculate the starting period to allow payable to have multiple valid & active payments
	 */
	public function paymentEndingLater(): MorphOne
	{
		return $this->morphOne(Payment::class, 'payable')
			->withoutGlobalScope(ValidPeriodScope::class)
			->where(function ($q) {
				$q->where(fn ($q) => $q->valid())->orWhere(fn ($q) => $q->onHold());
			})
			->orderByDesc('period_end');
	}
	
	/*
	 * Get all the user subscriptions (payments)
	 */
	public function subscriptions(): MorphMany
	{
		return $this->morphMany(Payment::class, 'payable');
	}
	
	/*
	|--------------------------------------------------------------------------
	| SCOPES
	|--------------------------------------------------------------------------
	*/
	public function scopeVerified(Builder $builder): Builder
	{
		$builder->where(function (Builder $query) {
			$query->whereNotNull('email_verified_at')->whereNotNull('phone_verified_at');
		});
		
		return $builder;
	}
	
	public function scopeUnverified(Builder $builder): Builder
	{
		$builder->where(function (Builder $query) {
			$query->whereNull('email_verified_at')->orWhereNull('phone_verified_at');
		});
		
		return $builder;
	}
	
	/*
	|--------------------------------------------------------------------------
	| ACCESSORS | MUTATORS
	|--------------------------------------------------------------------------
	*/
	protected function originalUpdatedAt(): Attribute
	{
		return Attribute::make(
			get: fn () => $this->getRawOriginal('updated_at'),
		);
	}
	
	protected function originalLastActivity(): Attribute
	{
		return Attribute::make(
			get: fn () => $this->getRawOriginal('last_activity'),
		);
	}
	
	protected function createdAtFormatted(): Attribute
	{
		return Attribute::make(
			get: fn () => Date::customFromNow($this->created_at ?? null),
		);
	}
	
	protected function photoPath(): Attribute
	{
		return Attribute::make(
			set: function ($value, $attributes) {
				if (!is_string($value)) {
					return $value;
				}
				
				if ($value == url('/')) {
					return null;
				}
				
				// Retrieve current value without upload a new file
				if (str_starts_with($value, config('larapen.media.picture'))) {
					return null;
				}
				
				if (!str_starts_with($value, 'avatars/')) {
					$userId = $this->id ?? $attributes['id'] ?? null;
					$countryCode = $this->country_code ?? $attributes['country_code'] ?? null;
					
					if (empty($userId) || empty($countryCode)) {
						return null;
					}
					
					$destPath = 'avatars/' . strtolower($countryCode) . '/' . $userId;
					$value = $destPath . last(explode($destPath, $value));
				}
				
				// Generate the user's photo thumbnails
				$filePathFallback = config('larapen.media.avatar');
				GenerateThumbnail::dispatchSync($value, $filePathFallback, 'avatar');
				
				return $value;
			},
		);
	}
	
	protected function photoUrl(): Attribute
	{
		return Attribute::make(
			get: function () {
				$filePath = $this->photo_path ?? null;
				$filePathFallback = config('larapen.media.avatar');
				$resizeOptionsName = 'avatar';
				
				// Add the user's photo thumbnails generation in queue
				GenerateThumbnail::dispatch($filePath, $filePathFallback, $resizeOptionsName);
				
				return thumbParam($filePath, $filePathFallback)->setOption($resizeOptionsName)->url();
			},
		);
	}
	
	protected function phoneIntl(): Attribute
	{
		return Attribute::make(
			get: function () {
				$phoneCountry = $this->phone_country ?? config('country.code');
				$phone = $this->phone ?? null;
				
				$value = !empty($this->phone_national) ? $this->phone_national : $phone;
				
				if ($phoneCountry == config('country.code')) {
					return phoneNational($value, $phoneCountry);
				}
				
				return phoneIntl($value, $phoneCountry);
			},
		);
	}
	
	protected function name(): Attribute
	{
		return Attribute::make(
			get: fn ($value) => mb_ucwords($value),
		);
	}
	
	protected function pIsOnline(): Attribute
	{
		return Attribute::make(
			get: function () {
				if (!isset($this->last_activity) || !$this->last_activity instanceof Carbon) {
					return false;
				}
				
				$timeAgoFromNow = now(Date::getAppTimeZone())->subMinutes(5);
				$isOnline = (
					!empty($this->getRawOriginal('last_activity'))
					&& $this->last_activity->gt($timeAgoFromNow)
				);
				
				// Allow only logged users to get the other users status
				$guard = getAuthGuard();
				
				return auth($guard)->check() ? $isOnline : false;
			},
		);
	}
	
	protected function countryFlagUrl(): Attribute
	{
		return Attribute::make(
			get: fn () => getCountryFlagUrl($this->country_code ?? null),
		);
	}
	
	/*
	 * Remaining Posts for the User (Without to apply the current subscription)
	 * - Need to use User::with(['posts' => fn ($q) => $q->withoutGlobalScopes($postScopes)->unarchived()]),
	 *   to retrieve it like this: $user->remaining_posts
	 * - The Post Remaining for the User current subscription can be got by using:
	 *   User::with('payment', fn ($q) => $q->with(['posts' => fn ($q) => $q->withoutGlobalScopes($postScopes)->unarchived()]))
	 *   and retrieve it like this: $user->payment->remaining_posts
	 */
	protected function remainingPosts(): Attribute
	{
		return Attribute::make(
			get: function () {
				// If the relation is not loaded through the Eloquent 'with()' method,
				// then don't make an additional query (to prevent performance issues).
				if (!$this->relationLoaded('posts')) {
					return null;
				}
				
				if (!isset($this->posts)) {
					return null;
				}
				
				$postsLimit = (int)config('settings.listing_form.listings_limit');
				try {
					$countPosts = $this->posts->count();
				} catch (\Throwable $e) {
					$countPosts = 0;
				}
				$remainingPosts = ($postsLimit >= $countPosts) ? $postsLimit - $countPosts : 0;
				
				return (int)$remainingPosts;
			},
		);
	}
	
	/*
	|--------------------------------------------------------------------------
	| OTHER PRIVATE METHODS
	|--------------------------------------------------------------------------
	*/
}
