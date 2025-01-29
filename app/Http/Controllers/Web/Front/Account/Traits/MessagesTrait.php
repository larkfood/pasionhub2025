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

namespace App\Http\Controllers\Web\Front\Account\Traits;

use App\Models\Thread;

trait MessagesTrait
{
	/**
	 * Check Threads with New Messages
	 *
	 * @return \Illuminate\Http\JsonResponse|void
	 */
	public function checkNew()
	{
		if (!isFromAjax()) {
			return;
		}
		
		$guard = getAuthGuard();
		$authUser = auth($guard)->check() ? auth($guard)->user() : null;
		$authUserId = !empty($authUser) ? $authUser->getAuthIdentifier() : 0;
		
		$countLimit = 20;
		$countThreadsWithNewMessages = 0;
		$oldValue = request()->input('oldValue');
		$languageCode = request()->input('languageCode');
		
		if (!empty($authUserId)) {
			$countThreadsWithNewMessages = Thread::query()
				->whereHas('post', fn ($query) => $query->inCountry()->unarchived())
				->forUserWithNewMessages($authUserId)
				->count();
		}
		
		$result = [
			'logged'                      => $authUserId,
			'countLimit'                  => (int)$countLimit,
			'countThreadsWithNewMessages' => (int)$countThreadsWithNewMessages,
			'oldValue'                    => (int)$oldValue,
			'loginUrl'                    => urlGen()->login(),
		];
		
		return ajaxResponse()->json($result);
	}
	
	/* PRIVATE */
	
	/**
	 * @param $entryId
	 * @return string
	 */
	private function getSelectedIds($entryId): string
	{
		$ids = [];
		if (request()->filled('entries')) {
			$ids = (array)request()->input('entries');
		} else {
			if (is_numeric($entryId) && $entryId > 0) {
				$ids[] = $entryId;
			}
		}
		
		return !empty($ids) ? '/' . implode(',', $ids) : '';
	}
}
