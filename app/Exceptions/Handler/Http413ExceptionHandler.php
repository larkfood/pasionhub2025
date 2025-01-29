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

namespace App\Exceptions\Handler;

use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/*
 * Post Too Large Exception
 */

trait Http413ExceptionHandler
{
	/**
	 * Check it is a 'Post Too Large' exception
	 *
	 * @param \Throwable $e
	 * @return bool
	 */
	protected function isHttp413Exception(\Throwable $e): bool
	{
		return (
			$e instanceof PostTooLargeException
			|| (
				$this->isHttpException($e)
				&& method_exists($e, 'getStatusCode')
				&& $e->getStatusCode() == Response::HTTP_REQUEST_ENTITY_TOO_LARGE
			));
	}
	
	/**
	 * @param \Throwable $e
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
	 */
	protected function responseHttp413Exception(\Throwable $e, Request $request): Response|JsonResponse
	{
		$message = $this->getHttp413ExceptionMessage($e, $request);
		
		return $this->responseCustomError($e, $request, $message, Response::HTTP_REQUEST_ENTITY_TOO_LARGE);
	}
	
	// PRIVATE
	
	/**
	 * @param \Throwable $e
	 * @param \Illuminate\Http\Request $request
	 * @return string
	 */
	private function getHttp413ExceptionMessage(\Throwable $e, Request $request): string
	{
		$message = 'Maximum data (including files to upload) size to post and memory usage are limited on the server.';
		$message = 'Payload Too Large. ' . $message;
		
		if (!isFromApi($request)) {
			$backLink = ' <a href="' . url()->previous() . '">' . t('Back') . '</a>';
			$message = $message . $backLink;
		}
		
		return $message;
	}
}
