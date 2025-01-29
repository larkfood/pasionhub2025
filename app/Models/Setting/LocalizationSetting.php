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
 * settings.localization.option
 */

class LocalizationSetting
{
	public static function getValues($value, $disk)
	{
		if (empty($value)) {
			
			$value['geoip_driver'] = 'ipapi';
			$value['country_flag_shape'] = 'rectangle';
			$value['show_country_flag'] = 'in_next_logo';
			
			$value['auto_detect_language'] = 'disabled';
			$value['show_languages_flags'] = '0';
			
		} else {
			
			// Old key
			if (!array_key_exists('geoip_activation', $value)) {
				if (isset($value['active'])) {
					$value['geoip_activation'] = $value['active'];
				}
			}
			
			if (!array_key_exists('geoip_driver', $value)) {
				$value['geoip_driver'] = 'ipapi';
			}
			if (!array_key_exists('country_flag_shape', $value)) {
				$value['country_flag_shape'] = 'rectangle';
			} else {
				$flagShapes = array_keys(getCountryFlagShapes());
				$countryFlagShape = $value['country_flag_shape'] ?? 'rectangle';
				$value['country_flag_shape'] = in_array($countryFlagShape, $flagShapes) ? $countryFlagShape : 'rectangle';
			}
			if (!array_key_exists('show_country_flag', $value)) {
				$value['show_country_flag'] = 'in_next_logo';
			}
			
			if (!array_key_exists('auto_detect_language', $value)) {
				$oldAutoDetectLanguage = config('settings.app.auto_detect_language', '0');
				$oldAutoDetectLanguageOptions = [
					'0' => 'disabled',
					'1' => 'from_browser',
					'2' => 'from_country',
				];
				$value['auto_detect_language'] = $oldAutoDetectLanguageOptions[$oldAutoDetectLanguage] ?? 'disabled';
			}
			if (!array_key_exists('show_languages_flags', $value)) {
				$oldShowLanguagesFlags = config('settings.app.show_languages_flags', '0');
				$value['show_languages_flags'] = $oldShowLanguagesFlags;
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
		// Get Drivers List
		$geoipDrivers = (array)config('larapen.options.geoip');
		
		// Get the drivers selectors list as JS objects
		$geoipDriversSelectorsJson = collect($geoipDrivers)
			->keys()
			->mapWithKeys(fn ($item) => [$item => '.' . $item])
			->toJson();
		
		// Geo Location
		$fields = [
			[
				'name'  => 'localization_geolocation',
				'type'  => 'custom_html',
				'value' => trans('admin.localization_geolocation'),
			],
			[
				'name'              => 'geoip_activation',
				'label'             => trans('admin.geoip_activation_label'),
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.geoip_activation_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
				'newline'           => true,
			],
			
			[
				'name'              => 'geoip_driver',
				'label'             => trans('admin.geoip_driver_label'),
				'type'              => 'select2_from_array',
				'options'           => $geoipDrivers,
				'hint'              => trans('admin.geoip_driver_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'geoip_driver_test',
				'label'             => trans('admin.geoip_driver_test_label'),
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.geoip_driver_test_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6 mt-3',
				],
			],
		];
		
		// ipinfo.io
		if (array_key_exists('ipinfo', $geoipDrivers)) {
			$fields = array_merge($fields, [
				[
					'name'              => 'ipinfo_info',
					'type'              => 'custom_html',
					'value'             => trans('admin.ipinfo_info'),
					'wrapperAttributes' => [
						'class' => 'ipinfo',
					],
				],
				[
					'name'              => 'ipinfo_token',
					'label'             => trans('admin.ipinfo_token_label'),
					'type'              => 'text',
					'wrapperAttributes' => [
						'class' => 'col-md-6 ipinfo',
					],
				],
			]);
		}
		
		// db-ip.com
		if (array_key_exists('dbip', $geoipDrivers)) {
			$fields = array_merge($fields, [
				[
					'name'              => 'dbip_info',
					'type'              => 'custom_html',
					'value'             => trans('admin.dbip_info'),
					'wrapperAttributes' => [
						'class' => 'dbip',
					],
				],
				[
					'name'              => 'dbip_pro',
					'label'             => trans('admin.geoip_driver_pro_label'),
					'type'              => 'checkbox_switch',
					'wrapperAttributes' => [
						'class' => 'col-md-6 dbip',
					],
				],
				[
					'name'              => 'dbip_api_key',
					'label'             => trans('admin.dbip_api_key_label'),
					'type'              => 'text',
					'wrapperAttributes' => [
						'class' => 'col-md-6 dbip',
					],
				],
			]);
		}
		
		// ipbase.com
		if (array_key_exists('ipbase', $geoipDrivers)) {
			$fields = array_merge($fields, [
				[
					'name'              => 'ipbase_info',
					'type'              => 'custom_html',
					'value'             => trans('admin.ipbase_info'),
					'wrapperAttributes' => [
						'class' => 'ipbase',
					],
				],
				[
					'name'              => 'ipbase_api_key',
					'label'             => trans('admin.ipbase_api_key_label'),
					'type'              => 'text',
					'required'          => true,
					'wrapperAttributes' => [
						'class' => 'col-md-6 ipbase',
					],
				],
			]);
		}
		
		// ip2location.com
		if (array_key_exists('ip2location', $geoipDrivers)) {
			$fields = array_merge($fields, [
				[
					'name'              => 'ip2location_info',
					'type'              => 'custom_html',
					'value'             => trans('admin.ip2location_info'),
					'wrapperAttributes' => [
						'class' => 'ip2location',
					],
				],
				[
					'name'              => 'ip2location_api_key',
					'label'             => trans('admin.ip2location_api_key_label'),
					'type'              => 'text',
					'required'          => true,
					'wrapperAttributes' => [
						'class' => 'col-md-6 ip2location',
					],
				],
			]);
		}
		
		// ip-api.com
		if (array_key_exists('ipapi', $geoipDrivers)) {
			$fields = array_merge($fields, [
				[
					'name'              => 'ipapi_info',
					'type'              => 'custom_html',
					'value'             => trans('admin.ipapi_info'),
					'wrapperAttributes' => [
						'class' => 'ipapi',
					],
				],
				[
					'name'              => 'ipapi_pro',
					'label'             => trans('admin.geoip_driver_pro_label'),
					'type'              => 'checkbox_switch',
					'wrapperAttributes' => [
						'class' => 'col-md-6 ipapi',
					],
				],
			]);
		}
		
		// ipapi.co
		if (array_key_exists('ipapico', $geoipDrivers)) {
			$fields = array_merge($fields, [
				[
					'name'              => 'ipapico_info',
					'type'              => 'custom_html',
					'value'             => trans('admin.ipapico_info'),
					'wrapperAttributes' => [
						'class' => 'ipapico',
					],
				],
			]);
		}
		
		// ipgeolocation.io
		if (array_key_exists('ipgeolocation', $geoipDrivers)) {
			$fields = array_merge($fields, [
				[
					'name'              => 'ipgeolocation_info',
					'type'              => 'custom_html',
					'value'             => trans('admin.ipgeolocation_info'),
					'wrapperAttributes' => [
						'class' => 'ipgeolocation',
					],
				],
				[
					'name'              => 'ipgeolocation_api_key',
					'label'             => trans('admin.ipgeolocation_api_key_label'),
					'type'              => 'text',
					'required'          => true,
					'wrapperAttributes' => [
						'class' => 'col-md-6 ipgeolocation',
					],
				],
			]);
		}
		
		// iplocation.net
		if (array_key_exists('iplocation', $geoipDrivers)) {
			$fields = array_merge($fields, [
				[
					'name'              => 'iplocation_info',
					'type'              => 'custom_html',
					'value'             => trans('admin.iplocation_info'),
					'wrapperAttributes' => [
						'class' => 'iplocation',
					],
				],
				[
					'name'              => 'iplocation_pro',
					'label'             => trans('admin.geoip_driver_pro_label'),
					'type'              => 'checkbox_switch',
					'wrapperAttributes' => [
						'class' => 'col-md-6 iplocation',
					],
				],
				[
					'name'              => 'iplocation_api_key',
					'label'             => trans('admin.iplocation_api_key_label'),
					'type'              => 'text',
					'wrapperAttributes' => [
						'class' => 'col-md-6 iplocation',
					],
				],
			]);
		}
		
		// ipstack.com
		if (array_key_exists('ipstack', $geoipDrivers)) {
			$fields = array_merge($fields, [
				[
					'name'              => 'ipstack_info',
					'type'              => 'custom_html',
					'value'             => trans('admin.ipstack_info'),
					'wrapperAttributes' => [
						'class' => 'ipstack',
					],
				],
				[
					'name'              => 'ipstack_pro',
					'label'             => trans('admin.geoip_driver_pro_label'),
					'type'              => 'checkbox_switch',
					'wrapperAttributes' => [
						'class' => 'col-md-6 ipstack',
					],
				],
				[
					'name'              => 'ipstack_access_key',
					'label'             => trans('admin.ipstack_access_key_label'),
					'type'              => 'text',
					'wrapperAttributes' => [
						'class' => 'col-md-6 ipstack',
					],
				],
			]);
		}
		
		// maxmind.com (Web Services)
		if (array_key_exists('maxmind_api', $geoipDrivers)) {
			$fields = array_merge($fields, [
				[
					'name'              => 'maxmind_api_info',
					'type'              => 'custom_html',
					'value'             => trans('admin.maxmind_api_info'),
					'wrapperAttributes' => [
						'class' => 'maxmind_api',
					],
				],
				[
					'name'              => 'maxmind_api_account_id',
					'label'             => trans('admin.maxmind_api_account_id_label'),
					'type'              => 'text',
					'required'          => true,
					'wrapperAttributes' => [
						'class' => 'col-md-6 maxmind_api',
					],
				],
				[
					'name'              => 'maxmind_api_license_key',
					'label'             => trans('admin.maxmind_api_license_key_label'),
					'type'              => 'text',
					'required'          => true,
					'wrapperAttributes' => [
						'class' => 'col-md-6 maxmind_api',
					],
				],
			]);
		}
		
		// maxmind.com (Database)
		if (array_key_exists('maxmind_database', $geoipDrivers)) {
			$fields = array_merge($fields, [
				[
					'name'              => 'maxmind_database_info',
					'type'              => 'custom_html',
					'value'             => trans('admin.maxmind_database_info'),
					'wrapperAttributes' => [
						'class' => 'maxmind_database',
					],
				],
				[
					'name'              => 'maxmind_database_license_key',
					'label'             => trans('admin.maxmind_database_license_key_label'),
					'type'              => 'text',
					'wrapperAttributes' => [
						'class' => 'col-md-6 maxmind_database',
					],
				],
			]);
		}
		
		// Country & Region
		$fields = array_merge($fields, [
			[
				'name'  => 'localization_country_region',
				'type'  => 'custom_html',
				'value' => trans('admin.localization_country_region'),
			],
			[
				'name'              => 'default_country_code',
				'label'             => trans('admin.Default Country'),
				'type'              => 'select2',
				'attribute'         => 'name',
				'model'             => '\App\Models\Country',
				'allows_null'       => true,
				'hint'              => trans('admin.default_country_code_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'local_currency_packages_activation',
				'label'             => trans('admin.Allow users to pay the Packages in their country currency'),
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.package_currency_by_country_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
		]);
		
		// Language
		$fields = array_merge($fields, [
			[
				'name'  => 'localization_language',
				'type'  => 'custom_html',
				'value' => trans('admin.localization_language'),
			],
			[
				'name'              => 'show_country_spoken_languages',
				'label'             => trans('admin.show_country_spoken_languages_label'),
				'type'              => 'select2_from_array',
				'options'           => [
					'disabled'         => trans('admin.country_spoken_languages_option_0'),
					'active'           => trans('admin.country_spoken_languages_option_1'),
					'active_with_en'   => trans('admin.country_spoken_languages_option_2'),
					'active_with_main' => trans('admin.country_spoken_languages_option_3'),
				],
				'hint'              => trans('admin.show_country_spoken_languages_hint', [
					'field'    => trans('admin.country_spoken_languages_label'),
					'url'      => admin_url('countries'),
					'option_0' => trans('admin.country_spoken_languages_option_0'),
					'option_1' => trans('admin.country_spoken_languages_option_1'),
					'option_2' => trans('admin.country_spoken_languages_option_2'),
					'option_3' => trans('admin.country_spoken_languages_option_3'),
				]),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
				'newline'           => true,
			],
			
			[
				'name'              => 'auto_detect_language',
				'label'             => trans('admin.auto_detect_language_label'),
				'type'              => 'select2_from_array',
				'options'           => [
					'disabled'     => trans('admin.auto_detect_language_option_0'),
					'from_browser' => trans('admin.auto_detect_language_option_1'),
					'from_country' => trans('admin.auto_detect_language_option_2'),
				],
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'  => 'auto_detect_language_warning_sep',
				'type'  => 'custom_html',
				'value' => trans('admin.auto_detect_language_warning_sep_value'),
			],
		]);
		
		// Front Header UI/UX
		$fields = array_merge($fields, [
			[
				'name'  => 'localization_front_header',
				'type'  => 'custom_html',
				'value' => trans('admin.localization_front_header'),
			],
			[
				'name'              => 'show_country_flag',
				'label'             => trans('admin.show_country_flag_label'),
				'type'              => 'select2_from_array',
				'options'           => [
					'disabled'     => trans('admin.show_country_flag_option_0'),
					'in_next_logo' => trans('admin.show_country_flag_option_1'),
					'in_next_lang' => trans('admin.show_country_flag_option_2'),
				],
				'hint'              => trans('admin.show_country_flag_hint', [
					'option_0' => trans('admin.show_country_flag_option_0'),
					'option_1' => trans('admin.show_country_flag_option_1'),
					'option_2' => trans('admin.show_country_flag_option_2'),
				]),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'country_flag_shape',
				'label'             => trans('admin.country_flag_shape_label'),
				'type'              => 'select2_from_array',
				'options'           => getCountryFlagShapes(),
				'hint'              => trans('admin.country_flag_shape_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'show_languages_flags',
				'label'             => trans('admin.show_languages_flags_label'),
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.show_languages_flags_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
		]);
		
		return addOptionsGroupJavaScript(__NAMESPACE__, __CLASS__, $fields, [
			'geoipDriversSelectorsJson' => $geoipDriversSelectorsJson,
		]);
	}
}
