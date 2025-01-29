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

namespace App\Observers\Traits\Setting;

use App\Helpers\DotenvEditor;

trait OptimizationTrait
{
	/**
	 * Updating
	 *
	 * @param $setting
	 * @param $original
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	public function optimizationUpdating($setting, $original)
	{
		$this->updateEnvFileForCacheParameters($setting);
	}
	
	/**
	 * Update app caching system parameters in the /.env file
	 *
	 * @param $setting
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	private function updateEnvFileForCacheParameters($setting): void
	{
		if (!is_array($setting->value)) return;
		
		// Cache
		// Remove Existing Variables
		DotenvEditor::deleteKey('CACHE_STORE');
		DotenvEditor::deleteKey('CACHE_PREFIX');
		
		// memcached (remove /.env vars)
		DotenvEditor::deleteKey('MEMCACHED_PERSISTENT_ID');
		DotenvEditor::deleteKey('MEMCACHED_USERNAME');
		DotenvEditor::deleteKey('MEMCACHED_PASSWORD');
		$i = 1;
		while (DotenvEditor::keyExists('MEMCACHED_SERVER_' . $i . '_HOST')) {
			DotenvEditor::deleteKey('MEMCACHED_SERVER_' . $i . '_HOST');
			$i++;
		}
		$i = 1;
		while (DotenvEditor::keyExists('MEMCACHED_SERVER_' . $i . '_PORT')) {
			DotenvEditor::deleteKey('MEMCACHED_SERVER_' . $i . '_PORT');
			$i++;
		}
		
		// ...
		
		// Create Variables
		if (array_key_exists('cache_driver', $setting->value)) {
			DotenvEditor::setKey('CACHE_STORE', $setting->value['cache_driver']);
			DotenvEditor::setKey('CACHE_PREFIX', 'lc_');
		}
		
		// memcached (create /.env vars)
		if (array_key_exists('memcached_persistent_id', $setting->value)) {
			DotenvEditor::setKey('MEMCACHED_PERSISTENT_ID', $setting->value['memcached_persistent_id']);
		}
		if (array_key_exists('memcached_sasl_username', $setting->value)) {
			DotenvEditor::setKey('MEMCACHED_USERNAME', $setting->value['memcached_sasl_username']);
		}
		if (array_key_exists('memcached_sasl_password', $setting->value)) {
			DotenvEditor::setKey('MEMCACHED_PASSWORD', $setting->value['memcached_sasl_password']);
		}
		$i = 1;
		$fnWhileCondition = fn ($i) => (
			array_key_exists('memcached_servers_' . $i . '_host', $setting->value)
			&& array_key_exists('memcached_servers_' . $i . '_port', $setting->value)
		);
		while ($fnWhileCondition($i)) {
			DotenvEditor::deleteKey('MEMCACHED_SERVER_' . $i . '_HOST');
			DotenvEditor::deleteKey('MEMCACHED_SERVER_' . $i . '_PORT');
			DotenvEditor::setKey('MEMCACHED_SERVER_' . $i . '_HOST', $setting->value['memcached_servers_' . $i . '_host']);
			DotenvEditor::setKey('MEMCACHED_SERVER_' . $i . '_PORT', $setting->value['memcached_servers_' . $i . '_port']);
			$i++;
		}
		
		// Queue
		// Queue Driver
		if (array_key_exists('queue_driver', $setting->value)) {
			DotenvEditor::setKey('QUEUE_CONNECTION', $setting->value['queue_driver']);
		}
		
		// Save the /.env file
		DotenvEditor::save();
		
		// Some time of pause
		sleep(2);
	}
}
