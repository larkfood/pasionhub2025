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

namespace App\Models\Setting;

/*
 * settings.optimization.option
 */

class OptimizationSetting
{
	public static function getValues($value, $disk)
	{
		if (empty($value)) {
			
			$value['cache_driver'] = 'file';
			$value['cache_expiration'] = '86400';
			$value['memcached_servers_1_host'] = '127.0.0.1';
			$value['memcached_servers_1_port'] = '11211';
			$value['redis_client'] = 'predis';
			$value['redis_cluster'] = 'predis';
			$value['redis_host'] = '127.0.0.1';
			$value['redis_password'] = null;
			$value['redis_port'] = '6379';
			$value['redis_database'] = '0';
			$value['queue_driver'] = 'sync';
			$value['lazy_loading_activation'] = '0';
			$value['minify_html_activation'] = '0';
			
		} else {
			
			if (!array_key_exists('cache_driver', $value)) {
				$value['cache_driver'] = 'file';
			}
			if (!array_key_exists('cache_expiration', $value)) {
				$value['cache_expiration'] = '86400';
			}
			if (!array_key_exists('memcached_servers_1_host', $value)) {
				$value['memcached_servers_1_host'] = '127.0.0.1';
			}
			if (!array_key_exists('memcached_servers_1_port', $value)) {
				$value['memcached_servers_1_port'] = '11211';
			}
			if (!array_key_exists('redis_client', $value)) {
				$value['redis_client'] = 'predis';
			}
			if (!array_key_exists('redis_cluster', $value)) {
				$value['redis_cluster'] = 'predis';
			}
			if (!array_key_exists('redis_host', $value)) {
				$value['redis_host'] = '127.0.0.1';
			}
			if (!array_key_exists('redis_password', $value)) {
				$value['redis_password'] = null;
			}
			if (!array_key_exists('redis_port', $value)) {
				$value['redis_port'] = '6379';
			}
			if (!array_key_exists('redis_database', $value)) {
				$value['redis_database'] = '0';
			}
			if (!array_key_exists('queue_driver', $value)) {
				$value['queue_driver'] = 'sync';
			}
			if (!array_key_exists('lazy_loading_activation', $value)) {
				$value['lazy_loading_activation'] = '0';
			}
			if (!array_key_exists('minify_html_activation', $value)) {
				$value['minify_html_activation'] = '0';
			}
			
		}
		
		// During the Cache variable updating from the Admin panel,
		// Check if the /.env file's cache configuration variables are different to the DB value,
		// If so, then display the right value from the /.env file.
		if (is_array($value)) {
			if (str_contains(currentRouteAction(), 'Admin\SettingController@edit')) {
				// Cache
				if (array_key_exists('cache_driver', $value) && getenv('CACHE_STORE')) {
					if ($value['cache_driver'] != env('CACHE_STORE')) {
						$value['cache_driver'] = env('CACHE_STORE');
					}
				}
				if (array_key_exists('memcached_servers_1_host', $value) && getenv('MEMCACHED_SERVER_1_HOST')) {
					if ($value['memcached_servers_1_host'] != env('MEMCACHED_SERVER_1_HOST')) {
						$value['memcached_servers_1_host'] = env('MEMCACHED_SERVER_1_HOST');
					}
				}
				if (array_key_exists('memcached_servers_1_port', $value) && getenv('MEMCACHED_SERVER_1_PORT')) {
					if ($value['memcached_servers_1_port'] != env('MEMCACHED_SERVER_1_PORT')) {
						$value['memcached_servers_1_port'] = env('MEMCACHED_SERVER_1_PORT');
					}
				}
				if (array_key_exists('redis_client', $value) && getenv('REDIS_CLIENT')) {
					if ($value['redis_client'] != env('REDIS_CLIENT')) {
						$value['redis_client'] = env('REDIS_CLIENT');
					}
				}
				if (array_key_exists('redis_cluster', $value) && getenv('REDIS_CLUSTER')) {
					if ($value['redis_cluster'] != env('REDIS_CLUSTER')) {
						$value['redis_cluster'] = env('REDIS_CLUSTER');
					}
				}
				if (array_key_exists('redis_host', $value) && getenv('REDIS_HOST')) {
					if ($value['redis_host'] != env('REDIS_HOST')) {
						$value['redis_host'] = env('REDIS_HOST');
					}
				}
				if (array_key_exists('redis_password', $value) && getenv('REDIS_PASSWORD')) {
					if ($value['redis_password'] != env('REDIS_PASSWORD')) {
						$value['redis_password'] = env('REDIS_PASSWORD');
					}
				}
				if (array_key_exists('redis_port', $value) && getenv('REDIS_PORT')) {
					if ($value['redis_port'] != env('REDIS_PORT')) {
						$value['redis_port'] = env('REDIS_PORT');
					}
				}
				if (array_key_exists('redis_database', $value) && getenv('REDIS_DB')) {
					if ($value['redis_database'] != env('REDIS_DB')) {
						$value['redis_database'] = env('REDIS_DB');
					}
				}
				
				// Queue
				if (array_key_exists('queue_driver', $value) && getenv('QUEUE_CONNECTION')) {
					if ($value['queue_driver'] != env('QUEUE_CONNECTION')) {
						$value['queue_driver'] = env('QUEUE_CONNECTION');
					}
				}
			}
		}
		
		return $value;
	}
	
