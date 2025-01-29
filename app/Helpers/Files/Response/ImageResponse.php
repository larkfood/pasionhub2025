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

namespace App\Helpers\Files\Response;

use Illuminate\Filesystem\FilesystemAdapter;

class ImageResponse
{
	/**
	 * Create response for previewing specified image.
	 * Optionally resize image to specified size.
	 *
	 * @param $disk
	 * @param string|null $filePath
	 * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
	 */
	public static function create($disk, ?string $filePath)
	{
		if (!$disk instanceof FilesystemAdapter) {
			abort(500);
		}
		
		if (empty($filePath) || !$disk->exists($filePath)) {
			abort(404);
		}
		
		$mime = $disk->mimeType($filePath);
		$content = $disk->get($filePath);
		
		return response($content, 200)->header('Content-Type', $mime);
	}
}
