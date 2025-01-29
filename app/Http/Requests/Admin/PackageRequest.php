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

namespace App\Http\Requests\Admin;

use App\Models\Package;
use App\Models\Scopes\ActiveScope;

class PackageRequest extends Request
{
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		$type = $this->input('type');
		$isPromoPackage = ($type == 'promotion');
		$isSubsPackage = ($type == 'subscription');
		
		$globalListingsLimit = config('settings.listing_form.listings_limit', 5);
		$globalPicturesLimit = config('settings.listing_form.pictures_limit', 5);
		$globalActivatedListingsExpiration = config('settings.cron.activated_listings_expiration', 30);
		
		$rules = [
			'name'          => ['required', 'min:2', 'max:255'],
			'short_name'    => ['required', 'min:2', 'max:255'],
			'price'         => ['required', 'numeric'],
			'currency_code' => ['required'],
		];
		
		if ($isSubsPackage) {
			$rules['interval'] = ['required'];
			$rules['listings_limit'] = ['required', 'numeric', 'gte:' . $globalListingsLimit];
		}
		if ($this->filled('pictures_limit')) {
			$rules['pictures_limit'] = ['numeric', 'gte:' . $globalPicturesLimit];
		}
		if ($this->filled('expiration_time')) {
			$rules['expiration_time'] = ['numeric', 'gte:' . $globalActivatedListingsExpiration];
		}
		
		$isFromEditForm = in_array($this->method(), ['PUT', 'PATCH', 'UPDATE']);
		$currentPackageId = $this->segment(4);
		
		$countBasicPackage = Package::query()
			->withoutGlobalScopes([ActiveScope::class])
			->when($isPromoPackage, fn ($query) => $query->promotion())
			->when($isSubsPackage, fn ($query) => $query->subscription())
			->when($isFromEditForm, fn ($query) => $query->where('id', '!=', $currentPackageId))
			->applyCurrency()
			->columnIsEmpty('price')
			->count();
		
		$doesBasicPackageExist = ($countBasicPackage >= 1);
		if ($doesBasicPackageExist) {
			$rules['price'] = ['gt:0'];
		}
		
		return $rules;
	}
}
