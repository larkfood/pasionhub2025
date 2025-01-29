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

namespace App\Providers\AppService\ConfigTrait;

trait CurrencyexchangeConfig
{
	private function updateCurrencyexchangeConfig(?array $settings = []): void
	{
		// currencyexchange
		$driver = $settings['driver'] ?? null;
		config()->set('currencyexchange.default', env('CURRENCY_EXCHANGE_DRIVER', $driver));
		
		// currencylayer
		if ($driver == 'currencylayer') {
			$accessKey = $settings['currencylayer_access_key'] ?? null;
			$currencyBase = $settings['currencylayer_base'] ?? null;
			$pro = $settings['currencylayer_pro'] ?? null;
			
			config()->set('currencyexchange.drivers.currencylayer.accessKey', env('CURRENCYLAYER_ACCESS_KEY', $accessKey));
			config()->set('currencyexchange.drivers.currencylayer.currencyBase', env('CURRENCYLAYER_BASE', $currencyBase));
			config()->set('currencyexchange.drivers.currencylayer.pro', env('CURRENCYLAYER_PRO', $pro));
		}
		
		// exchangerate_api
		if ($driver == 'exchangerate_api') {
			$apiKey = $settings['exchangerate_api_api_key'] ?? null;
			$currencyBase = $settings['exchangerate_api_base'] ?? null;
			
			config()->set('currencyexchange.drivers.exchangerate_api.apiKey', env('EXCHANGERATE_API_KEY', $apiKey));
			config()->set('currencyexchange.drivers.exchangerate_api.currencyBase', env('EXCHANGERATE_API_BASE', $currencyBase));
		}
		
		// exchangeratesapi_io
		if ($driver == 'exchangeratesapi_io') {
			$accessKey = $settings['exchangeratesapi_io_access_key'] ?? null;
			$currencyBase = $settings['exchangeratesapi_io_base'] ?? null;
			$pro = $settings['exchangeratesapi_io_pro'] ?? null;
			
			config()->set('currencyexchange.drivers.exchangeratesapi_io.accessKey', env('EXCHANGERATESAPI_IO_ACCESS_KEY', $accessKey));
			config()->set('currencyexchange.drivers.exchangeratesapi_io.currencyBase', env('EXCHANGERATESAPI_IO_BASE', $currencyBase));
			config()->set('currencyexchange.drivers.exchangeratesapi_io.pro', env('EXCHANGERATESAPI_IO_PRO', $pro));
		}
		
		// openexchangerates
		if ($driver == 'openexchangerates') {
			$appId = $settings['openexchangerates_app_id'] ?? null;
			$currencyBase = $settings['openexchangerates_base'] ?? null;
			
			config()->set('currencyexchange.drivers.openexchangerates.appId', env('OPENEXCHANGERATES_APP_ID', $appId));
			config()->set('currencyexchange.drivers.openexchangerates.currencyBase', env('OPENEXCHANGERATES_BASE', $currencyBase));
		}
		
		// fixer_io
		if ($driver == 'fixer_io') {
			$accessKey = $settings['fixer_io_access_key'] ?? null;
			$currencyBase = $settings['fixer_io_base'] ?? null;
			$pro = $settings['fixer_io_pro'] ?? null;
			
			config()->set('currencyexchange.drivers.fixer_io.accessKey', env('FIXER_IO_ACCESS_KEY', $accessKey));
			config()->set('currencyexchange.drivers.fixer_io.currencyBase', env('FIXER_IO_BASE', $currencyBase));
			config()->set('currencyexchange.drivers.fixer_io.pro', env('FIXER_IO_PRO', $pro));
		}
		
		// ecb
		if ($driver == 'ecb') {
			//...
		}
		
		// cbr
		if ($driver == 'cbr') {
			//...
		}
		
		// tcmb
		if ($driver == 'tcmb') {
			//...
		}
		
		// nbu
		if ($driver == 'nbu') {
			//...
		}
		
		// cnb
		if ($driver == 'cnb') {
			//...
		}
		
		// bnr
		if ($driver == 'bnr') {
			//...
		}
		
		// cache parameters
		$cacheTtl = $settings['cache_ttl'] ?? 86400;
		$cacheKeyPrefix = 'currencies-special-';
		
		config()->set('currencyexchange.options.cache_ttl', env('CURRENCY_EXCHANGE_CACHE_TTL', $cacheTtl));
		config()->set('currencyexchange.options.cache_key_prefix', $cacheKeyPrefix);
	}
	
	/**
	 * @param bool $isTestEnabled
	 * @param array|null $settings
	 * @return string|null
	 */
	private function testCurrencyexchangeConfig(bool $isTestEnabled, ?array $settings = []): ?string
	{
		if (!config('plugins.currencyexchange.installed')) {
			return null;
		}
		
		if (!$isTestEnabled) {
			return null;
		}
		
		// Apply updated config
		$this->updateCurrencyexchangeConfig($settings);
		
		/*
		 * Fetch the service
		 */
		$driver = config('currencyexchange.default');
		$message = null;
		try {
			$currencyExchangerHelper = '\extras\plugins\currencyexchange\app\Helpers\CurrencyExchanger';
			$data = (new $currencyExchangerHelper())->getData();
			$currencyBase = data_get($data, 'base');
			$rates = data_get($data, 'rates');
			
			if (
				!is_string($currencyBase)
				|| strlen($currencyBase) != 3
				|| !is_array($rates)
				|| empty($rates)
			) {
				$message = data_get($data, 'error');
				if (empty($message)) {
					$message = 'Unknown error occurred.';
				}
				if (!is_string($message)) {
					$message = 'Error occurred, but the error message is not a string.';
				}
			}
		} catch (\Throwable $e) {
			$message = $e->getMessage();
		}
		
		if (!empty($message)) {
			$exceptionMessageFormat = ' ERROR: <span class="fw-bold">%s</span>';
			$message = sprintf($exceptionMessageFormat, $message);
			$message = trans('admin.currencyexchange_fetching_error', ['driver' => $driver]) . $message;
		}
		
		return $message;
	}
}
