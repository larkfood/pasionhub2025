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
use App\Http\Requests\Request;
use App\Jobs\GeneratePostThumbnails;
use App\Models\Picture;
use App\Models\Post;
use App\Models\Scopes\ReviewedScope;
use App\Models\Scopes\VerifiedScope;

trait SingleStepPictures
{
	/**
	 * @param $postId
	 * @param \App\Http\Requests\Request $request
	 * @return array
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	public function singleStepPicturesStore($postId, Request $request): array
	{
		$pictures = [];
		
		// Get pictures' uploaded files
		$files = (array)FileSys::getRequestedFile('pictures', $request);
		
		// If files not found again, return an empty array
		if (empty($files)) {
			return $pictures;
		}
		
		// Get pictures' post
		$post = Post::query()
			->withoutGlobalScopes([VerifiedScope::class, ReviewedScope::class])
			->where('id', $postId)
			->first();
		
		if (empty($post)) {
			return $pictures;
		}
		
		// Save all pictures
		$i = 0;
		foreach ($files as $key => $file) {
			if (empty($file)) {
				continue;
			}
			
			$picturePosition = $i;
			if (in_array($request->method(), ['PUT', 'PATCH', 'UPDATE'])) {
				// Delete old file if new file has uploaded
				// Check if current Listing have a pictures
				$possiblePictures = Picture::query()->where('post_id', $post->id)->where('id', $key);
				if ($possiblePictures->count() > 0) {
					$picture = $possiblePictures->first();
					$picturePosition = $picture->position;
					$picture->delete();
				}
			}
			
			// Save Post's Picture in DB
			$picture = new Picture([
				'post_id'   => $post->id,
				'file_path' => null,
				'mime_type' => null,
				'position'  => $picturePosition,
			]);
			
			// Upload File
			$destPath = 'files/' . strtolower($post->country_code) . '/' . $post->id;
			$picture->file_path = Upload::image($destPath, $file, null, true);
			$picture->mime_type = FileSys::getMimeType($file);
			
			if (!empty($picture->file_path)) {
				$picture->save();
			}
			
			$pictures[] = $picture;
			
			$i++;
		}
		
		// Generate the listing's images thumbnails
		GeneratePostThumbnails::dispatchSync($post);
		
		return $pictures;
	}
}
