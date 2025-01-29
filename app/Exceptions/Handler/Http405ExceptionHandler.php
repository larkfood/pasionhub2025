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
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

/*
 * HTTP Method Not Allowed Exception
 */

trait Http405ExceptionHandler
{
	/**
	 * Check if it is an HTTP Method Not Allowed exception
	 *
	 * @param \Throwable $e
	 * @return bool
	 */
	protected function isHttp405Exception(\Throwable $e): bool
	{
		return (
			$e instanceof MethodNotAllowedHttpException
			|| (
				$this->isHttpException($e)
				&& method_exists($e, 'getStatusCode')
				&& $e->getStatusCode() == 405
			)
		);
	}
	
	/**
	 * @param \Throwable $e
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
	 */
	protected function responseHttp405Exception(\Throwable $e, Request $request): Response|JsonResponse
	{
		$message = $this->getHttp405ExceptionMessage($e, $request);
		
		return $this->responseCustomError($e, $request, $message, 405);
	}
	
	// PRIVATE
	
	/**
	 * @param \Throwable $e
	 * @param \Illuminate\Http\Request $request
	 * @return string
	 */
	private function getHttp405ExceptionMessage(\Throwable $e, Request $request): string
	{
		$message = "Whoops! Seems you use a bad request method. Please try again.";
		
		if (!isFromApi($request)) {
			$backLink = ' <a href="' . url()->previous() . '">' . t('Back') . '</a>';
			$message = $message . $backLink;
		}
		
		return $message;
	}
}
