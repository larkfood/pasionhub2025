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

namespace App\Http\Controllers\Api\Picture;

use App\Helpers\Files\FileSys;
use App\Helpers\Files\Upload;
use App\Http\Requests\Front\PhotoRequest;
use App\Http\Resources\PictureResource;
use App\Http\Resources\PostResource;
use App\Jobs\GeneratePostThumbnails;
use App\Models\Picture;
use App\Models\Post;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\ReviewedScope;
use App\Models\Scopes\VerifiedScope;

trait MultiStepsPictures
{
	/**
	 * Store Pictures (from Multi Steps Form)
	 *
	 * @param \App\Http\Requests\Front\PhotoRequest $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function multiStepsPicturesStore(PhotoRequest $request): \Illuminate\Http\JsonResponse
	{
		// Get customized request variables
		$countryCode = $request->input('country_code', config('country.code'));
		$countPackages = $request->integer('count_packages');
		$countPaymentMethods = $request->integer('count_payment_methods');
		$postId = $request->input('post_id');
		
		$authUser = null;
		if (auth('sanctum')->check()) {
			$authUser = auth('sanctum')->user();
		}
		
		$post = null;
		if (!empty($authUser) && !empty($postId)) {
			$post = Post::query()
				->withoutGlobalScopes([VerifiedScope::class, ReviewedScope::class])
				->inCountry($countryCode)
				->where('user_id', $authUser->getAuthIdentifier())
				->where('id', $postId)
				->first();
		}
		
		if (empty($post)) {
			return apiResponse()->notFound(t('post_not_found'));
		}
		
		$pictures = Picture::where('post_id', $post->id);
		
		// Get default/global pictures limit
		$defaultPicturesLimit = (int)config('settings.listing_form.pictures_limit', 5);
		if ($post->featured == 1 && !empty($post->payment)) {
			if (isset($post->payment->package) && !empty($post->payment->package)) {
				if (!empty($post->payment->package->pictures_limit)) {
					$defaultPicturesLimit = $post->payment->package->pictures_limit;
				}
			}
		}
		
		// Get picture limit
		$countExistingPictures = $pictures->count();
		$picturesLimit = $defaultPicturesLimit - $countExistingPictures;
		
		if ($picturesLimit > 0) {
			// Get pictures initial position
			$latestPosition = $pictures->orderByDesc('position')->first();
			$initialPosition = (!empty($latestPosition) && (int)$latestPosition->position > 0) ? (int)$latestPosition->position : 0;
			$initialPosition = ($countExistingPictures >= $initialPosition) ? $countExistingPictures : $initialPosition;
			
			// Get pictures' uploaded files
			$files = (array)FileSys::getRequestedFile('pictures', $request);
			
			// Save all pictures
			$pictures = [];
			if (!empty($files)) {
				foreach ($files as $key => $file) {
					if (empty($file)) {
						continue;
					}
					
					// Delete old file if new file has uploaded
					// Check if current Listing has a pictures
					$picturePosition = $initialPosition + (int)$key + 1;
					$picture = Picture::query()
						->where('post_id', $post->id)
						->where('id', $key)
						->first();
					if (!empty($picture)) {
						$picturePosition = $picture->position;
						$picture->delete();
					}
					
					// Post Picture in the database
					$picture = new Picture([
						'post_id'   => $post->id,
						'file_path' => null,
						'mime_type' => null,
						'position'  => $picturePosition,
					]);
					
					// Upload File
					try {
						$destPath = 'files/' . strtolower($post->country_code) . '/' . $post->id;
						$picture->file_path = Upload::image($destPath, $file, null, true);
						$picture->mime_type = FileSys::getMimeType($file);
					} catch (\Throwable $e) {
						$data = [
							'success' => false,
							'message' => $e->getMessage(),
							'result'  => null,
						];
						
						return apiResponse()->json($data);
					}
					
					if (!empty($picture->file_path)) {
						$picture->save();
					}
					
					$pictures[] = (new PictureResource($picture));
					
					// Check the pictures limit
					if ($key >= ($picturesLimit - 1)) {
						break;
					}
				}
			}
			
			if (!empty($pictures)) {
				$data = [
					'success' => true,
					'message' => t('The pictures have been updated'),
					'result'  => $pictures,
				];
			} else {
				$data = [
					'success' => false,
					'message' => t('error_found'),
					'result'  => null,
				];
			}
		} else {
			$pictures = [];
			$data = [
				'success' => false,
				'message' => t('pictures_limit_reached'),
				'result'  => null,
			];
		}
		
		// Generate the listing's images thumbnails
		GeneratePostThumbnails::dispatchSync($post);
		
		$extra = [];
		
		$extra['post']['result'] = (new PostResource($post))->toArray($request);
		
		// User should he go on Payment page or not?
		$shouldHeGoOnPaymentPage = (
			is_numeric($countPackages)
			&& is_numeric($countPaymentMethods)
			&& $countPackages > 0
			&& $countPaymentMethods > 0
		);
		if ($shouldHeGoOnPaymentPage) {
			$extra['steps']['payment'] = true;
			$extra['nextStepLabel'] = t('Next');
		} else {
			$extra['steps']['payment'] = false;
			$extra['nextStepLabel'] = t('Done');
		}
		
		if (doesRequestIsFromWebClient()) {
			// Get the FileInput plugin's data
			$fileInput = [];
			$fileInput['initialPreview'] = [];
			$fileInput['initialPreviewConfig'] = [];
			
			$pictures = collect($pictures);
			if ($pictures->count() > 0 && isset($this->disk)) {
				foreach ($pictures as $picture) {
					if (empty($picture->file_path)) {
						continue;
					}
					
					$pictureUrl = $picture->file_url_medium;
					$deleteUrl = url('posts/' . $post->id . '/photos/' . $picture->id . '/delete');
					
					try {
						$fileSize = $this->disk->exists($picture->file_path)
							? (int)$this->disk->size($picture->file_path)
							: 0;
					} catch (\Throwable $e) {
						$fileSize = 0;
					}
					
					// Build Bootstrap-FileInput plugin's parameters
					$fileInput['initialPreview'][] = $pictureUrl;
					$fileInput['initialPreviewConfig'][] = [
						'key'     => $picture->id,
						'caption' => basename($picture->file_path),
						'size'    => $fileSize,
						'url'     => $deleteUrl,
						'extra'   => ['id' => $picture->id],
					];
				}
			}
			$extra['fileInput'] = $fileInput;
		}
		
		$data['extra'] = $extra;
		
		return apiResponse()->json($data);
	}
	
	/**
	 * Delete a Picture (from Multi Steps Form)
	 *
	 * @param $pictureId
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function deleteMultiStepsPicture($pictureId): \Illuminate\Http\JsonResponse
	{
		// Get customized request variables
		$postId = request()->input('post_id');
		
		$authUser = null;
		if (auth('sanctum')->check()) {
			$authUser = auth('sanctum')->user();
		}
		
		// Get Post
		$post = null;
		if (!empty($authUser) && !empty($postId)) {
			$post = Post::query()
				->withoutGlobalScopes([VerifiedScope::class, ReviewedScope::class])
				->where('user_id', $authUser->getAuthIdentifier())
				->where('id', $postId)
				->first();
		}
		
		if (empty($post)) {
			return apiResponse()->notFound(t('post_not_found'));
		}
		
		$pictures = Picture::query()->withoutGlobalScopes([ActiveScope::class])->where('post_id', $postId);
		
		if ($pictures->count() <= 0) {
			return apiResponse()->forbidden();
		}
		
		if ($pictures->count() == 1) {
			if (config('settings.listing_form.picture_mandatory')) {
				return apiResponse()->forbidden(t('the_latest_picture_removal_text'));
			}
		}
		
		$pictures = $pictures->get();
		foreach ($pictures as $picture) {
			if ($picture->id == $pictureId) {
				$res = $picture->delete();
				break;
			}
		}
		
		$message = t('The picture has been deleted');
		
		return apiResponse()->success($message);
	}
	
	/**
	 * Reorder Pictures - Bulk Update
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function reorderMultiStepsPictures(): \Illuminate\Http\JsonResponse
	{
		// Get customized request variables
		$postId = request()->input('post_id');
		
		if (request()->header('X-Action') != 'bulk') {
			return apiResponse()->unauthorized();
		}
		
		$bodyJson = request()->input('body');
		if (!isJson($bodyJson)) {
			return apiResponse()->error('Invalid JSON format for the "body" field.');
		}
		
		$bodyArray = json_decode($bodyJson);
		if (!is_array($bodyArray) || empty($bodyArray)) {
			return apiResponse()->noContent();
		}
		
		$authUser = null;
		if (auth('sanctum')->check()) {
			$authUser = auth('sanctum')->user();
		}
		
		$pictures = [];
		foreach ($bodyArray as $item) {
			if (!isset($item->id) || !isset($item->position)) {
				continue;
			}
			if (empty($item->id) || !is_numeric($item->position)) {
				continue;
			}
			
			$picture = null;
			if (!empty($authUser) && !empty($postId)) {
				$picture = Picture::where('id', $item->id)
					->whereHas('post', fn ($query) => $query->where('user_id', $authUser->id))
					->first();
			}
			
			if (!empty($picture)) {
				$picture->position = $item->position;
				$picture->save();
				
				$pictures[] = (new PictureResource($picture));
			}
		}
		
		// Get endpoint output data
		$data = [
			'success' => !empty($pictures),
			'message' => !empty($pictures) ? t('Your picture has been reorder successfully') : null,
			'result'  => !empty($pictures) ? $pictures : null,
		];
		
		return apiResponse()->json($data);
	}
}
