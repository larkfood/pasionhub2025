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
 * settings.footer.option
 */

class FooterSetting
{
	public static function getValues($value, $disk)
	{
		if (empty($value)) {
			
			$value['hide_payment_plugins_logos'] = '1';
			
		} else {
			
			if (!array_key_exists('hide_payment_plugins_logos', $value)) {
				$value['hide_payment_plugins_logos'] = '1';
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
		$fields = [
			[
				'name'              => 'hide_links',
				'label'             => trans('admin.Hide Links'),
				'type'              => 'checkbox_switch',
				'wrapperAttributes' => [
					'class' => 'col-md-12',
				],
			],
			[
				'name'              => 'hide_payment_plugins_logos',
				'label'             => trans('admin.Hide Payment Plugins Logos'),
				'type'              => 'checkbox_switch',
				'wrapperAttributes' => [
					'class' => 'col-md-12',
				],
			],
			[
				'name'              => 'hide_powered_by',
				'label'             => trans('admin.Hide Powered by Info'),
				'type'              => 'checkbox_switch',
				'wrapperAttributes' => [
					'class' => 'col-md-5 mt-3',
				],
			],
			[
				'name'              => 'powered_by_info',
				'label'             => trans('admin.Powered by'),
				'type'              => 'text',
				'wrapperAttributes' => [
					'class' => 'col-md-7 powered-by-field',
				],
				'newline'           => true,
			],
			[
				'name'              => 'tracking_code',
				'label'             => trans('admin.Tracking Code'),
				'type'              => 'textarea',
				'attributes'        => [
					'rows' => '15',
				],
				'hint'              => trans('admin.tracking_code_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-12',
				],
			],
		];
		
		return addOptionsGroupJavaScript(__NAMESPACE__, __CLASS__, $fields);
	}
}
