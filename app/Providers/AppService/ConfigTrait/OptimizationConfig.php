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

trait OptimizationConfig
{
	private function updateOptimizationConfig(?array $settings = []): void
	{
		$cacheDrivers = (array)config('larapen.options.cache');
		$queueDrivers = (array)config('larapen.options.queue');
		
		// Cache Options
		// Cache Driver (Can be reset from the /.env file)
		$cacheDriver = $settings['cache_driver'] ?? 'file';
		$cacheDriver = env('CACHE_STORE', $cacheDriver);
		$cacheDriver = in_array($cacheDriver, array_keys($cacheDrivers)) ? $cacheDriver : 'file';
		config()->set('cache.default', $cacheDriver);
		
		// Memcached
		if ($cacheDriver == 'memcached') {
			$persistentId = $settings['memcached_persistent_id'] ?? null;
			$persistentId = env('MEMCACHED_PERSISTENT_ID', $persistentId);
			config()->set('cache.stores.memcached.persistent_id', $persistentId);
			
			$saslUsername = $settings['memcached_sasl_username'] ?? null;
			$saslPassword = $settings['memcached_sasl_password'] ?? null;
			$saslUsername = env('MEMCACHED_USERNAME', $saslUsername);
			$saslPassword = env('MEMCACHED_PASSWORD', $saslPassword);
			config()->set('cache.stores.memcached.sasl', [$saslUsername, $saslPassword]);
			
			$memcachedServers = [];
			$i = 1;
			// $fnWhileCondition = fn ($i) => getenv('MEMCACHED_SERVER_' . $i . '_HOST');
			$fnWhileCondition = fn ($i) => array_key_exists('memcached_servers_' . $i . '_host', $settings);
			while ($fnWhileCondition($i)) {
				$isFirstServer = ($i == 1);
				
				$host = $isFirstServer ? '127.0.0.1' : null;
				$port = $isFirstServer ? 11211 : null;
				
				$host = $settings['memcached_servers_' . $i . '_host'] ?? $host;
				$host = env('MEMCACHED_SERVER_' . $i . '_HOST', $host);
				$memcachedServers[$i]['host'] = $host;
				
				$port = $settings['memcached_servers_' . $i . '_port'] ?? $port;
				$port = env('MEMCACHED_SERVER_' . $i . '_PORT', $port);
				$memcachedServers[$i]['port'] = $port;
				
				$i++;
			}
			config()->set('cache.stores.memcached.servers', $memcachedServers);
		}
		
		// Check if the caching is disabled, then disabled it!
		if ($cacheDriver == 'array') {
			config()->set('settings.optimization.cache_expiration', '-1');
		}
		
		// Queue Options
		// Queue Driver (Can be reset from the /.env file)
		$queueDriver = $settings['queue_driver'] ?? 'sync';
		$queueDriver = env('QUEUE_CONNECTION', $queueDriver);
		$queueDriver = in_array($queueDriver, array_keys($queueDrivers)) ? $queueDriver : 'sync';
		config()->set('queue.default', $queueDriver);
		
		// Amazon SQS
		if ($queueDriver == 'sqs') {
			$key = $settings['sqs_key'] ?? null;
			$key = env('AWS_ACCESS_KEY_ID', $key);
			config()->set('queue.connections.sqs.key', $key);
			
			$secret = $settings['sqs_secret'] ?? null;
			$secret = env('AWS_SECRET_ACCESS_KEY', $secret);
			config()->set('queue.connections.sqs.secret', $secret);
			
			$prefix = $settings['sqs_prefix'] ?? null;
			$prefix = env('SQS_PREFIX', $prefix);
			config()->set('queue.connections.sqs.prefix', $prefix);
			
			$queue = $settings['sqs_queue'] ?? 'default';
			$queue = env('SQS_QUEUE', $queue);
			config()->set('queue.connections.sqs.queue', $queue);
			
			$suffix = $settings['sqs_suffix'] ?? null;
			$suffix = env('SQS_SUFFIX', $suffix);
			config()->set('queue.connections.sqs.suffix', $suffix);
			
			$region = $settings['sqs_region'] ?? 'us-east-1';
			$region = env('AWS_DEFAULT_REGION', $region);
			config()->set('queue.connections.sqs.region', $region);
		}
	}
}
