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
 * settings.listing_form.option
 */

class ListingFormSetting
{
	public static function getValues($value, $disk)
	{
		if (empty($value)) {
			
			$value['publication_form_type'] = '1';
			$value['city_selection'] = 'modal';
			$value['picture_mandatory'] = '1';
			$value['listings_limit'] = '50';
			$value['pictures_limit'] = '5';
			$value['title_min_length'] = '2';
			$value['title_max_length'] = '150';
			$value['description_min_length'] = '5';
			$value['description_max_length'] = '6000';
			$value['tags_limit'] = '15';
			$value['tags_min_length'] = '2';
			$value['tags_max_length'] = '30';
			$value['guest_can_submit_listings'] = '0';
			$value['permanent_listings_enabled'] = '0';
			$value['default_package_type'] = 'promotion';
			$value['utf8mb4_enabled'] = isUtf8mb4Available() ? '1' : '0';
			$value['allow_emojis'] = '0';
			$value['cat_display_type'] = 'c_bigIcon_list';
			$value['wysiwyg_editor'] = 'tinymce';
			$value['auto_registration'] = '0';
			
		} else {
			
			if (!array_key_exists('publication_form_type', $value)) {
				$value['publication_form_type'] = '1';
			}
			if (!array_key_exists('city_selection', $value)) {
				$value['city_selection'] = 'modal';
			}
			if (!array_key_exists('picture_mandatory', $value)) {
				$value['picture_mandatory'] = '1';
			}
			if (!array_key_exists('listings_limit', $value)) {
				$value['listings_limit'] = '50';
			}
			if (!array_key_exists('pictures_limit', $value)) {
				$value['pictures_limit'] = '5';
			}
			if (!array_key_exists('title_min_length', $value)) {
				$value['title_min_length'] = '2';
			}
			if (!array_key_exists('title_max_length', $value)) {
				$value['title_max_length'] = '150';
			}
			if (!array_key_exists('description_min_length', $value)) {
				$value['description_min_length'] = '5';
			}
			if (!array_key_exists('description_max_length', $value)) {
				$value['description_max_length'] = '6000';
			}
			if (!array_key_exists('tags_limit', $value)) {
				$value['tags_limit'] = '15';
			}
			if (!array_key_exists('tags_min_length', $value)) {
				$value['tags_min_length'] = '2';
			}
			if (!array_key_exists('tags_max_length', $value)) {
				$value['tags_max_length'] = '30';
			}
			if (!array_key_exists('guest_can_submit_listings', $value)) {
				$value['guest_can_submit_listings'] = '0';
			}
			if (!array_key_exists('permanent_listings_enabled', $value)) {
				$value['permanent_listings_enabled'] = '0';
			}
			if (!array_key_exists('default_package_type', $value)) {
				$value['default_package_type'] = 'promotion';
			}
			if (!array_key_exists('utf8mb4_enabled', $value)) {
				$value['utf8mb4_enabled'] = isUtf8mb4Available() ? '1' : '0';
			}
			if (!array_key_exists('allow_emojis', $value)) {
				$value['allow_emojis'] = '0';
			}
			if (!array_key_exists('cat_display_type', $value)) {
				$value['cat_display_type'] = 'c_bigIcon_list';
			}
			if (!array_key_exists('wysiwyg_editor', $value)) {
				$value['wysiwyg_editor'] = 'tinymce';
			}
			if (!array_key_exists('auto_registration', $value)) {
				$value['auto_registration'] = '0';
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
				'name'  => 'general_separator_title',
				'type'  => 'custom_html',
				'value' => trans('admin.general_separator_title'),
			],
			[
				'name'              => 'publication_form_type',
				'label'             => trans('admin.publication_form_type_label'),
				'type'              => 'select2_from_array',
				'options'           => [
					1 => trans('admin.publication_form_type_option_1'),
					2 => trans('admin.publication_form_type_option_2'),
				],
				'hint'              => trans('admin.publication_form_type_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'city_selection',
				'label'             => trans('admin.city_selection_label'),
				'type'              => 'select2_from_array',
				'options'           => [
					'select' => trans('admin.city_selection_option_1'),
					'modal'  => trans('admin.city_selection_option_2'),
				],
				'hint'              => trans('admin.city_selection_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
				'newline'           => true,
			],
			
			[
				'name'              => 'title_min_length',
				'label'             => trans('admin.title_min_length_label'),
				'type'              => 'number',
				'attributes'        => [
					'min'  => 0,
					'max'  => 255,
					'step' => 1,
				],
				'hint'              => trans('admin.title_min_length_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-3',
				],
			],
			[
				'name'              => 'title_max_length',
				'label'             => trans('admin.title_max_length_label'),
				'type'              => 'number',
				'attributes'        => [
					'min'  => 1,
					'max'  => 255,
					'step' => 1,
				],
				'hint'              => trans('admin.title_max_length_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-3',
				],
			],
			[
				'name'              => 'description_min_length',
				'label'             => trans('admin.description_min_length_label'),
				'type'              => 'number',
				'attributes'        => [
					'min'  => 0,
					'max'  => 16777215,
					'step' => 1,
				],
				'hint'              => trans('admin.description_min_length_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-3',
				],
			],
			[
				'name'              => 'description_max_length',
				'label'             => trans('admin.description_max_length_label'),
				'type'              => 'number',
				'attributes'        => [
					'min'  => 1,
					'max'  => 16777215,
					'step' => 1,
				],
				'hint'              => trans('admin.description_max_length_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-3',
				],
			],
			[
				'name'              => 'price_mandatory',
				'label'             => trans('admin.price_mandatory_label'),
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.price_mandatory_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'picture_mandatory',
				'label'             => trans('admin.picture_mandatory_label'),
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.picture_mandatory_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'listings_limit',
				'label'             => trans('admin.listings_limit_label'),
				'type'              => 'number',
				'hint'              => trans('admin.listings_limit_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'pictures_limit',
				'label'             => trans('admin.pictures_limit_label'),
				'type'              => 'number',
				'hint'              => trans('admin.pictures_limit_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'tags_limit',
				'label'             => trans('admin.tags_limit_label'),
				'type'              => 'number',
				'hint'              => trans('admin.tags_limit_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'tags_min_length',
				'label'             => trans('admin.tags_min_length_label'),
				'type'              => 'number',
				'attributes'        => [
					'min'  => 0,
					'max'  => 16777215,
					'step' => 1,
				],
				'hint'              => trans('admin.tags_min_length_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-3',
				],
			],
			[
				'name'              => 'tags_max_length',
				'label'             => trans('admin.tags_max_length_label'),
				'type'              => 'number',
				'attributes'        => [
					'min'  => 1,
					'max'  => 16777215,
					'step' => 1,
				],
				'hint'              => trans('admin.tags_max_length_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-3',
				],
				'newline'           => true,
			],
			
			[
				'name'              => 'guest_can_submit_listings',
				'label'             => trans('admin.Allow Guests to post Listings'),
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.guest_can_submit_listings_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'listings_review_activation',
				'label'             => trans('admin.Allow listings to be reviewed by Admins'),
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.listings_review_activation_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'permanent_listings_enabled',
				'label'             => trans('admin.permanent_listings_label'),
				'type'              => 'select2_from_array',
				'options'           => [
					0 => trans('admin.permanent_listings_option_0'),
					4 => trans('admin.permanent_listings_option_4'),
				],
				'hint'              => trans('admin.permanent_listings_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
				'newline'           => true,
			],
			
			[
				'name'              => 'pricing_page_enabled',
				'label'             => trans('admin.pricing_page_label'),
				'type'              => 'select2_from_array',
				'options'           => [
					0 => trans('admin.pricing_page_option_0'),
					1 => trans('admin.pricing_page_option_1'),
					2 => trans('admin.pricing_page_option_2'),
				],
				'hint'              => trans('admin.card_light_inverse', ['content' => trans('admin.pricing_page_hint')]),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'default_package_type',
				'label'             => trans('admin.default_package_type_label'),
				'type'              => 'select2_from_array',
				'options'           => [
					'promotion'    => mb_ucfirst(trans('admin.promotion')),
					'subscription' => mb_ucfirst(trans('admin.subscription')),
				],
				'hint'              => trans('admin.default_package_type_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
		];
		
		if (isUtf8mb4Available()) {
			$fields[] = [
				'name'              => 'utf8mb4_enabled',
				'label'             => trans('admin.utf8mb4_enabled_label'),
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.utf8mb4_enabled_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			];
			$fields[] = [
				'name'              => 'allow_emojis',
				'label'             => trans('admin.allow_emojis_label'),
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.allow_emojis_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6 utf8mb4-field',
				],
				'newline'           => true,
			];
		}
		
		$fields = array_merge($fields, [
			[
				'name'              => 'show_listing_type',
				'label'             => trans('admin.show_listing_type_label'),
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.show_listing_type_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'enable_post_uniqueness',
				'label'             => trans('admin.enable_post_uniqueness_label'),
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.enable_post_uniqueness_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			
			[
				'name'  => 'form_cat_selection_title',
				'type'  => 'custom_html',
				'value' => trans('admin.form_cat_selection_title'),
			],
			[
				'name'              => 'cat_display_type',
				'label'             => trans('admin.form_cat_display_type_label'),
				'type'              => 'select2_from_array',
				'options'           => [
					'c_normal_list'  => trans('admin.cat_display_type_op_1'),
					'c_border_list'  => trans('admin.cat_display_type_op_2'),
					'c_bigIcon_list' => trans('admin.cat_display_type_op_3'),
					'c_picture_list' => trans('admin.cat_display_type_op_4'),
				],
				'allows_null'       => false,
				'hint'              => trans('admin.form_cat_display_type_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			
			[
				'name'  => 'wysiwyg_editor_title',
				'type'  => 'custom_html',
				'value' => trans('admin.wysiwyg_editor_title_value'),
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
				'name'  => 'remove_url_title',
				'type'  => 'custom_html',
				'value' => trans('admin.remove_url_title_value'),
			],
			[
				'name'              => 'remove_url_before',
				'label'             => trans('admin.remove_element_before_label'),
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.remove_element_before_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'remove_url_after',
				'label'             => trans('admin.remove_element_after_label'),
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.remove_element_after_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'  => 'remove_email_title',
				'type'  => 'custom_html',
				'value' => trans('admin.remove_email_title_value'),
			],
			[
				'name'              => 'remove_email_before',
				'label'             => trans('admin.remove_element_before_label'),
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.remove_element_before_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'remove_email_after',
				'label'             => trans('admin.remove_element_after_label'),
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.remove_element_after_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'  => 'remove_phone_title',
				'type'  => 'custom_html',
				'value' => trans('admin.remove_phone_title_value'),
			],
			[
				'name'              => 'remove_phone_before',
				'label'             => trans('admin.remove_element_before_label'),
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.remove_element_before_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'remove_phone_after',
				'label'             => trans('admin.remove_element_after_label'),
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.remove_element_after_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			
			[
				'name'  => 'auto_registration_sep',
				'type'  => 'custom_html',
				'value' => trans('admin.auto_registration_sep_value'),
			],
			[
				'name'              => 'auto_registration',
				'label'             => trans('admin.auto_registration_label'),
				'type'              => 'select2_from_array',
				'options'           => [
					0 => trans('admin.auto_registration_option_0'), // 0 => 'disabled'
					1 => trans('admin.auto_registration_option_1'), // 1 => 'enabled_shown'
					2 => trans('admin.auto_registration_option_2'), // 2 => 'enabled_hidden'
				],
				'hint'              => trans('admin.auto_registration_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
		]);
		
		return addOptionsGroupJavaScript(__NAMESPACE__, __CLASS__, $fields);
	}
}
