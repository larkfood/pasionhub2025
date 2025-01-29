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

namespace App\Http\Controllers\Web\Setup\Update\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

trait ApiTrait
{
	/**
	 * Check the Purchase Code
	 *
	 * @return bool
	 */
	private function checkPurchaseCode(): bool
	{
		// Make sure that the website is properly installed
		if (!appEnvFileExists()) {
			return false;
		}
		
		// Make the purchase code verification only if 'installed' file exists
		if (!File::exists(storage_path('installed'))) {
			// Get purchase code from DB
			$purchaseCode = config('settings.app.purchase_code');
			
			// Write 'installed' file
			File::put(storage_path('installed'), '');
			
			// Send the purchase code checking
			$data = [];
			$endpoint = getPurchaseCodeApiEndpoint($purchaseCode, config('larapen.core.item.id'));
			try {
				/*
				 * Make the request and wait for 30 seconds for response.
				 * If it does not receive one, wait 5000 milliseconds (5 seconds), and then try again.
				 * Keep trying up to 2 times, and finally give up and throw an exception.
				 */
				$response = Http::withoutVerifying()->timeout(30)->retry(2, 5000)->get($endpoint)->throw();
				$data = $response->json();
			} catch (\Throwable $e) {
				$endpoint = (str_starts_with($endpoint, 'https:'))
					? str_replace('https:', 'http:', $endpoint)
					: str_replace('http:', 'https:', $endpoint);
				
				try {
					$response = Http::withoutVerifying()->timeout(30)->retry(2, 5000)->get($endpoint)->throw();
					$data = $response->json();
				} catch (\Throwable $e) {
					$data['message'] = parseHttpRequestError($e);
				}
			}
			
			// Update 'installed' file
			if (data_get($data, 'valid')) {
				File::put(storage_path('installed'), data_get($data, 'license_code'));
			}
		}
		
		return true;
	}
}
