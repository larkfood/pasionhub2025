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

namespace App\Http\Controllers\Web\Front\Account;

use Larapen\LaravelMetaTags\Facades\MetaTag;

class TransactionsController extends AccountBaseController
{
	/**
	 * Promotions Transactions List
	 *
	 * @return \Illuminate\Contracts\View\View
	 */
	public function index()
	{
		$type = request()->segment(3);
		
		$isPromoting = ($type == 'promotion');
		$isSubscripting = ($type == 'subscription');
		
		// Call API endpoint
		$endpoint = '/payments/' . $type;
		$otherEmbed = $isSubscripting ? ',posts' : '';
		$queryParams = [
			'embed' => 'payable,paymentMethod,package,currency' . $otherEmbed,
			'sort'  => 'created_at',
		];
		$queryParams = array_merge(request()->all(), $queryParams);
		$data = makeApiRequest(method: 'get', uri: $endpoint, data: $queryParams);
		
		$apiMessage = $this->handleHttpError($data);
		$apiResult = data_get($data, 'result');
		
		$appName = config('settings.app.name', 'Site Name');
		$title = ($isSubscripting) ? t('my_subs_transactions') : t('my_promo_transactions');
		$title = $title . ' - ' . $appName;
		$description = t('my_transactions_on', ['appName' => config('settings.app.name')]);
		
		// Meta Tags
		MetaTag::set('title', $title);
		MetaTag::set('description', $description);
		
		return view(
			'front.account.transactions',
			compact('type', 'isPromoting', 'isSubscripting', 'apiResult', 'apiMessage')
		);
	}
}
