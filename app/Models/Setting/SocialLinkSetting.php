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
 * settings.social_link.option
 */

class SocialLinkSetting
{
	public static function getValues($value, $disk)
	{
		if (empty($value)) {
			
			$value['facebook_page_url'] = '#';
			$value['twitter_url'] = '#';
			$value['linkedin_url'] = '#';
			$value['pinterest_url'] = '#';
			$value['instagram_url'] = '#';
			$value['tiktok_url'] = '#';
			$value['youtube_url'] = '#';
			$value['vimeo_url'] = '#';
			$value['vk_url'] = '#';
			$value['tumblr_url'] = '';
			$value['flickr_url'] = '';
			
		} else {
			
			if (!array_key_exists('facebook_page_url', $value)) {
				$value['facebook_page_url'] = '#';
			}
			if (!array_key_exists('twitter_url', $value)) {
				$value['twitter_url'] = '#';
			}
			if (!array_key_exists('linkedin_url', $value)) {
				$value['linkedin_url'] = '#';
			}
			if (!array_key_exists('pinterest_url', $value)) {
				$value['pinterest_url'] = '#';
			}
			if (!array_key_exists('instagram_url', $value)) {
				$value['instagram_url'] = '#';
			}
			if (!array_key_exists('tiktok_url', $value)) {
				$value['tiktok_url'] = '#';
			}
			if (!array_key_exists('youtube_url', $value)) {
				$value['youtube_url'] = '#';
			}
			if (!array_key_exists('vimeo_url', $value)) {
				$value['vimeo_url'] = '#';
			}
			if (!array_key_exists('vk_url', $value)) {
				$value['vk_url'] = '#';
			}
			if (!array_key_exists('tumblr_url', $value)) {
				$value['tumblr_url'] = '';
			}
			if (!array_key_exists('flickr_url', $value)) {
				$value['flickr_url'] = '';
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
				'name'  => 'social_link_info',
				'type'  => 'custom_html',
				'value' => trans('admin.social_link_info'),
			],
			[
				'name'  => 'facebook_page_url',
				'label' => trans('admin.facebook_page_url'),
				'type'  => 'text',
			],
			[
				'name'  => 'twitter_url',
				'label' => trans('admin.twitter_url'),
				'type'  => 'text',
			],
			[
				'name'  => 'linkedin_url',
				'label' => trans('admin.linkedin_url'),
				'type'  => 'text',
			],
			[
				'name'  => 'pinterest_url',
				'label' => trans('admin.pinterest_url'),
				'type'  => 'text',
			],
			[
				'name'  => 'instagram_url',
				'label' => trans('admin.instagram_url'),
				'type'  => 'text',
			],
			[
				'name'  => 'tiktok_url',
				'label' => trans('admin.tiktok_url'),
				'type'  => 'text',
			],
			[
				'name'  => 'youtube_url',
				'label' => trans('admin.youtube_url'),
				'type'  => 'text',
			],
			[
				'name'  => 'vimeo_url',
				'label' => trans('admin.vimeo_url'),
				'type'  => 'text',
			],
			[
				'name'  => 'vk_url',
				'label' => trans('admin.vk_url'),
				'type'  => 'text',
			],
			[
				'name'  => 'tumblr_url',
				'label' => trans('admin.tumblr_url'),
				'type'  => 'text',
			],
			[
				'name'  => 'flickr_url',
				'label' => trans('admin.flickr_url'),
				'type'  => 'text',
			],
		];
		
		return $fields;
	}
}
