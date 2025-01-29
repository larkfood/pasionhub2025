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

namespace App\Http\Controllers\Api;

use App\Models\PaymentMethod;
use App\Http\Resources\EntityCollection;
use App\Http\Resources\PaymentMethodResource;

/**
 * @group Payment Methods
 */
class PaymentMethodController extends BaseController
{
	/**
	 * List payment methods
	 *
	 * @queryParam countryCode string Country code. Select only the payment methods related to a country. Example: US
	 * @queryParam sort string The sorting parameter (Order by DESC with the given column. Use "-" as prefix to order by ASC). Possible values: lft. Example: -lft
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index(): \Illuminate\Http\JsonResponse
	{
		$plugins = array_keys((array)config('plugins.installed'));
		$countryCode = getAsStringOrNull(request()->input('countryCode'));
		
		// Cache ID
		$cachePluginsId = !empty($plugins) ? '.plugins.' . implode(',', $plugins) : '';
		$cacheId = $countryCode . '.paymentMethods.all' . $cachePluginsId;
		
		// Cached Query
		$paymentMethods = cache()->remember($cacheId, $this->cacheExpiration, function () use ($plugins, $countryCode) {
			$paymentMethods = PaymentMethod::query()->whereIn('name', $plugins);
			
			if (!empty($countryCode)) {
				$countryCode = strtolower($countryCode);
				$findInSet = 'FIND_IN_SET("' . $countryCode . '", LOWER(countries)) > 0';
				
				$paymentMethods->where(function ($query) use ($findInSet) {
					$query->whereRaw($findInSet)->orWhere(fn ($query) => $query->columnIsEmpty('countries'));
				});
			}
			
			// Sorting
			$paymentMethods = $this->applySorting($paymentMethods, ['lft']);
			
			return $paymentMethods->get();
		});
		
		$resourceCollection = new EntityCollection(class_basename($this), $paymentMethods);
		
		$message = ($paymentMethods->count() <= 0) ? t('no_payment_methods_found') : null;
		
		return apiResponse()->withCollection($resourceCollection, $message);
	}
	
	/**
	 * Get payment method
	 *
	 * @urlParam $id int required Can be the ID (int) or name (string) of the payment method. Example: 1
	 *
	 * @param int|string $id
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show(int|string $id): \Illuminate\Http\JsonResponse
	{
		$idColumn = is_numeric($id) ? 'id' : 'name';
		
		$cacheId = 'paymentMethod.' . $idColumn . '.' . $id;
		$paymentMethod = cache()->remember($cacheId, $this->cacheExpiration, function () use ($idColumn, $id) {
			$paymentMethod = PaymentMethod::query()->where($idColumn, $id);
			
			return $paymentMethod->first();
		});
		
		abort_if(empty($paymentMethod), 404, t('payment_method_not_found'));
		
		$resource = new PaymentMethodResource($paymentMethod);
		
		return apiResponse()->withResource($resource);
	}
}
