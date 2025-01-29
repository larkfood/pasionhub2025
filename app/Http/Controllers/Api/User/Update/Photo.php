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

use App\Helpers\Files\Upload;
use App\Http\Resources\UserResource;
use App\Models\Scopes\VerifiedScope;
use App\Models\User;
use Illuminate\Http\Request;

trait Photo
{
	/**
	 * Update the user's photo
	 *
	 * @param $userId
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function updateUserPhoto($userId, Request $request): \Illuminate\Http\JsonResponse
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
		
		$file = $request->file('photo_path');
		if (empty($file)) {
			return apiResponse()->error('File is empty.');
		}
		
		// Upload & save the picture
		$param = [
			'destPath' => 'avatars/' . strtolower($user->country_code) . '/' . $user->id,
			'width'    => (int)config('larapen.media.resize.namedOptions.avatar.width', 800),
			'height'   => (int)config('larapen.media.resize.namedOptions.avatar.height', 800),
			'ratio'    => config('larapen.media.resize.namedOptions.avatar.ratio', '1'),
			'upsize'   => config('larapen.media.resize.namedOptions.avatar.upsize', '0'),
		];
		try {
			$user->photo_path = Upload::image($param['destPath'], $file, $param);
		} catch (\Throwable $e) {
			return apiResponse()->error($e->getMessage());
		}
		$user->save();
		
		// Result data
		$data = [
			'success' => true,
			'message' => t('Your photo or avatar have been updated'),
			'result'  => (new UserResource($user))->toArray($request),
		];
		
		$extra = [];
		if (doesRequestIsFromWebClient()) {
			// Get the FileInput plugin's data
			$fileInput = [];
			$fileInput['initialPreview'] = [];
			$fileInput['initialPreviewConfig'] = [];
			
			if (!empty($user->photo_path) && isset($this->disk)) {
				$photoUrl = $user->photo_url;
				$deleteUrl = url('account/photo/delete');
				
				try {
					$fileSize = $this->disk->exists($user->photo_path)
						? (int)$this->disk->size($user->photo_path)
						: 0;
				} catch (\Throwable $e) {
					$fileSize = 0;
				}
				
				// Extra Fields for AJAX file removal (related to the $initialPreviewConfigUrl)
				$initialPreviewConfigExtra = [
					'_token'  => csrf_token(),
					'_method' => 'PUT',
				];
				
				// Build Bootstrap-FileInput plugin's parameters
				$fileInput['initialPreview'][] = $photoUrl;
				$fileInput['initialPreviewConfig'][] = [
					'key'     => $user->id,
					'caption' => basename($user->photo_path),
					'size'    => $fileSize,
					'url'     => $deleteUrl,
					'extra'   => $initialPreviewConfigExtra,
				];
			}
			$extra['fileInput'] = $fileInput;
		}
		
		$data['extra'] = $extra;
		
		return apiResponse()->json($data);
	}
	
	/**
	 * Remove the user's photo
	 *
	 * @param $userId
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function removeUserPhoto($userId, Request $request): \Illuminate\Http\JsonResponse
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
		
		// Remove all the current user's photos, by removing his photos' directory
		$destinationPath = substr($user->photo_path, 0, strrpos($user->photo_path, '/'));
		if (!empty($destinationPath) && $this->disk->exists($destinationPath)) {
			$this->disk->deleteDirectory($destinationPath);
		}
		
		// Delete the photo path from DB
		$user->photo_path = null;
		$user->save();
		
		// Result data
		$data = [
			'success' => true,
			'message' => t('Your photo or avatar has been deleted'),
			'result'  => (new UserResource($user))->toArray($request),
		];
		
		return apiResponse()->json($data);
	}
}
