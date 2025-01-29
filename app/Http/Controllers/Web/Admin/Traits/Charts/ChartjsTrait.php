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

namespace App\Http\Controllers\Web\Admin\Traits\Charts;

/*
 * $colorOptions = ['luminosity' => 'light', 'hue' => ['red','orange','yellow','green','blue','purple','pink']];
 * $colorOptions = ['luminosity' => 'light'];
 */

use App\Helpers\RandomColor;
use App\Models\Country;

trait ChartjsTrait
{
	/**
	 * Graphic chart: Get total listings per country (limited to X countries)
	 *
	 * @param int $limit
	 * @param array|null $colorOptions
	 * @return array
	 */
	private function getPostsPerCountryForChartjs(int $limit = 5, ?array $colorOptions = []): array
	{
		// Init.
		$limit = (is_numeric($limit) && $limit > 0) ? $limit : 5;
		$colorOptions = (is_array($colorOptions)) ? $colorOptions : [];
		$data = [];
		
		// Get Data
		if ($this->countCountries > 1) {
			$countries = Country::query()
				->active()
				->has('posts')
				->withCount('posts')
				->orderByDesc('posts_count')
				->take($limit)
				->get();
			
			// Format Data
			if ($countries->count() > 0) {
				foreach ($countries as $country) {
					$data['datasets'][0]['data'][] = $country->posts_count;
					$data['datasets'][0]['backgroundColor'][] = RandomColor::one($colorOptions);
					$data['labels'][] = (!empty($country->name)) ? $country->name : $country->code;
				}
				$data['datasets'][0]['label'] = trans('admin.Posts Dataset');
			}
		}
		
		$data = json_encode($data, JSON_NUMERIC_CHECK);
		
		return [
			'title'          => trans('admin.Listings per Country') . ' (' . trans('admin.Most active Countries') . ')',
			'data'           => $data,
			'countCountries' => $this->countCountries,
		];
	}
	
	/**
	 * Graphic chart: Get total users per country (limited to X countries)
	 *
	 * @param int $limit
	 * @param array|null $colorOptions
	 * @return array
	 */
	private function getUsersPerCountryForChartjs(int $limit = 5, ?array $colorOptions = []): array
	{
		// Init.
		$limit = (is_numeric($limit) && $limit > 0) ? $limit : 5;
		$colorOptions = (is_array($colorOptions)) ? $colorOptions : [];
		$data = [];
		
		// Get Data
		if ($this->countCountries > 1) {
			$countries = Country::query()
				->active()
				->has('users')
				->withCount('users')
				->orderByDesc('users_count')
				->take($limit)
				->get();
			
			// Format Data
			if ($countries->count() > 0) {
				foreach ($countries as $country) {
					$data['datasets'][0]['data'][] = $country->users_count;
					$data['datasets'][0]['backgroundColor'][] = RandomColor::one($colorOptions);
					$data['labels'][] = (!empty($country->name)) ? $country->name : $country->code;
				}
				$data['datasets'][0]['label'] = trans('admin.Users Dataset');
			}
		}
		
		$data = json_encode($data, JSON_NUMERIC_CHECK);
		
		return [
			'title'          => trans('admin.Users per Country') . ' (' . trans('admin.Most active Countries') . ')',
			'data'           => $data,
			'countCountries' => $this->countCountries,
		];
	}
}
