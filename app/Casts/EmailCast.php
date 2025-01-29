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

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class EmailCast implements CastsAttributes
{
	/**
	 * Cast the given value.
	 *
	 * @param \Illuminate\Database\Eloquent\Model $model
	 * @param string $key
	 * @param mixed $value
	 * @param array<string, mixed> $attributes
	 * @return string|null
	 */
	public function get(Model $model, string $key, mixed $value, array $attributes): ?string
	{
		if ($model->getTable() == 'posts') {
			if (!$model->relationLoaded('user')) {
				return getAsStringOrNull($value);
			}
		}
		
		if (isAdminPanel() && isDemoDomain()) {
			$isPostOrPutMethod = (in_array(strtolower(request()->method()), ['post', 'put']));
			$isNotFromAuthForm = (!in_array(request()->segment(2), ['password', 'login']));
			
			$guard = getAuthGuard();
			if (auth($guard)->check()) {
				if (isset($model->phone_token)) {
					if ($model->phone_token == 'demoFaker') {
						return getAsStringOrNull($value);
					}
				}
				if (!$isPostOrPutMethod && $isNotFromAuthForm) {
					if (!isDemoEmailAddress($value)) {
						$value = emailPrefixMask($value);
					}
				}
			}
		}
		
		return getAsStringOrNull($value);
	}
	
	/**
	 * Prepare the given value for storage.
	 *
	 * @param \Illuminate\Database\Eloquent\Model $model
	 * @param string $key
	 * @param mixed $value
	 * @param array<string, mixed> $attributes
	 * @return mixed|null
	 */
	public function set(Model $model, string $key, mixed $value, array $attributes): mixed
	{
		return $value;
	}
}
