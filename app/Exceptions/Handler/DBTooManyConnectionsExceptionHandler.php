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
 * Too Many Connections Exception
 */

trait DBTooManyConnectionsExceptionHandler
{
	/**
	 * @param \Throwable $e
	 * @return bool
	 */
	protected function isDBTooManyConnectionsException(\Throwable $e): bool
	{
		$isMaxUserConnectionsException = (
			str_contains($e->getMessage(), 'max_user_connections')
			&& str_contains($e->getMessage(), 'active connections')
		);
		$isMaxConnectionsException = str_contains($e->getMessage(), 'max_connections');
		$isTooManyConnectionsException = str_contains($e->getMessage(), 'Too many connections');
		
		return (
			appInstallFilesExist()
			&& (
				$isMaxUserConnectionsException
				|| $isMaxConnectionsException
				|| $isTooManyConnectionsException
			)
		);
	}
	
	/**
	 * @param \Throwable $e
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
	 */
	protected function responseDBTooManyConnectionsException(\Throwable $e, Request $request): Response|JsonResponse
	{
		$message = $this->getDBTooManyConnectionsExceptionMessage($e, $request);
		
		return $this->responseCustomError($e, $request, $message);
	}
	
	// PRIVATE
	
	/**
	 * @param \Throwable $e
	 * @param \Illuminate\Http\Request $request
	 * @return string
	 */
	private function getDBTooManyConnectionsExceptionMessage(\Throwable $e, Request $request): string
	{
		$message = 'Too many connections. ' . "\n";
		$message .= 'We are experiencing a high volume of connections at the moment. ' . "\n";
		$message .= 'Please try again later. ' . "\n";
		$message .= 'We sincerely apologize for any inconvenience caused.' . "\n";
		
		return $message;
	}
}
