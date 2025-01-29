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

use App\Helpers\DBTool;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

trait DBConnectionFailedExceptionHandler
{
	private ?string $connectionErrorMessage = null;
	
	/**
	 * Test Database Connection
	 *
	 * @return bool
	 */
	private function isDBConnectionFailedException(): bool
	{
		$pdo = null;
		
		try {
			$pdo = DBTool::getPdoConnection();
		} catch (\Throwable $e) {
			$this->connectionErrorMessage = $e->getMessage();
		}
		
		return (appInstallFilesExist() && !($pdo instanceof \PDO));
	}
	
	/**
	 * @param \Throwable $e
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
	 */
	protected function responseDBConnectionFailedException(\Throwable $e, Request $request): Response|JsonResponse
	{
		$message = $this->getDBConnectionFailedExceptionMessage($e, $request);
		
		return $this->responseCustomError($e, $request, $message);
	}
	
	// PRIVATE
	
	/**
	 * @param \Throwable $e
	 * @param \Illuminate\Http\Request $request
	 * @return string
	 */
	private function getDBConnectionFailedExceptionMessage(\Throwable $e, Request $request): string
	{
		$message = $this->connectionErrorMessage;
		if (empty($message)) {
			$message = 'Connection to the database failed.';
		}
		
		$exceptionMessage = $e->getMessage();
		if (!empty($exceptionMessage)) {
			$message .= " \n" . $exceptionMessage;
		}
		
		return $message;
	}
}
