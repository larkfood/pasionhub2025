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

namespace App\Observers\Traits\Setting;

trait DomainmappingTrait
{
	/**
	 * Updating (domainmapping)
	 *
	 * @param $setting
	 * @param $original
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	public function domainmappingUpdating($setting, $original)
	{
		if (!isset($setting->key)) return;
		
		if ($setting->key == 'domainmapping') {
			// Check if the session sharing field has changed & Update the /.env file
			if (array_key_exists('share_session', $setting->value)) {
				if (
					empty($original['value'])
					|| (
						is_array($original['value'])
						&& !isset($original['value']['share_session'])
					)
					|| (
						is_array($original['value'])
						&& isset($original['value']['share_session'])
						&& $setting->value['share_session'] != $original['value']['share_session']
					)
				) {
					$this->updateEnvFileForSessionSharing($setting);
				}
			}
		}
	}
	
	/**
	 * Update the /.env file to apply the session sharing rules
	 * The admin user will be log out automatically.
	 *
	 * @param $setting
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	private function updateEnvFileForSessionSharing($setting): void
	{
		// Check the Domain Mapping plugin
		if (config('plugins.domainmapping.installed')) {
			if (isset($setting->value['share_session'])) {
				config()->set('settings.domainmapping.share_session', $setting->value['share_session']);
				
				// Log out the admin user
				\extras\plugins\domainmapping\Domainmapping::logout();
				
				// Update the /.env file to meet the plugin installation requirements
				\extras\plugins\domainmapping\Domainmapping::updateEnvFile(true);
			}
		}
	}
}
