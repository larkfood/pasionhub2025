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

namespace App\Http\Controllers\Api\User\Update;

use App\Http\Resources\UserResource;
use App\Models\Scopes\VerifiedScope;
use App\Models\User;
use Illuminate\Http\Request;

trait DarkMode
{
	/**
	 * Update the user's dark mode option
	 *
	 * @param $userId
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function setDarkMode($userId, Request $request): \Illuminate\Http\JsonResponse
	{
		$user = User::withoutGlobalScopes([VerifiedScope::class])->where('id', $userId)->first();
		
		if (empty($user)) {
			return apiResponse()->notFound(t('user_not_found'));
		}
		
		$authUser = auth('sanctum')->user();
		if (empty($authUser)) {
			return apiResponse()->unauthorized();
		}
		
		// Check logged User
		if ($authUser->getAuthIdentifier() != $user->id) {
			return apiResponse()->unauthorized();
		}
		
		// Set the dark mode in the DB
		$user->dark_mode = $request->input('dark_mode');
		$user->save();
		
		// Result data
		$data = [
			'success' => true,
			'message' => ($user->dark_mode == 1) ? t('dark_mode_is_set') : t('dark_mode_is_disabled'),
			'result'  => (new UserResource($user))->toArray($request),
		];
		
		return apiResponse()->json($data);
	}
}
