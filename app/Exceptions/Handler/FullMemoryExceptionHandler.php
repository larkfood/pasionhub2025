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

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/*
 * Memory is full exception
 * Note: Called only when reporting some Laravel error traces
 */

trait FullMemoryExceptionHandler
{
	/**
	 * @param \Throwable $e
	 * @return bool
	 */
	protected function isFullMemoryException(\Throwable $e): bool
	{
		return (
			str_contains($e->getMessage(), 'Allowed memory size of')
			&& str_contains($e->getMessage(), 'tried to allocate')
		);
	}
	
	/**
	 * @param \Throwable $e
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
	 */
	protected function responseFullMemoryException(\Throwable $e, Request $request): Response|JsonResponse
	{
		$message = $this->getFullMemoryExceptionMessage($e, $request);
		
		return $this->responseCustomError($e, $request, $message);
	}
	
	// PRIVATE
	
	/**
	 * @param \Throwable $e
	 * @param \Illuminate\Http\Request $request
	 * @return string
	 */
	private function getFullMemoryExceptionMessage(\Throwable $e, Request $request): string
	{
		// Memory is full
		$message = $e->getMessage() . ". \n";
		$message .= 'The server\'s memory must be increased so that it can support the load of the requested resource.';
		
		return $message;
	}
}
