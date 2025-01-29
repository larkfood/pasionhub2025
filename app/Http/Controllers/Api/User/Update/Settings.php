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

use App\Http\Requests\Front\UserSettingsRequest;
use App\Http\Resources\UserResource;
use App\Models\Scopes\VerifiedScope;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

trait Settings
{
	/**
	 * Update the user's settings
	 *
	 * @param $id
	 * @param \App\Http\Requests\Front\UserSettingsRequest $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function updateUserSettings($id, UserSettingsRequest $request): \Illuminate\Http\JsonResponse
	{
		$user = User::withoutGlobalScopes([VerifiedScope::class])->where('id', $id)->first();
		
		if (empty($user)) {
			return apiResponse()->notFound(t('user_not_found'));
		}
		
		$authUser = request()->user() ?? auth('sanctum')->user();
		if (empty($authUser)) {
			return apiResponse()->unauthorized();
		}
		
		// Check logged User
		// Get the User Personal Access Token Object
		$personalAccess = $authUser->tokens()->where('id', getApiAuthToken())->first();
		if (!empty($personalAccess)) {
			if ($personalAccess->tokenable_id != $user->id) {
				return apiResponse()->unauthorized();
			}
		} else {
			return apiResponse()->unauthorized();
		}
		
		// Update User
		$input = $request->only($user->getFillable());
		
		$protectedColumns = ['password'];
		
		foreach ($input as $key => $value) {
			if ($request->has($key)) {
				if (in_array($key, $protectedColumns) && empty($value)) {
					continue;
				}
				
				$user->{$key} = $value;
			}
		}
		
		// Checkboxes
		$user->disable_comments = (int)$request->input('disable_comments');
		$user->accept_marketing_offers = (int)$request->input('accept_marketing_offers');
		if ($request->filled('accept_terms')) {
			$user->accept_terms = (int)$request->input('accept_terms');
		}
		
		// Other fields
		if ($request->filled('password')) {
			if (isset($input['password'])) {
				$user->password = Hash::make($input['password']);
			}
		}
		
		// Save
		$user->save();
		
		$data = [
			'success' => true,
			'message' => t('account_settings_has_updated_successfully'),
			'result'  => (new UserResource($user))->toArray($request),
		];
		
		return apiResponse()->updated($data);
	}
}
