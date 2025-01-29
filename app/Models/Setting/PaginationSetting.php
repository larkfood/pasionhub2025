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
 * settings.pagination.option
 */

class PaginationSetting
{
	public static function getValues($value, $disk)
	{
		if (empty($value)) {
			
			$value['per_page'] = 10;
			$value['categories_per_page'] = 12;
			$value['cities_per_page'] = 40;
			$value['payments_per_page'] = 10;
			$value['posts_per_page'] = 12;
			$value['saved_posts_per_page'] = 10;
			$value['saved_search_per_page'] = 20;
			$value['subadmin1_per_page'] = 39;
			$value['subadmin2_per_page'] = 38;
			$value['subscriptions_per_page'] = 10;
			$value['threads_per_page'] = 20;
			$value['threads_messages_per_page'] = 10;
			if (plugin_exists('reviews')) {
				$value['reviews_per_page'] = 10;
			}
			
			$value['similar_posts_limit'] = 4;
			$value['categories_limit'] = 50;
			$value['cities_limit'] = 50;
			$value['auto_complete_cities_limit'] = 25;
			
			$value['subadmin1_select_limit'] = 200;
			$value['subadmin2_select_limit'] = 5000;
			$value['cities_select_limit'] = 25;
			
		} else {
			
			if (!array_key_exists('per_page', $value)) {
				$value['per_page'] = 10;
			}
			if (!array_key_exists('categories_per_page', $value)) {
				$value['categories_per_page'] = 12;
			}
			if (!array_key_exists('cities_per_page', $value)) {
				$value['cities_per_page'] = 40;
			}
			if (!array_key_exists('payments_per_page', $value)) {
				$value['payments_per_page'] = 10;
			}
			if (!array_key_exists('posts_per_page', $value)) {
				$value['posts_per_page'] = 12;
			}
			if (!array_key_exists('saved_posts_per_page', $value)) {
				$value['saved_posts_per_page'] = 10;
			}
			if (!array_key_exists('saved_search_per_page', $value)) {
				$value['saved_search_per_page'] = 20;
			}
			if (!array_key_exists('subadmin1_per_page', $value)) {
				$value['subadmin1_per_page'] = 39;
			}
			if (!array_key_exists('subadmin2_per_page', $value)) {
				$value['subadmin2_per_page'] = 38;
			}
			if (!array_key_exists('subscriptions_per_page', $value)) {
				$value['subscriptions_per_page'] = 10;
			}
			if (!array_key_exists('threads_per_page', $value)) {
				$value['threads_per_page'] = 20;
			}
			if (!array_key_exists('threads_messages_per_page', $value)) {
				$value['threads_messages_per_page'] = 10;
			}
			if (plugin_exists('reviews')) {
				if (!array_key_exists('reviews_per_page', $value)) {
					$value['reviews_per_page'] = 10;
				}
			}
			
			if (!array_key_exists('similar_posts_limit', $value)) {
				$value['similar_posts_limit'] = 4;
			}
			if (!array_key_exists('categories_limit', $value)) {
				$value['categories_limit'] = 50;
			}
			if (!array_key_exists('cities_limit', $value)) {
				$value['cities_limit'] = 50;
			}
			if (!array_key_exists('auto_complete_cities_limit', $value)) {
				$value['auto_complete_cities_limit'] = 25;
			}
			
			if (!array_key_exists('subadmin1_select_limit', $value)) {
				$value['subadmin1_select_limit'] = 200;
			}
			if (!array_key_exists('subadmin2_select_limit', $value)) {
				$value['subadmin2_select_limit'] = 5000;
			}
			if (!array_key_exists('cities_select_limit', $value)) {
				$value['cities_select_limit'] = 25;
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
		$singleUrl = admin_url('settings/find/single');
		
		$fields = [
			[
				'name'  => 'per_page_info',
				'type'  => 'custom_html',
				'value' => trans('admin.per_page_info', ['url' => admin_url('settings/reset/pagination')]),
			],
			[
				'name'  => 'per_page_title',
				'type'  => 'custom_html',
				'value' => trans('admin.per_page_title'),
			],
			[
				'name'              => 'per_page',
				'label'             => trans('admin.per_page_label'),
				'type'              => 'number',
				'attributes'        => [
					'min'  => 1,
					'max'  => getMaxItemsPerPage(),
					'step' => 1,
				],
				'hint'              => trans('admin.per_page_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
				'newline'           => true,
			],
			
			[
				'name'              => 'categories_per_page',
				'label'             => trans('admin.categories_per_page_label'),
				'type'              => 'number',
				'attributes'        => [
					'min'  => 1,
					'max'  => getMaxItemsPerPage(),
					'step' => 1,
				],
				'hint'              => trans('admin.specific_per_page_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'cities_per_page',
				'label'             => trans('admin.cities_per_page_label'),
				'type'              => 'number',
				'attributes'        => [
					'min'  => 1,
					'max'  => getMaxItemsPerPage(),
					'step' => 1,
				],
				'hint'              => trans('admin.specific_per_page_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'posts_per_page',
				'label'             => trans('admin.posts_per_page_label'),
				'type'              => 'number',
				'attributes'        => [
					'min'  => 1,
					'max'  => getMaxItemsPerPage('posts'),
					'step' => 1,
				],
				'hint'              => trans('admin.specific_per_page_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'payments_per_page',
				'label'             => trans('admin.payments_per_page_label'),
				'type'              => 'number',
				'attributes'        => [
					'min'  => 1,
					'max'  => getMaxItemsPerPage(),
					'step' => 1,
				],
				'hint'              => trans('admin.specific_per_page_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'saved_posts_per_page',
				'label'             => trans('admin.saved_posts_per_page_label'),
				'type'              => 'number',
				'attributes'        => [
					'min'  => 1,
					'max'  => getMaxItemsPerPage(),
					'step' => 1,
				],
				'hint'              => trans('admin.specific_per_page_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'saved_search_per_page',
				'label'             => trans('admin.saved_search_per_page_label'),
				'type'              => 'number',
				'attributes'        => [
					'min'  => 1,
					'max'  => getMaxItemsPerPage(),
					'step' => 1,
				],
				'hint'              => trans('admin.specific_per_page_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'subadmin1_per_page',
				'label'             => trans('admin.subadmin1_per_page_label'),
				'type'              => 'number',
				'attributes'        => [
					'min'  => 1,
					'max'  => getMaxItemsPerPage(),
					'step' => 1,
				],
				'hint'              => trans('admin.specific_per_page_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'subadmin2_per_page',
				'label'             => trans('admin.subadmin2_per_page_label'),
				'type'              => 'number',
				'attributes'        => [
					'min'  => 1,
					'max'  => getMaxItemsPerPage(),
					'step' => 1,
				],
				'hint'              => trans('admin.specific_per_page_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'subscriptions_per_page',
				'label'             => trans('admin.subscriptions_per_page_label'),
				'type'              => 'number',
				'attributes'        => [
					'min'  => 1,
					'max'  => getMaxItemsPerPage(),
					'step' => 1,
				],
				'hint'              => trans('admin.specific_per_page_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'threads_per_page',
				'label'             => trans('admin.threads_per_page_label'),
				'type'              => 'number',
				'attributes'        => [
					'min'  => 1,
					'max'  => getMaxItemsPerPage(),
					'step' => 1,
				],
				'hint'              => trans('admin.specific_per_page_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'threads_messages_per_page',
				'label'             => trans('admin.threads_messages_per_page_label'),
				'type'              => 'number',
				'attributes'        => [
					'min'  => 1,
					'max'  => getMaxItemsPerPage(),
					'step' => 1,
				],
				'hint'              => trans('admin.specific_per_page_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
		];
		
		if (plugin_exists('reviews')) {
			$fields[] = [
				'name'              => 'reviews_per_page',
				'label'             => trans('reviews::messages.reviews_per_page_label'),
				'type'              => 'number',
				'attributes'        => [
					'min'  => 1,
					'max'  => getMaxItemsPerPage(),
					'step' => 1,
				],
				'hint'              => trans('admin.specific_per_page_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			];
		}
		
		$fields = array_merge($fields, [
			[
				'name'  => 'pagination_limit_title',
				'type'  => 'custom_html',
				'value' => trans('admin.pagination_limit_title'),
			],
			[
				'name'  => 'pagination_limit_info',
				'type'  => 'custom_html',
				'value' => trans('admin.pagination_limit_info', [
					'sectionsUrl'     => admin_url('sections'),
					'citiesUrl'       => admin_url('sections/find/locations'),
					'categoriesUrl'   => admin_url('sections/find/categories'),
					'postsUrl'        => admin_url('sections/find/latest_listings'),
					'premiumPostsUrl' => admin_url('sections/find/premium_listings'),
					'companiesUrl'    => admin_url('sections/find/companies'),
				]),
			],
			[
				'name'              => 'similar_posts_limit',
				'label'             => trans('admin.similar_posts_limit_label'),
				'type'              => 'number',
				'attributes'        => [
					'min'  => 1,
					'max'  => getMaxItemsPerPage('posts'),
					'step' => 1,
				],
				'hint'              => trans('admin.similar_posts_limit_hint', ['url' => $singleUrl]),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'categories_limit',
				'label'             => trans('admin.categories_limit_label'),
				'type'              => 'number',
				'attributes'        => [
					'min'  => 1,
					'max'  => getMaxItemsPerPage(),
					'step' => 1,
				],
				'hint'              => trans('admin.categories_limit_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'cities_limit',
				'label'             => trans('admin.cities_limit_label'),
				'type'              => 'number',
				'attributes'        => [
					'min'  => 1,
					'max'  => getMaxItemsPerPage(),
					'step' => 1,
				],
				'hint'              => trans('admin.cities_limit_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'auto_complete_cities_limit',
				'label'             => trans('admin.auto_complete_cities_limit_label'),
				'type'              => 'number',
				'attributes'        => [
					'min'  => 1,
					'max'  => getMaxItemsPerPage(),
					'step' => 1,
				],
				'hint'              => trans('admin.auto_complete_cities_limit_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			
			[
				'name'  => 'pagination_limit_location_title',
				'type'  => 'custom_html',
				'value' => trans('admin.pagination_limit_location_title'),
			],
			[
				'name'  => 'pagination_limit_location_info',
				'type'  => 'custom_html',
				'value' => trans('admin.pagination_limit_location_info', ['url' => $singleUrl]),
			],
			[
				'name'              => 'subadmin1_select_limit',
				'label'             => trans('admin.subadmin1_select_limit_label'),
				'type'              => 'number',
				'attributes'        => [
					'min'  => 1,
					'max'  => getMaxItemsPerPage('subadmin1_select'),
					'step' => 1,
				],
				'hint'              => trans('admin.specific_limit_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'subadmin2_select_limit',
				'label'             => trans('admin.subadmin2_select_limit_label'),
				'type'              => 'number',
				'attributes'        => [
					'min'  => 1,
					'max'  => getMaxItemsPerPage('subadmin2_select'),
					'step' => 1,
				],
				'hint'              => trans('admin.specific_limit_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'cities_select_limit',
				'label'             => trans('admin.cities_select_limit_label'),
				'type'              => 'number',
				'attributes'        => [
					'min'  => 1,
					'max'  => getMaxItemsPerPage(),
					'step' => 1,
				],
				'hint'              => trans('admin.specific_limit_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
		]);
		
		return $fields;
	}
}
