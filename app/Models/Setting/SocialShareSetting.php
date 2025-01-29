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

/*
 * settings.social_share.option
 */

class SocialShareSetting
{
	public static function passedValidation($request)
	{
		$params = [
			[
				'attribute' => 'og_image',
				'destPath'  => 'app/logo',
				'width'     => (int)config('larapen.media.resize.namedOptions.bg-header.width', 2000),
				'height'    => (int)config('larapen.media.resize.namedOptions.bg-header.height', 1000),
				'ratio'     => config('larapen.media.resize.namedOptions.bg-header.ratio', '1'),
				'upsize'    => config('larapen.media.resize.namedOptions.bg-header.upsize', '0'),
				'filename'  => 'og-',
				'quality'   => 100,
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
			
			$value['facebook'] = '1';
			$value['twitter'] = '1';
			$value['linkedin'] = '1';
			$value['whatsapp'] = '1';
			$value['telegram'] = '1';
			$value['snapchat'] = '0';
			$value['messenger'] = '0';
			$value['pinterest'] = '0';
			$value['vk'] = '0';
			$value['tumblr'] = '0';
			$value['og_image_width'] = config('settings.seo.og_image_width', '1200');
			$value['og_image_height'] = config('settings.seo.og_image_height', '630');
			
		} else {
			
			if (!array_key_exists('facebook', $value)) {
				$value['facebook'] = '1';
			}
			if (!array_key_exists('twitter', $value)) {
				$value['twitter'] = '1';
			}
			if (!array_key_exists('linkedin', $value)) {
				$value['linkedin'] = '1';
			}
			if (!array_key_exists('whatsapp', $value)) {
				$value['whatsapp'] = '1';
			}
			if (!array_key_exists('telegram', $value)) {
				$value['telegram'] = '1';
			}
			if (!array_key_exists('snapchat', $value)) {
				$value['snapchat'] = '0';
			}
			if (!array_key_exists('messenger', $value)) {
				$value['messenger'] = '0';
			}
			if (!array_key_exists('pinterest', $value)) {
				$value['pinterest'] = '0';
			}
			if (!array_key_exists('vk', $value)) {
				$value['vk'] = '0';
			}
			if (!array_key_exists('tumblr', $value)) {
				$value['tumblr'] = '0';
			}
			if (!array_key_exists('og_image_width', $value)) {
				$value['og_image_width'] = config('settings.seo.og_image_width', '1200');
			}
			if (!array_key_exists('og_image_height', $value)) {
				$value['og_image_height'] = config('settings.seo.og_image_height', '630');
			}
			
		}
		
		// Append files URLs
		// og_image_url
		$ogImage = $value['og_image'] ?? config('settings.seo.og_image');
		$ogImageWidth = !empty($value['og_image_width']) ? (int)$value['og_image_width'] : 0;
		$ogImageHeight = !empty($value['og_image_height']) ? (int)$value['og_image_height'] : 0;
		$resizeOptionsName = ($ogImageWidth > 0 && $ogImageHeight > 0) ? ($ogImageWidth . 'x' . $ogImageHeight) : 'bg-header';
		$value['og_image_url'] = thumbService($ogImage, false)->resize($resizeOptionsName)->url();
		
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
				'name'  => 'social_share_title',
				'type'  => 'custom_html',
				'value' => trans('admin.social_share_title'),
			],
			[
				'name'  => 'social_share_info',
				'type'  => 'custom_html',
				'value' => trans('admin.social_share_info'),
			],
		]);
		
		$fields = array_merge($fields, [
			[
				'name'              => 'facebook',
				'label'             => 'Facebook',
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.facebook_share_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-12',
				],
			],
		]);
		
		$fields = array_merge($fields, [
			[
				'name'              => 'twitter',
				'label'             => 'X (Twitter)',
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.twitter_share_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-12',
				],
			],
		]);
		
		$fields = array_merge($fields, [
			[
				'name'              => 'linkedin',
				'label'             => 'LinkedIn',
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.linkedin_share_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-12',
				],
			],
		]);
		
		$fields = array_merge($fields, [
			[
				'name'              => 'whatsapp',
				'label'             => 'WhatsApp',
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.whatsapp_share_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-12',
				],
			],
		]);
		
		$fields = array_merge($fields, [
			[
				'name'              => 'telegram',
				'label'             => 'Telegram',
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.telegram_share_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-12',
				],
			],
		]);
		
		$fields = array_merge($fields, [
			[
				'name'              => 'snapchat',
				'label'             => 'Snapchat',
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.snapchat_share_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-12',
				],
			],
		]);
		
		$fields = array_merge($fields, [
			[
				'name'              => 'messenger',
				'label'             => 'Facebook Messenger',
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.messenger_share_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'facebook_app_id',
				'label'             => trans('admin.facebook_app_id'),
				'type'              => 'text',
				'hint'              => trans('admin.facebook_app_id_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6 messenger',
				],
			],
		]);
		
		$fields = array_merge($fields, [
			[
				'name'              => 'pinterest',
				'label'             => 'Pinterest',
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.pinterest_share_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-12',
				],
			],
		]);
		
		$fields = array_merge($fields, [
			[
				'name'              => 'vk',
				'label'             => 'VK (VKontakte)',
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.vk_share_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-12',
				],
			],
		]);
		
		$fields = array_merge($fields, [
			[
				'name'              => 'tumblr',
				'label'             => 'Tumblr',
				'type'              => 'checkbox_switch',
				'hint'              => trans('admin.tumblr_share_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-12',
				],
			],
		]);
		
		$fields = array_merge($fields, [
			[
				'name'  => 'og_image_title',
				'type'  => 'custom_html',
				'value' => trans('admin.og_image_title'),
			],
			[
				'name'  => 'og_image_info',
				'type'  => 'custom_html',
				'value' => trans('admin.og_image_info'),
			],
			[
				'name'   => 'og_image',
				'label'  => trans('admin.og_image_label'),
				'type'   => 'image',
				'upload' => true,
				'disk'   => $diskName,
				'hint'   => trans('admin.og_image_hint'),
			],
			[
				'name'              => 'og_image_width',
				'label'             => trans('admin.width_label') . ' (' . trans('admin.og_image_label') . ')',
				'type'              => 'number',
				'attributes'        => [
					'placeholder' => '1200',
				],
				'hint'              => trans('admin.width_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'              => 'og_image_height',
				'label'             => trans('admin.height_label') . ' (' . trans('admin.og_image_label') . ')',
				'type'              => 'number',
				'attributes'        => [
					'placeholder' => '630',
				],
				'hint'              => trans('admin.height_hint'),
				'wrapperAttributes' => [
					'class' => 'col-md-6',
				],
			],
		]);
		
		return addOptionsGroupJavaScript(__NAMESPACE__, __CLASS__, $fields);
	}
}