	public static function setValues($value, $setting)
	{
		return $value;
	}
	
	public static function getFields($diskName): array
	{
		$cacheDrivers = (array)config('larapen.options.cache');
		$queueDrivers = (array)config('larapen.options.queue');
		$queue = 'mail,sms,thumbs,default';
		
		$fields = [
			[
				'name'  => 'caching_system_sep',
				'type'  => 'custom_html',
				'value' => trans('admin.caching_system_sep_value'),
			],
			[
				'name'              => 'cache_driver',
				'label'             => trans('admin.cache_driver_label'),
				'type'              => 'select2_from_array',
				'options'           => $cacheDrivers,
				'hint'              => trans('admin.cache_driver_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'cache_expiration',
				'label'             => trans('admin.cache_expiration_label'),
				'type'              => 'number',
				'hint'              => trans('admin.cache_expiration_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6 cache-enabled',
				],
			],
			[
				'name'  => 'cache_driver_info_sep',
				'type'  => 'custom_html',
				'value' => trans('admin.card_light', ['content' => trans('admin.cache_driver_info')]),
			],
			
			[
				'name'              => 'memcached_sep',
				'type'              => 'custom_html',
				'value'             => trans('admin.memcached_sep_value'),
				'wrapperAttributes' => [
					'class' => 'col-md-12 memcached',
				],
			],
			[
				'name'              => 'memcached_persistent_id',
				'label'             => trans('admin.memcached_persistent_id_label'),
				'type'              => 'text',
				'hint'              => trans('admin.memcached_persistent_id_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6 memcached',
				],
			],
			[
				'name'              => 'separator_clear_1',
				'type'              => 'custom_html',
				'value'             => '<div style="clear: both;"></div>',
				'wrapperAttributes' => [
					'class' => 'col-md-12 memcached',
				],
			],
			[
				'name'              => 'memcached_sasl_username',
				'label'             => trans('admin.memcached_sasl_username_label'),
				'type'              => 'text',
				'hint'              => trans('admin.memcached_sasl_username_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6 memcached',
				],
			],
			[
				'name'              => 'memcached_sasl_password',
				'label'             => trans('admin.memcached_sasl_password_label'),
				'type'              => 'text',
				'hint'              => trans('admin.memcached_sasl_password_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6 memcached',
				],
			],
			[
				'name'              => 'memcached_servers_sep',
				'type'              => 'custom_html',
				'value'             => trans('admin.memcached_servers_sep_value'),
				'wrapperAttributes' => [
					'class' => 'col-md-12 memcached',
				],
			],
			[
				'name'              => 'memcached_servers_1_host',
				'label'             => trans('admin.memcached_servers_host_label', ['num' => 1]),
				'type'              => 'text',
				'hint'              => trans('admin.memcached_servers_host_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6 memcached',
				],
			],
			[
				'name'              => 'memcached_servers_1_port',
				'label'             => trans('admin.memcached_servers_port_label', ['num' => 1]),
				'type'              => 'number',
				'hint'              => trans('admin.memcached_servers_port_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6 memcached',
				],
			],
			[
				'name'              => 'memcached_servers_2_host',
				'label'             => trans('admin.memcached_servers_host_label', ['num' => 2]) . ' (' . trans('admin.Optional') . ')',
				'type'              => 'text',
				'wrapperAttributes' => [
					'class' => 'col-md-6 memcached',
				],
			],
			[
				'name'              => 'memcached_servers_2_port',
				'label'             => trans('admin.memcached_servers_port_label', ['num' => 2]) . ' (' . trans('admin.Optional') . ')',
				'type'              => 'number',
				'wrapperAttributes' => [
					'class' => 'col-md-6 memcached',
				],
			],
			[
				'name'              => 'memcached_servers_3_host',
				'label'             => trans('admin.memcached_servers_host_label', ['num' => 3]) . ' (' . trans('admin.Optional') . ')',
				'type'              => 'text',
				'wrapperAttributes' => [
					'class' => 'col-md-6 memcached',
				],
			],
			[
				'name'              => 'memcached_servers_3_port',
				'label'             => trans('admin.memcached_servers_port_label', ['num' => 3]) . ' (' . trans('admin.Optional') . ')',
				'type'              => 'number',
				'wrapperAttributes' => [
					'class' => 'col-md-6 memcached',
				],
			],
			
			[
				'name'  => 'queue_title',
				'type'  => 'custom_html',
				'value' => trans('admin.queue_title'),
			],
			[
				'name'              => 'queue_driver',
				'label'             => trans('admin.queue_driver_label'),
				'type'              => 'select2_from_array',
				'options'           => $queueDrivers,
				'hint'              => trans('admin.queue_driver_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'queue_driver_info',
				'type'              => 'custom_html',
				'value'             => trans('admin.card_light_warning', [
					'content' => trans('admin.queue_driver_info', [
						'cmd' => getRightPathsForCmd('php artisan queue:work --queue=mail,sms,thumbs,default', wrapped: false),
					]),
				]),
				'wrapperAttributes' => [
					'class' => 'col-md-12',
				],
			],
			
			[
				'name'              => 'sqs_title',
				'type'              => 'custom_html',
				'value'             => trans('admin.sqs_title'),
				'wrapperAttributes' => [
					'class' => 'col-md-12 sqs',
				],
			],
			[
				'name'              => 'sqs_key',
				'label'             => trans('admin.sqs_key_label'),
				'type'              => 'text',
				'required'          => true,
				'hint'              => trans('admin.sqs_key_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6 sqs',
				],
			],
			[
				'name'              => 'sqs_secret',
				'label'             => trans('admin.sqs_secret_label'),
				'type'              => 'text',
				'required'          => true,
				'hint'              => trans('admin.sqs_secret_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6 sqs',
				],
			],
			[
				'name'              => 'sqs_prefix',
				'label'             => trans('admin.sqs_prefix_label'),
				'type'              => 'text',
				'default'           => 'https://sqs.us-east-1.amazonaws.com/your-account-id',
				'hint'              => trans('admin.sqs_prefix_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6 sqs',
				],
			],
			[
				'name'              => 'sqs_queue',
				'label'             => trans('admin.sqs_queue_label'),
				'type'              => 'text',
				'default'           => $queue,
				'attributes'        => [
					'disabled' => true,
				],
				'hint'              => trans('admin.sqs_queue_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6 sqs',
				],
			],
			[
				'name'              => 'sqs_suffix',
				'label'             => trans('admin.sqs_suffix_label'),
				'type'              => 'text',
				'default'           => '',
				'hint'              => trans('admin.sqs_suffix_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6 sqs',
				],
			],
			[
				'name'              => 'sqs_region',
				'label'             => trans('admin.sqs_region_label'),
				'type'              => 'text',
				'default'           => 'us-east-1',
				'hint'              => trans('admin.sqs_region_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6 sqs',
				],
			],
			
			[
				'name'  => 'webp_format_title',
				'type'  => 'custom_html',
				'value' => trans('admin.webp_format_title'),
			],
			[
				'name'  => 'webp_format',
				'label' => trans('admin.webp_format_label'),
				'type'  => 'checkbox_switch',
				'hint'  => trans('admin.webp_format_hint'),
			],
			
			[
				'name'  => 'lazy_loading_sep',
				'type'  => 'custom_html',
				'value' => trans('admin.lazy_loading_sep_value'),
			],
			[
				'name'  => 'lazy_loading_activation',
				'label' => trans('admin.lazy_loading_activation_label'),
				'type'  => 'checkbox_switch',
				'hint'  => trans('admin.lazy_loading_activation_hint'),
			],
			[
				'name'  => 'minify_html_sep',
				'type'  => 'custom_html',
				'value' => trans('admin.minify_html_sep_value'),
			],
			[
				'name'  => 'minify_html_activation',
				'label' => trans('admin.minify_html_activation_label'),
				'type'  => 'checkbox_switch',
				'hint'  => trans('admin.minify_html_activation_hint'),
			],
		];
		
		return addOptionsGroupJavaScript(__NAMESPACE__, __CLASS__, $fields);
	}
}
