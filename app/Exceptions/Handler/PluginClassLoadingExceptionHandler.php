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

use App\Exceptions\Handler\Plugin\FixFolderName;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/*
 * Check if there is no plugin class loading issue (inside composer class loader)
 */

trait PluginClassLoadingExceptionHandler
{
	use FixFolderName;
	
	/**
	 * @param \Throwable $e
	 * @return bool
	 */
	protected function isPluginClassLoadingException(\Throwable $e): bool
	{
		// Check if there is no plugin class loading issue (inside composer class loader)
		return (
			method_exists($e, 'getFile') && method_exists($e, 'getMessage')
			&& !empty($e->getFile()) && !empty($e->getMessage())
			&& str_contains($e->getFile(), '/vendor/composer/ClassLoader.php')
			&& str_contains($e->getMessage(), '/extras/plugins/')
		);
	}
	
	/**
	 * @param \Throwable $e
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
	 */
	protected function responsePluginClassLoadingException(\Throwable $e, Request $request): Response|JsonResponse
	{
		$message = $this->getPluginClassLoadingExceptionMessage($e, $request);
		
		return $this->responseCustomError($e, $request, $message);
	}
	
	// PRIVATE
	
	/**
	 * @param \Throwable $e
	 * @param \Illuminate\Http\Request $request
	 * @return string|null
	 */
	private function getPluginClassLoadingExceptionMessage(\Throwable $e, Request $request): ?string
	{
		$message = $e->getMessage();
		
		return !empty($message) ? $this->tryToFixPluginDirName($message) : null;
	}
}
