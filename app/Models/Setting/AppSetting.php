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

use App\Helpers\Files\Upload;
use Illuminate\Support\Facades\Storage;

/*
 * settings.app.option
 */

class AppSetting
{
	public static function passedValidation($request)
	{
		$params = [
			[
				'attribute' => 'logo',
				'destPath'  => 'app/logo',
				'width'     => (int)config('settings.upload.img_resize_logo_width', 454),
				'height'    => (int)config('settings.upload.img_resize_logo_height', 80),
				'ratio'     => config('settings.upload.img_resize_logo_ratio', '1'),
				'upsize'    => config('settings.upload.img_resize_logo_upsize', '1'),
				'filename'  => 'logo-',
			],
			[
				'attribute' => 'logo_dark',
				'destPath'  => 'app/logo',
				'width'     => (int)config('settings.upload.img_resize_logo_width', 454),
				'height'    => (int)config('settings.upload.img_resize_logo_height', 80),
				'ratio'     => config('settings.upload.img_resize_logo_ratio', '1'),
				'upsize'    => config('settings.upload.img_resize_logo_upsize', '0'),
				'filename'  => 'logo-dark-',
			],
			[
				'attribute' => 'logo_light',
				'destPath'  => 'app/logo',
				'width'     => (int)config('settings.upload.img_resize_logo_width', 454),
				'height'    => (int)config('settings.upload.img_resize_logo_height', 80),
				'ratio'     => config('settings.upload.img_resize_logo_ratio', '1'),
				'upsize'    => config('settings.upload.img_resize_logo_upsize', '0'),
				'filename'  => 'logo-light-',
			],
			[
				'attribute' => 'favicon',
				'destPath'  => 'app/ico',
				'width'     => (int)config('settings.upload.img_resize_favicon_width', 32),
				'height'    => (int)config('settings.upload.img_resize_favicon_height', 32),
				'ratio'     => config('settings.upload.img_resize_favicon_ratio', '1'),
				'upsize'    => config('settings.upload.img_resize_favicon_upsize', '0'),
				'filename'  => 'ico-',
			],
		];
		
		foreach ($params as $param) {
			$file = $request->hasFile($param['attribute'])
				? $request->file($param['attribute'])
				: $request->input($param['attribute']);
			
			$request->request->set($param['attribute'], Upload::image($param['destPath'], $file, $param));
		}
		
		return $request;
	}
	
