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

namespace App\Http\Requests\Admin\SettingRequest;

use App\Http\Requests\Admin\Request;
use App\Rules\RedisConnectionRule;

/*
 * Use request() instead of $this since this form request can be called from another
 */

class OptimizationRequest extends Request
{
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		$request = request();
		
		$rules = [
			'cache_driver' => ['required', 'string'],
			'queue_driver' => ['required', 'string'],
		];
		
		// Cache
		$cacheDriver = $request->input('cache_driver');
		if ($cacheDriver == 'redis') {
			$origCacheDriver = config('cache.default');
			config()->set('cache.default', $cacheDriver);
			
			$rules['cache_driver'][] = new RedisConnectionRule();
			
			config()->set('cache.default', $origCacheDriver);
		}
		
		// Queue
		$queueDriver = $request->input('queue_driver');
		if ($queueDriver == 'redis') {
			$origQueueDriver = config('queue.default');
			config()->set('queue.default', $queueDriver);
			
			$rules['queue_driver'][] = new RedisConnectionRule();
			
			config()->set('queue.default', $origQueueDriver);
		}
		if ($queueDriver == 'sqs') {
			$rules['sqs_key'] = ['required', 'string'];
			$rules['sqs_secret'] = ['required', 'string'];
		}
		
		return $rules;
	}
	
	/**
	 * @return array
	 */
	public function messages(): array
	{
		$messages = [];
		
		return array_merge(parent::messages(), $messages);
	}
	
	/**
	 * @return array
	 */
	public function attributes(): array
	{
		$attributes = [
			'cache_driver' => trans('admin.cache_driver_label'),
			'queue_driver' => trans('admin.queue_driver_label'),
			'sqs_key'      => trans('admin.sqs_key_label'),
			'sqs_secret'   => trans('admin.sqs_secret_label'),
		];
		
		return array_merge(parent::attributes(), $attributes);
	}
}
