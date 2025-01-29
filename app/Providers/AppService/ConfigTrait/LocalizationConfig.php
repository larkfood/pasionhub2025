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

use App\Helpers\GeoIP;

trait LocalizationConfig
{
	private function updateLocalizationConfig(?array $settings = []): void
	{
		// geoip
		$driver = $settings['geoip_driver'] ?? null;
		$driverTest = $settings['geoip_driver_test'] ?? '0';
		$driverTest = ($driverTest == '1');
		
		config()->set('geoip.default', env('GEOIP_DRIVER', $driver));
		config()->set('geoip.randomIp', env('GEOIP_RANDOM_IP', $driverTest));
		
		// ipinfo
		if (config('geoip.default') == 'ipinfo') {
			$token = $settings['ipinfo_token'] ?? null;
			config()->set('geoip.drivers.ipinfo.token', env('GEOIP_IPINFO_TOKEN', $token));
		}
		
		// dbip
		if (config('geoip.default') == 'dbip') {
			$apiKey = $settings['dbip_api_key'] ?? null;
			$pro = $settings['dbip_pro'] ?? null;
			
			config()->set('geoip.drivers.dbip.apiKey', env('GEOIP_DBIP_API_KEY', $apiKey));
			config()->set('geoip.drivers.dbip.pro', env('GEOIP_DBIP_PRO', $pro));
		}
		
		// ipbase
		if (config('geoip.default') == 'ipbase') {
			$apiKey = $settings['ipbase_api_key'] ?? null;
			config()->set('geoip.drivers.ipbase.apiKey', env('GEOIP_IPBASE_API_KEY', $apiKey));
		}
		
		// ip2location
		if (config('mail.default') == 'ip2location') {
			$apiKey = $settings['ip2location_api_key'] ?? null;
			config()->set('geoip.drivers.ip2location.apiKey', env('GEOIP_IP2LOCATION_API_KEY', $apiKey));
		}
		
		// ipapi
		if (config('geoip.default') == 'ipapi') {
			$pro = $settings['ipapi_pro'] ?? null;
			config()->set('geoip.drivers.ipapi.pro', env('GEOIP_IPAPI_PRO', $pro));
		}
		
		// ipapico
		if (config('geoip.default') == 'ipapico') {
			//...
		}
		
		// ipgeolocation
		if (config('geoip.default') == 'ipgeolocation') {
			$apiKey = $settings['ipgeolocation_api_key'] ?? null;
			config()->set('geoip.drivers.ipgeolocation.apiKey', env('GEOIP_IPGEOLOCATION_API_KEY', $apiKey));
		}
		
		// iplocation
		if (config('geoip.default') == 'iplocation') {
			$apiKey = $settings['iplocation_api_key'] ?? null;
			$pro = $settings['iplocation_pro'] ?? null;
			
			config()->set('geoip.drivers.iplocation.apiKey', env('GEOIP_IPLOCATION_API_KEY', $apiKey));
			config()->set('geoip.drivers.iplocation.pro', env('GEOIP_IPLOCATION_PRO', $pro));
		}
		
		// ipstack
		if (config('geoip.default') == 'ipstack') {
			$accessKey = $settings['ipstack_access_key'] ?? null;
			$pro = $settings['ipstack_pro'] ?? null;
			
			config()->set('geoip.drivers.ipstack.accessKey', env('GEOIP_IPSTACK_ACCESS_KEY', $accessKey));
			config()->set('geoip.drivers.ipstack.pro', env('GEOIP_IPLOCATION_PRO', $pro));
		}
		
		// maxmind_api
		if (config('geoip.default') == 'maxmind_api') {
			$accountId = $settings['maxmind_api_account_id'] ?? null;
			$licenseKey = $settings['maxmind_api_license_key'] ?? null;
			
			config()->set('geoip.drivers.maxmind_api.accountId', env('GEOIP_MAXMIND_ACCOUNT_ID', $accountId));
			config()->set('geoip.drivers.maxmind_api.licenseKey', env('GEOIP_MAXMIND_LICENSE_KEY', $licenseKey));
		}
		
		// maxmind_database
		if (config('geoip.default') == 'maxmind_database') {
			$licenseKey = $settings['maxmind_database_license_key'] ?? null;
			config()->set('geoip.drivers.maxmind_database.licenseKey', env('GEOIP_MAXMIND_LICENSE_KEY', $licenseKey));
		}
	}
	
	/**
	 * @param bool $isTestEnabled
	 * @param array|null $settings
	 * @return string|null
	 */
	private function testGeoIPConfig(bool $isTestEnabled, ?array $settings = []): ?string
	{
		if (!$isTestEnabled) {
			return null;
		}
		
		// Apply updated config
		$this->updateLocalizationConfig($settings);
		
		/*
		 * Fetch the service
		 */
		$driver = config('geoip.default');
		$message = null;
		try {
			$data = (new GeoIP())->getData();
			$countryCode = data_get($data, 'countryCode');
			
			if (!is_string($countryCode) || strlen($countryCode) != 2) {
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
			$message = trans('admin.geoip_fetching_error', ['driver' => $driver]) . $message;
		}
		
		return $message;
	}
}
