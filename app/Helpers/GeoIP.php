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

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class GeoIP
{
	protected $ip = null;
	
	/**
	 * @return false|string|null
	 */
	public function getIp()
	{
		$ip = null;
		if (config('geoip.randomIp')) {
			$ip = long2ip(mt_rand());
		}
		if (empty($ip)) {
			$ip = Ip::get();
		}
		
		$this->ip = $ip;
		
		return $ip;
	}
	
	/**
	 * @return mixed
	 * @throws \Exception
	 */
	public function getData()
	{
		if (empty($this->ip)) {
			$this->ip = $this->getIp();
		}
		
		try {
			$data = $this->getDriver()->get($this->ip);
		} catch (\Exception $e) {
			$message = 'Failed to get GeoIP data';
			if (!empty($e->getMessage())) {
				$message = $e->getMessage() . ' ' . $message;
			}
			throw new \Exception($message, 0, $e);
		}
		
		/*
		// DEBUG
		if (config('geoip.randomIp') && !empty(data_get($data, 'countryCode'))) {
			$msg = 'GeoIP (' . config('geoip.default', '--') . '): ' . $this->ip . ' => ' . data_get($data, 'countryCode');
			Log::info($msg);
		}
		*/
		
		return $data;
	}
	
	/**
	 * @param $driver
	 * @return mixed
	 * @throws \Exception
	 */
	public function getDriver($driver = null)
	{
		$defaultDriver = $driver ?? config('geoip.default', '');
		
		$namespace = '\App\Helpers\GeoIP\Drivers\\';
		$driverClass = str($defaultDriver)
			->camel()
			->ucfirst()
			->prepend($namespace)
			->toString();
		
		if (!class_exists($driverClass)) {
			throw new \Exception(sprintf('Driver [%s] not supported.', $defaultDriver));
		}
		
		if (!method_exists($driverClass, 'get')) {
			throw new \Exception(sprintf('Driver [%s] not fully supported.', $defaultDriver));
		}
		
		return new $driverClass();
	}
}
