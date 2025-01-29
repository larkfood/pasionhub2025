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
 * DB Tables & Columns Errors Exception
 */

trait DBTableExceptionHandler
{
	/**
	 * Check if it is a DB table error exception
	 *
	 * DB Connection Error:
	 * http://dev.mysql.com/doc/refman/5.7/en/error-messages-server.html
	 *
	 * @param \Throwable $e
	 * @return bool
	 */
	protected function isDBTableException(\Throwable $e): bool
	{
		$tableErrorCodes = [
			'mysql'        => ['1051', '1109', '1146'],
			'standardized' => ['42S02'],
		];
		
		return (
			$this->isPDOException($e)
			&& (
				in_array($e->getCode(), $tableErrorCodes['mysql'])
				|| in_array($e->getCode(), $tableErrorCodes['standardized'])
			)
		);
	}
	
	/**
	 * @param \Throwable $e
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
	 */
	protected function responseDBTableException(\Throwable $e, Request $request): Response|JsonResponse
	{
		$message = $this->getDBTableExceptionMessage($e, $request);
		
		return $this->responseCustomError($e, $request, $message);
	}
	
	// PRIVATE
	
	/**
	 * @param \Throwable $e
	 * @param \Illuminate\Http\Request $request
	 * @return string
	 */
	private function getDBTableExceptionMessage(\Throwable $e, Request $request): string
	{
		$message = 'Some tables of the database are absent.' . "\n";
		$message .= $e->getMessage() . "\n";
		$message .= '1/ Remove all tables from the database (if existing)' . "\n";
		$message .= '2/ Delete the <code>/.env</code> file (required before re-installation)' . "\n";
		$message .= '3/ and reload this page -or- go to install URL: <a href="' . url('install') . '">' . url('install') . '</a>.' . "\n";
		$message .= 'BE CAREFUL: If your site is already in production, you will lose all your data in both cases.' . "\n";
		
		return $message;
	}
}
