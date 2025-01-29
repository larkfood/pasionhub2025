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
 * DB Collation Error Exception
 */

trait DBCollationErrorExceptionHandler
{
	/**
	 * Check if it is a DB collation error exception
	 *
	 * @param \Throwable $e
	 * @return bool
	 */
	protected function isDBCollationErrorException(\Throwable $e): bool
	{
		$message = mb_strtolower($e->getMessage());
		
		return (
			$this->isPDOException($e)
			&& str_contains($message, 'collation')
		);
	}
	
	/**
	 * @param \Throwable $e
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
	 */
	protected function responseDBCollationErrorException(\Throwable $e, Request $request): Response|JsonResponse
	{
		$message = $this->getDBCollationErrorExceptionMessage($e, $request);
		
		return $this->responseCustomError($e, $request, $message);
	}
	
	// PRIVATE
	
	/**
	 * @param \Throwable $e
	 * @param \Illuminate\Http\Request $request
	 * @return string
	 */
	private function getDBCollationErrorExceptionMessage(\Throwable $e, Request $request): string
	{
		$message = $e->getMessage();
		if (isFromAjax($request)) {
			$message = $this->parseDBCollationErrorExceptionMessage($message);
		}
		
		$message = $message . ".";
		$message .= "\n\n";
		$message .= '<br>';
		$message .= '<div class="text-start">';
		$message .= 'The database server <strong>character set</strong> and <strong>collation</strong> are not properly configured.';
		$message .= '<br> ';
		$message .= 'Please visit the "Admin panel → System Info" for more information.';
		$message .= '</div>';
		
		return $message;
	}
	
	/**
	 * @param string $message
	 * @return string
	 */
	private function parseDBCollationErrorExceptionMessage(string $message): string
	{
		/*
		 * SQLSTATE\[\d+\]: Matches SQLSTATE followed by a number inside square brackets.
		 * :(.*?): Captures everything after the colon, which represents the error message.
		 * (?= \(Connection: mysql): A lookahead that ensures we stop capturing before the phrase (Connection: mysql).
		 */
		$pattern = '/SQLSTATE\[\d+\]:(.*?)(?= \(Connection: mysql)/';
		$matches = [];
		preg_match($pattern, $message, $matches);
		if (isset($matches[1])) {
			$message = trim($matches[1]);
		}
		
		return $message;
	}
}
