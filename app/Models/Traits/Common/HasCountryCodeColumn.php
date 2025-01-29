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

namespace App\Models\Traits\Common;

use App\Models\Country;
use Illuminate\Database\Eloquent\Builder;

trait HasCountryCodeColumn
{
	/*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
	public function getCountryHtml(): string
	{
		$out = '';
		
		$country = $this->country ?? null;
		$countryCode = $country->code ?? $this->country_code ?? null;
		
		if (empty($countryCode)) {
			return $out;
		}
		
		$countryName = $country->name ?? $countryCode;
		$countryFlagUrl = $country->flag_url ?? $this->country_flag_url ?? null;
		
		if (!empty($countryFlagUrl)) {
			$out .= '<a href="' . dmUrl($countryCode, '/', true, true) . '" target="_blank">';
			$out .= '<img src="' . $countryFlagUrl . '" data-bs-toggle="tooltip" title="' . $countryName . '">';
			$out .= '</a>';
		} else {
			$out .= $countryCode;
		}
		
		return $out;
	}
	
	/*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
	public function country()
	{
		return $this->belongsTo(Country::class, 'country_code', 'code');
	}
	
	/*
	|--------------------------------------------------------------------------
	| SCOPES
	|--------------------------------------------------------------------------
	*/
	public function scopeByCountry(Builder $builder, ?string $code = null): Builder
	{
		$code = !empty($code) ? $code : config('country.code');
		
		return $builder->where('country_code', '=', $code);
	}
	
	public function scopeInCountry(Builder $builder, ?string $code = null): Builder
	{
		$code = !empty($code) ? $code : config('country.code');
		
		return $builder->where('country_code', '=', $code);
	}
	
	// Old: Need to be removed
	public function scopeCurrentCountry(Builder $builder): Builder
	{
		return $builder->where('country_code', '=', config('country.code'));
	}
	
	// Old: Need to be removed
	public function scopeCountryOf(Builder $builder, $countryCode): Builder
	{
		return $builder->where('country_code', '=', $countryCode);
	}
	
	/*
	|--------------------------------------------------------------------------
	| ACCESSORS
	|--------------------------------------------------------------------------
	*/
	
	/*
	|--------------------------------------------------------------------------
	| MUTATORS
	|--------------------------------------------------------------------------
	*/
}
