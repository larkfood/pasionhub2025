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

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

trait AuthenticationExceptionHandler
{
	/**
	 * @param \Throwable $e
	 * @return bool
	 */
	protected function isAuthenticationException(\Throwable $e): bool
	{
		return ($e instanceof AuthenticationException);
	}
	
	/**
	 * @param \Throwable $e
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
	 */
	protected function responseAuthenticationException(\Throwable $e, Request $request): Response|JsonResponse|RedirectResponse
	{
		$message = t('unauthenticated_or_token_expired');
		
		if (!isFromApi($request) && !isFromAjax($request)) {
			notification($message, 'error');
			
			return redirect()->guest(urlGen()->loginPath());
		}
		
		return $this->responseCustomError($e, $request, $message, Response::HTTP_UNAUTHORIZED);
	}
}
