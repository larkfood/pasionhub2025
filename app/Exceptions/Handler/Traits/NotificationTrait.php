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

namespace App\Exceptions\Handler\Traits;

use App\Notifications\ExceptionOccurred;
use Illuminate\Support\Facades\Notification;

trait NotificationTrait
{
	/**
	 * @param \Throwable $e
	 * @return void
	 */
	public function sendNotification(\Throwable $e): void
	{
		if ($this->isFullMemoryException($e)) {
			die($this->getFullMemoryMessage($e));
		}
		
		if (appIsBeingInstalled()) {
			return;
		}
		
		if (!config('larapen.core.sendNotificationOnError')) {
			return;
		}
		
		try {
			$fullUrl = request()->fullUrl();
			$isFromApi = (isFromApi() || str_contains($fullUrl, '/api/'));
			
			$content = [];
			// The request
			$content['method'] = request()->getMethod();
			if ($isFromApi) {
				$content['endpoint'] = $fullUrl;
				if (request()->hasHeader('X-WEB-REQUEST-URL')) {
					$content['url'] = request()->header('X-WEB-REQUEST-URL');
				}
				if (request()->hasHeader('X-IP')) {
					$content['ip'] = request()->header('X-IP');
				}
			} else {
				$content['url'] = $fullUrl;
				$content['ip'] = request()->ip();
			}
			$content['userAgent'] = request()->server('HTTP_USER_AGENT');
			$content['referer'] = request()->server('HTTP_REFERER');
			$content['body'] = request()->all();
			
			// The error
			$content['message'] = $e->getMessage();
			$content['file'] = $e->getFile();
			$content['line'] = $e->getLine();
			$content['trace'] = $e->getTrace();
			
			// Send notification
			Notification::route('mail', config('settings.app.email'))->notify(new ExceptionOccurred($content));
			
		} catch (\Throwable $e) {
			// dd($e); // debug
		}
	}
}
