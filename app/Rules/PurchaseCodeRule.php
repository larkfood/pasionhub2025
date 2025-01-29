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

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class PurchaseCodeRule implements ValidationRule
{
	protected ?string $itemId;
	private string $errorMessage;
	
	public function __construct(?string $itemId = null)
	{
		$this->itemId = $itemId;
		$this->errorMessage = trans('validation.purchase_code_rule');
	}
	
	/**
	 * Run the validation rule.
	 */
	public function validate(string $attribute, mixed $value, Closure $fail): void
	{
		if (!$this->passes($attribute, $value)) {
			$fail($this->errorMessage);
		}
	}
	
	/**
	 * Determine if the validation rule passes.
	 *
	 * @param string $attribute
	 * @param mixed $value
	 * @return bool
	 */
	public function passes(string $attribute, mixed $value): bool
	{
		$value = getAsString($value);
		
		// Check the purchase code
		$purchaseCodeData = $this->purchaseCodeChecker($value);
		$isValid = data_get($purchaseCodeData, 'valid');
		$doesPurchaseCodeIsValid = (is_bool($isValid) && $isValid == true);
		
		// Retrieve the error message
		if (!$doesPurchaseCodeIsValid) {
			$errorMessage = data_get($purchaseCodeData, 'message');
			$errorMessage = !empty($errorMessage) ? ' ERROR: <span class="fw-bold">' . $errorMessage . '</span>' : '';
			$this->errorMessage .= $errorMessage;
		}
		
		return $doesPurchaseCodeIsValid;
	}
	
	/**
	 * IMPORTANT: Do not change this part of the code to prevent any data-losing issue.
	 *
	 * @param string $purchaseCode
	 * @return array
	 */
	private function purchaseCodeChecker(string $purchaseCode): array
	{
		$data = [];
		$endpoint = getPurchaseCodeApiEndpoint($purchaseCode, $this->itemId);
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
		
		return is_array($data) ? $data : [];
	}
}