	public static function getValues($value, $disk)
	{
		if (empty($value)) {
			
			$value['purchase_code'] = env('PURCHASE_CODE', '');
			$value['name'] = config('app.name');
			$value['logo'] = config('larapen.media.logo');
			$value['logo_dark'] = config('larapen.media.logo-dark');
			$value['logo_light'] = config('larapen.media.logo-light');
			$value['favicon'] = config('larapen.media.favicon');
			$value['date_format'] = config('larapen.core.dateFormat.default');
			$value['datetime_format'] = config('larapen.core.datetimeFormat.default');
			$value['date_from_now_modifier'] = 'DIFF_RELATIVE_TO_NOW';
			$value['date_from_now_short'] = '0';
			
			$value['vector_charts_type'] = 'morris_bar';
			$value['vector_charts_limit'] = 7;
			$value['show_countries_charts'] = '1';
			$value['countries_charts_limit'] = 5;
			$value['latest_entries_limit'] = 5;
			$value['general_settings_as_submenu_in_sidebar'] = '1';
			
		} else {
			
			/**
			 * @var $disk Storage
			 */
			
			foreach ($value as $key => $item) {
				if ($key == 'logo') {
					$value['logo'] = str_replace('uploads/', '', $value['logo']);
					if (empty($value['logo']) || !$disk->exists($value['logo'])) {
						$value[$key] = config('larapen.media.logo');
					}
				}
				if ($key == 'logo_dark') {
					if (empty($value['logo_dark']) || !$disk->exists($value['logo_dark'])) {
						$value[$key] = config('larapen.media.logo-dark');
					}
				}
				if ($key == 'logo_light') {
					if (empty($value['logo_light']) || !$disk->exists($value['logo_light'])) {
						$value[$key] = config('larapen.media.logo-light');
					}
				}
				
				if ($key == 'favicon') {
					if (empty($value['favicon']) || !$disk->exists($value['favicon'])) {
						$value[$key] = config('larapen.media.favicon');
					}
				}
			}
			
			// Required keys & values
			// If $value exists and these keys don't exist, then set their default values
			if (!array_key_exists('purchase_code', $value)) {
				$value['purchase_code'] = env('PURCHASE_CODE', '');
			}
			if (!array_key_exists('name', $value)) {
				$value['name'] = config('app.name');
			}
			if (!array_key_exists('logo', $value)) {
				$value['logo'] = config('larapen.media.logo');
			}
			if (!array_key_exists('logo_dark', $value)) {
				$value['logo_dark'] = config('larapen.media.logo-dark');
			}
			if (!array_key_exists('logo_light', $value)) {
				$value['logo_light'] = config('larapen.media.logo-light');
			}
			if (!array_key_exists('favicon', $value)) {
				$value['favicon'] = config('larapen.media.favicon');
			}
			if (!array_key_exists('date_format', $value)) {
				$value['date_format'] = config('larapen.core.dateFormat.default');
			}
			if (!array_key_exists('datetime_format', $value)) {
				$value['datetime_format'] = config('larapen.core.datetimeFormat.default');
			}
			if (!array_key_exists('date_from_now_modifier', $value)) {
				$value['date_from_now_modifier'] = 'DIFF_RELATIVE_TO_NOW';
			}
			if (!array_key_exists('date_from_now_short', $value)) {
				$value['date_from_now_short'] = '0';
			}
			
			if (!array_key_exists('vector_charts_type', $value)) {
				$value['vector_charts_type'] = 'morris_bar';
			}
			if (!array_key_exists('vector_charts_limit', $value)) {
				$value['vector_charts_limit'] = 7;
			}
			if (!array_key_exists('show_countries_charts', $value)) {
				$value['show_countries_charts'] = '1';
			}
			if (!array_key_exists('countries_charts_limit', $value)) {
				$value['countries_charts_limit'] = 5;
			}
			if (!array_key_exists('latest_entries_limit', $value)) {
				$value['latest_entries_limit'] = 5;
			}
			if (!array_key_exists('general_settings_as_submenu_in_sidebar', $value)) {
				$value['general_settings_as_submenu_in_sidebar'] = '1';
			}
			
		}
		
		// logo
		$defaultLogo = 'app/default/logo.png';
		$defaultLogo = config('larapen.media.logo', $defaultLogo);
		$logo = $value['logo'] ?? $defaultLogo;
		
		// logo_dark
		$defaultLogoDark = 'app/default/logo-dark.png';
		$defaultLogoDark = config('larapen.media.logo-dark', $defaultLogoDark);
		$logoDark = $value['logo_dark'] ?? $defaultLogoDark;
		if ($logoDark == $defaultLogoDark && $logo != $defaultLogo) {
			if (!empty($logo) && $disk->exists($logo)) {
				$logoDark = $logo;
			}
		}
		
		// logo_light
		$defaultLogoLight = 'app/default/logo-light.png';
		$defaultLogoLight = config('larapen.media.logo-light', $defaultLogoLight);
		$logoLight = $value['logo_light'] ?? $defaultLogoLight;
		if ($logoLight == $defaultLogoLight && $logo != $defaultLogo) {
			if (!empty($logo) && $disk->exists($logo)) {
				$logoLight = $logo;
			}
		}
		
		// Append files URLs
		// logo
		$value['logo_url'] = thumbService($logo)->resize('logo')->url();
		$value['logo_dark_url'] = thumbService($logoDark)->resize('logo')->url();
		$value['logo_light_url'] = thumbService($logoLight)->resize('logo')->url();
		
		// favicon_url
		$favicon = 'app/default/ico/favicon.png';
		$favicon = $value['favicon'] ?? config('larapen.media.favicon', $favicon);
		$value['favicon_url'] = thumbService($favicon)->resize('favicon')->url();
		
		return $value;
	}
	
	public static function setValues($value, $setting)
	{
		return $value;
	}
	
