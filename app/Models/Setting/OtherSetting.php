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
 * settings.other.option
 */

class OtherSetting
{
	public static function getValues($value, $disk)
	{
		if (empty($value)) {
			
			$value['account_closure_enabled'] = '1';
			$value['cookie_consent_enabled'] = '0';
			$value['show_tips_messages'] = '1';
			$value['timer_new_messages_checking'] = 60000;
			$value['wysiwyg_editor'] = 'tinymce';
			$value['cookie_expiration'] = 1440;
			
		} else {
			
			if (!array_key_exists('account_closure_enabled', $value)) {
				$value['account_closure_enabled'] = '1';
			}
			if (!array_key_exists('cookie_consent_enabled', $value)) {
				$value['cookie_consent_enabled'] = '0';
			}
			if (!array_key_exists('show_tips_messages', $value)) {
				$value['show_tips_messages'] = '1';
			}
			if (!array_key_exists('timer_new_messages_checking', $value)) {
				$value['timer_new_messages_checking'] = 60000;
			}
			if (!array_key_exists('wysiwyg_editor', $value)) {
				$value['wysiwyg_editor'] = 'tinymce';
			}
			if (!array_key_exists('cookie_expiration', $value)) {
				$value['cookie_expiration'] = 1440;
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
		$wysiwygEditors = (array)config('larapen.options.wysiwyg');
		
		$fields = [
			[
				'name'  => 'account_option_title',
				'type'  => 'custom_html',
				'value' => trans('admin.account_option_title'),
			],
			[
				'name'              => 'account_closure_enabled',
				'label'             => trans('admin.account_closure_enabled_label'),
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.account_closure_enabled_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			
			[
				'name'  => 'separator_1',
				'type'  => 'custom_html',
				'value' => trans('admin.other_html_alerts_boxes'),
			],
			[
				'name'              => 'cookie_consent_enabled',
				'label'             => trans('admin.Cookie Consent Enabled'),
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.Enable Cookie Consent Alert to comply for EU law'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'show_tips_messages',
				'label'             => trans('admin.Show Tips Notification Messages'),
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.show_tips_messages_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'  => 'separator_2',
				'type'  => 'custom_html',
				'value' => trans('admin.other_html_google_maps'),
			],
			[
				'name'              => 'googlemaps_key',
				'label'             => trans('admin.Google Maps Key'),
				'type'              => 'text',
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'  => 'separator_3',
				'type'  => 'custom_html',
				'value' => trans('admin.other_html_messenger'),
			],
			[
				'name'              => 'timer_new_messages_checking',
				'label'             => trans('admin.Timer for New Messages Checking'),
				'type'              => 'number',
				'attributes'        => [
					'min'      => 0,
					'step'     => 2000,
					'required' => true,
				],
				'hint'              => trans('admin.timer_new_messages_checking_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'  => 'separator_4',
				'type'  => 'custom_html',
				'value' => trans('admin.textarea_editor_h3'),
			],
			[
				'name'              => 'wysiwyg_editor',
				'label'             => trans('admin.wysiwyg_editor_label'),
				'type'              => 'select2_from_array',
				'options'           => $wysiwygEditors,
				'hint'              => trans('admin.wysiwyg_editor_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'  => 'separator_5',
				'type'  => 'custom_html',
				'value' => trans('admin.other_html_mobile_app'),
			],
			[
				'name'              => 'ios_app_url',
				'label'             => trans('admin.App Store'),
				'type'              => 'text',
				'hint'              => trans('admin.Available on the App Store with the given URL'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'android_app_url',
				'label'             => trans('admin.Google Play'),
				'type'              => 'text',
				'hint'              => trans('admin.Available on Google Play with the given URL'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'  => 'separator_6',
				'type'  => 'custom_html',
				'value' => trans('admin.other_html_number_format'),
			],
			[
				'name'  => 'decimals_superscript',
				'label' => trans('admin.Decimals Superscript'),
				'type'  => 'checkbox_switch',
			],
			[
				'name'  => 'cookie_sep',
				'type'  => 'custom_html',
				'value' => trans('admin.cookie_sep_value'),
			],
			[
				'name'              => 'cookie_expiration',
				'label'             => trans('admin.cookie_expiration_label'),
				'type'              => 'number',
				'hint'              => trans('admin.cookie_expiration_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'  => 'separator_8',
				'type'  => 'custom_html',
				'value' => trans('admin.other_html_head_js'),
			],
			[
				'name'       => 'js_code',
				'label'      => trans('admin.JavaScript Code'),
				'type'       => 'textarea',
				'attributes' => [
					'rows' => '10',
				],
				'hint'       => trans('admin.js_code_hint'),
			],
		];
		
		return $fields;
	}
}
