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

namespace App\Helpers\Response;

use Illuminate\Http\JsonResponse;

Class Ajax
{
	/**
	 * @param array|null $data
	 * @param int $status
	 * @param array $headers
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function json(?array $data = [], int $status = 200, array $headers = []): JsonResponse
	{
		$data = is_array($data) ? $data : [];
		
		$headers = addContentTypeHeader('application/json', $headers);
		$status = getAsInt($status);
		$status = isValidHttpStatus($status) ? $status : 200;
		$statusText = getHttpStatusMessage($status);
		
		return response()
			->json($data, $status, $headers, JSON_UNESCAPED_UNICODE)
			->setStatusCode($status, $statusText);
	}
	
	/**
	 * @param string|null $content
	 * @param int $status
	 * @param array $headers
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
	 */
	public function text(?string $content = '', int $status = 200, array $headers = [])
	{
		$content = is_string($content) ? $content : '';
		
		$headers = addContentTypeHeader('text/plain', $headers);
		$status = getAsInt($status);
		$status = isValidHttpStatus($status) ? $status : 200;
		$statusText = getHttpStatusMessage($status);
		
		return response($content, $status)
			->withHeaders($headers)
			->setStatusCode($status, $statusText);
	}
}