	public static function getFields($diskName): array
	{
		$fields = [];
		
		// App's Info
		$fields = array_merge($fields, [
			[
				'name'  => 'separator_1',
				'type'  => 'custom_html',
				'value' => trans('admin.app_html_brand_info'),
			],
			[
				'name'  => 'purchase_code',
				'label' => trans('admin.Purchase Code'),
				'type'  => 'text',
				'hint'  => trans('admin.find_my_purchase_code', [
					'purchaseCodeFindingUrl' => config('larapen.core.purchaseCodeFindingUrl'),
				]),
			],
			[
				'name'              => 'name',
				'label'             => trans('admin.App Name'),
				'type'              => 'text',
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'slogan',
				'label'             => trans('admin.App Slogan'),
				'type'              => 'text',
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
		]);
		
		// App's Logo
		$fields = array_merge($fields, [
			[
				'name'              => 'dark_mode',
				'label'             => trans('admin.dark_mode_label'),
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.dark_mode_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-12',
				],
			],
			[
				'name'              => 'logo',
				'label'             => trans('admin.App Logo'),
				'type'              => 'image',
				'upload'            => true,
				'disk'              => $diskName,
				'default'           => config('larapen.media.logo'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'favicon',
				'label'             => trans('admin.Favicon'),
				'type'              => 'image',
				'upload'            => true,
				'disk'              => $diskName,
				'default'           => config('larapen.media.favicon'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'logo_dark',
				'label'             => trans('admin.logo_dark_label'),
				'type'              => 'image',
				'upload'            => true,
				'disk'              => $diskName,
				'default'           => config('larapen.media.logo-dark'),
				'hint'              => trans('admin.logo_dark_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6 dark-mode-field',
				],
			],
			[
				'name'              => 'logo_light',
				'label'             => trans('admin.logo_light_label'),
				'type'              => 'image',
				'upload'            => true,
				'disk'              => $diskName,
				'default'           => config('larapen.media.logo-light'),
				'hint'              => trans('admin.logo_light_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6 dark-mode-field',
				],
				'newline'           => true,
			],
		]);
		
		// App's Contact Info
		$fields = array_merge($fields, [
			[
				'name'              => 'email',
				'label'             => trans('admin.Email'),
				'type'              => 'email',
				'hint'              => trans('admin.The email address that all emails from the contact form will go to'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'phone_number',
				'label'             => trans('admin.Phone number'),
				'type'              => 'text',
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
		]);
		
		// Date Parameters
		$phpDateFormat = config('larapen.core.dateFormat.php');
		$phpDatetimeFormat = config('larapen.core.datetimeFormat.php');
		$phpDateFormatHint = trans('admin.php_date_format_hint', ['year' => date('Y')]);
		
		$isoDateFormat = config('larapen.core.dateFormat.default');
		$isoDatetimeFormat = config('larapen.core.datetimeFormat.default');
		$isoDateFormatHint = trans('admin.iso_date_format_hint', ['year' => date('Y')]);
		
		if (config('settings.app.php_specific_date_format')) {
			$dateFormat = $phpDateFormat;
			$datetimeFormat = $phpDatetimeFormat;
			$dateFormatHint = $phpDateFormatHint;
		} else {
			$dateFormat = $isoDateFormat;
			$datetimeFormat = $isoDatetimeFormat;
			$dateFormatHint = $isoDateFormatHint;
		}
		
		$fields = array_merge($fields, [
			[
				'name'  => 'dates_sep',
				'type'  => 'custom_html',
				'value' => trans('admin.dates_title'),
			],
			[
				'name'              => 'php_specific_date_format',
				'label'             => trans('admin.php_specific_date_format_label'),
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.php_specific_date_format_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-12',
				],
			],
			[
				'name'  => 'php_specific_date_format_info',
				'type'  => 'custom_html',
				'value' => trans('admin.php_specific_date_format_info'),
			],
			[
				'name'              => 'date_format',
				'label'             => trans('admin.date_format_label'),
				'type'              => 'text',
				'default'           => $dateFormat,
				'hint'              => $dateFormatHint . ' ' . trans('admin.app_date_format_hint_help'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'datetime_format',
				'label'             => trans('admin.datetime_format_label'),
				'type'              => 'text',
				'default'           => $datetimeFormat,
				'hint'              => $dateFormatHint . ' ' . trans('admin.app_date_format_hint_help'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'  => 'admin_date_format_info',
				'type'  => 'custom_html',
				'value' => trans('admin.admin_date_format_info', [
					'languagesUrl' => admin_url('languages'),
					'countriesUrl' => admin_url('countries'),
				]),
			],
			[
				'name'  => 'date_from_now_options_title',
				'type'  => 'custom_html',
				'value' => trans('admin.date_from_now_options_title', [
					'listingsList'    => trans('settings.listings_list'),
					'listingsListUrl' => admin_url('settings/find/listings_list'),
					'listingPage'     => trans('settings.listing_page'),
					'listingPageUrl'  => admin_url('settings/find/listing_page'),
				]),
			],
			[
				'name'              => 'date_from_now_modifier',
				'label'             => trans('admin.date_from_now_modifier_label'),
				'type'              => 'select2_from_array',
				'options'           => self::getDateFromNowModifiers(),
				'hint'              => trans('admin.date_from_now_modifier_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'date_from_now_short',
				'label'             => trans('admin.date_from_now_short_label'),
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.date_from_now_short_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6 mt-4',
				],
			],
		]);
		
		// Admin Panel Dashboard
		$fields = array_merge($fields, [
			[
				'name'  => 'backend_title_separator',
				'type'  => 'custom_html',
				'value' => trans('admin.backend_title_separator'),
			],
			[
				'name'  => 'settings_app_dashboard_sep',
				'type'  => 'custom_html',
				'value' => trans('admin.settings_app_dashboard_sep'),
			],
			[
				'name'              => 'vector_charts_type',
				'label'             => trans('admin.vector_charts_type_label'),
				'type'              => 'select2_from_array',
				'options'           => [
					'morris_bar'  => 'Morris - Bar Charts',
					'morris_line' => 'Morris - Line Charts',
				],
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'vector_charts_limit',
				'label'             => trans('admin.vector_charts_limit_label'),
				'type'              => 'select2_from_array',
				'options'           => collect(generateNumberRange(2, 15, 1))->mapWithKeys(fn ($i) => [$i => $i])->toArray(),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'show_countries_charts',
				'label'             => trans('admin.show_countries_charts_label'),
				'type'              => 'checkbox_switch',
				'wrapperAttributes' => [
					'class' => 'col-md-6 mt-4',
				],
			],
			[
				'name'              => 'countries_charts_limit',
				'label'             => trans('admin.countries_charts_limit_label'),
				'type'              => 'select2_from_array',
				'options'           => collect(generateNumberRange(2, 10, 1))->mapWithKeys(fn ($i) => [$i => $i])->toArray(),
				'wrapperAttributes' => [
					'class' => 'col-md-6 countries-charts-field',
				],
				'newline'           => true,
			],
			[
				'name'              => 'latest_entries_limit',
				'label'             => trans('admin.settings_app_latest_entries_limit_label'),
				'type'              => 'select2_from_array',
				'options'           => collect(generateNumberRange(5, 25, 5))->mapWithKeys(fn ($i) => [$i => $i])->toArray(),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'general_settings_as_submenu_in_sidebar',
				'label'             => trans('admin.general_settings_as_submenu_in_sidebar_label'),
				'type'              => 'checkbox_switch',
				'wrapperAttributes' => [
					'class' => 'col-md-6 mt-4',
				],
			],
		]);
		
		return addOptionsGroupJavaScript(__NAMESPACE__, __CLASS__, $fields, [
			'phpDateFormatHint' => $phpDateFormatHint,
			'phpDateFormat'     => $phpDateFormat,
			'phpDatetimeFormat' => $phpDatetimeFormat,
			'isoDateFormatHint' => $isoDateFormatHint,
			'isoDateFormat'     => $isoDateFormat,
			'isoDatetimeFormat' => $isoDatetimeFormat,
		]);
	}
	
	/**
	 * @return array
	 */
	private static function getDateFromNowModifiers(): array
	{
		$dateFromNowModifiers = [
			'DIFF_ABSOLUTE',
			'DIFF_RELATIVE_TO_NOW',
			'DIFF_RELATIVE_TO_OTHER',
		];
		
		return collect($dateFromNowModifiers)
			->mapWithKeys(function ($item, $key) {
				$index = $key + 1;
				$label = str($item)->remove('DIFF_')->replace('_', ' ')->title();
				
				return [$item => $index . '. ' . $label];
			})->toArray();
	}
}
