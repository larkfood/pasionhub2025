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
 * DB Errors Exception
 */

trait DBErrorsExceptionHandler
{
	/**
	 * Check if it is a DB connection exception
	 *
	 * DB Connection Error:
	 * http://dev.mysql.com/doc/refman/5.7/en/error-messages-server.html
	 *
	 * @param \Throwable $e
	 * @return bool
	 */
	protected function isDBErrorsException(\Throwable $e): bool
	{
		$databaseErrorCodes = [
			'mysql'        => ['1042', '1044', '1045', '1046', '1049'],
			'standardized' => ['08S01', '42000', '28000', '3D000', '42000', '42S22'],
		];
		
		return (
			$this->isPDOException($e)
			&& (
				in_array($e->getCode(), $databaseErrorCodes['mysql'])
				|| in_array($e->getCode(), $databaseErrorCodes['standardized'])
			)
		);
	}
	
	/**
	 * @param \Throwable $e
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
	 */
	protected function responseDBErrorsException(\Throwable $e, Request $request): Response|JsonResponse
	{
		return $this->responseCustomError($e, $request);
	}
}
