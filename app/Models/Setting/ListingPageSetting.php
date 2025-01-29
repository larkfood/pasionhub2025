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
 * settings.listing_page.option
 */

class ListingPageSetting
{
	public static function getValues($value, $disk)
	{
		if (empty($value)) {
			
			$value['guest_can_contact_authors'] = '0';
			$value['pictures_slider'] = 'swiper-horizontal';
			$value['similar_listings'] = '1';
			$value['similar_listings_in_carousel'] = '1';
			
		} else {
			
			if (!array_key_exists('guest_can_contact_authors', $value)) {
				$value['guest_can_contact_authors'] = '0';
			}
			if (!array_key_exists('pictures_slider', $value)) {
				$value['pictures_slider'] = 'swiper-horizontal';
			}
			if (!array_key_exists('similar_listings', $value)) {
				$value['similar_listings'] = '1';
			}
			if (!array_key_exists('similar_listings_in_carousel', $value)) {
				$value['similar_listings_in_carousel'] = '1';
			}
			if (!array_key_exists('date_from_now', $value)) {
				if (array_key_exists('elapsed_time_from_now', $value)) {
					$value['date_from_now'] = $value['elapsed_time_from_now'];
				}
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
		$fields = [];
		
		$fields = array_merge($fields, [
			[
				'name'  => 'around_phone_number_sep',
				'type'  => 'custom_html',
				'value' => trans('admin.around_phone_number_sep'),
			],
			[
				'name'              => 'show_security_tips',
				'label'             => trans('admin.show_security_tips_label'),
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.show_security_tips_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
				'newline'           => true,
			],
			
			[
				'name'              => 'enable_whatsapp_btn',
				'label'             => trans('admin.enable_whatsapp_btn_label'),
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.enable_whatsapp_btn_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'pre_filled_whatsapp_message',
				'label'             => trans('admin.pre_filled_whatsapp_message_label'),
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.pre_filled_whatsapp_message_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6 whatsapp-btn-field',
				],
			],
		]);
		
		$fields = array_merge($fields, [
			[
				'name'  => 'phone_number_sep',
				'type'  => 'custom_html',
				'value' => trans('admin.phone_number_sep_value'),
			],
			[
				'name'              => 'convert_phone_number_to_img',
				'label'             => trans('admin.convert_phone_number_to_img_label'),
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.convert_phone_number_to_img_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6 security-tips-field',
				],
			],
			[
				'name'              => 'hide_phone_number',
				'label'             => trans('admin.hide_phone_number_label'),
				'type'              => 'select2_from_array',
				'options'           => [
					0 => trans('admin.hide_phone_number_option_0'),
					1 => trans('admin.hide_phone_number_option_1'),
					2 => trans('admin.hide_phone_number_option_2'),
					3 => trans('admin.hide_phone_number_option_3'),
				],
				'hint'              => trans('admin.hide_phone_number_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
		]);
		
		$fields = array_merge($fields, [
			[
				'name'  => 'dates_sep',
				'type'  => 'custom_html',
				'value' => trans('admin.dates_title'),
			],
			[
				'name'              => 'hide_date',
				'label'             => trans('admin.hide_date_label'),
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.details_hide_date_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'php_specific_date_format',
				'type'              => 'custom_html',
				'value'             => trans('admin.php_specific_date_format_info'),
				'wrapperAttributes' => [
					'class' => 'col-md-12 date-field',
				],
			],
			[
				'name'              => 'date_from_now',
				'label'             => trans('admin.date_from_now_label'),
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.details_date_from_now_hint', [
					'app'          => trans('settings.app'),
					'appUrl'       => admin_url('settings/find/app'),
					'languagesUrl' => admin_url('languages'),
				]),
				'wrapperAttributes' => [
					'class' => 'col-md-12 date-field',
				],
			],
		]);
		
		$fields = array_merge($fields, [
			[
				'name'  => 'others_sep',
				'type'  => 'custom_html',
				'value' => trans('admin.others_sep_value'),
			],
			[
				'name'              => 'pictures_slider',
				'label'             => trans('admin.pictures_slider_label'),
				'type'              => 'select2_from_array',
				'options'           => [
					'bootstrap-carousel'  => trans('admin.pictures_slider_option_0'),
					'swiper-horizontal'   => trans('admin.pictures_slider_option_1'),
					'swiper-vertical'     => trans('admin.pictures_slider_option_2'),
					'bxslider-horizontal' => trans('admin.pictures_slider_option_3'),
					'bxslider-vertical'   => trans('admin.pictures_slider_option_4'),
				],
				'hint'              => trans('admin.pictures_slider_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
				'newline'           => true,
			],
			
			[
				'name'              => 'similar_listings',
				'label'             => trans('admin.similar_listings_label'),
				'type'              => 'select2_from_array',
				'options'           => [
					0 => trans('admin.similar_listings_option_0'),
					1 => trans('admin.similar_listings_option_1'),
					2 => trans('admin.similar_listings_option_2'),
				],
				'hint'              => trans('admin.similar_listings_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'similar_listings_in_carousel',
				'label'             => trans('admin.similar_listings_in_carousel_label'),
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.similar_listings_in_carousel_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6 mt-3 similar-listings-field',
				],
				'newline'           => true,
			],
			[
				'name'              => 'guest_can_contact_authors',
				'label'             => trans('admin.guest_can_contact_authors_label'),
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.guest_can_contact_authors_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'auth_required_to_report_abuse',
				'label'             => trans('admin.auth_required_to_report_abuse_label'),
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.auth_required_to_report_abuse_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
		]);
		
		$fields = array_merge($fields, [
			[
				'name'  => 'external_services_sep',
				'type'  => 'custom_html',
				'value' => trans('admin.single_html_external_services'),
			],
			[
				'name'              => 'show_listing_on_googlemap',
				'label'             => trans('admin.Show Listings on Google Maps'),
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.show_listings_on_google_maps_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'activation_facebook_comments',
				'label'             => trans('admin.Allow Facebook Comments'),
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.allow_facebook_comments_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
		]);
		
		return addOptionsGroupJavaScript(__NAMESPACE__, __CLASS__, $fields);
	}
}
