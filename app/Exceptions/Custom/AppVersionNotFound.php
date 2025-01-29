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

namespace App\Exceptions\Custom;

use App\Exceptions\Handler\Traits\ExceptionTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class AppVersionNotFound extends Exception
{
	use ExceptionTrait;
	
	/**
	 * Report the exception.
	 */
	public function report(): void
	{
		Log::warning($this->getMessage());
	}
	
	/**
	 * Render the exception into an HTTP response.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|false
	 */
	public function render(Request $request): Response|JsonResponse|false
	{
		$message = $this->getMessage();
		$status = 428; // Precondition Required
		
		if (isFromApi($request) || isFromAjax($request)) {
			$data = [
				'success'   => false,
				'message'   => strip_tags($message),
				'exception' => $this,
			];
			
			return apiResponse()->json($data, $status);
		}
		
		$message = "<strong style='color:red;'>ERROR:</strong> " . $message . "\n\n";
		$message .= "<strong style='color:green;'>SOLUTION:</strong>" . "\n";
		$message .= "1. You have to add in the '/.env' file a line like: <code>APP_VERSION=X.X.X</code>" . "\n";
		$message .= " (Don't forget to replace <code>X.X.X</code> by your current version)" . "\n";
		$message .= "2. (Optional) If you forget your current version, you have to see it from your backup 'config/app.php' file";
		$message .= " (it's the last element of the array)." . "\n";
		$message .= "3. And <strong>refresh this page</strong> to finish upgrading";
		
		$data = [
			'message'   => $message,
			'status'    => $status,
			'exception' => $this,
		];
		
		// Get the theme error view path
		$viewPath = $this->getThemeErrorViewPath('custom');
		
		// Render the exception (if its view found)
		// or EXIT (i.e. Allow the Laravel core to handle the error)
		return !empty($viewPath)
			? response()->view($viewPath, $data, $status)
			: false;
	}
}
