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

namespace App\Providers\PluginsService;

use App\Helpers\Arr;

trait PluginsTrait
{
	/**
	 * Load all the installed plugins
	 *
	 * @return void
	 */
	private function loadPlugins(): void
	{
		$plugins = plugin_installed_list();
		$plugins = collect($plugins)
			->map(function ($item) {
				if (is_object($item)) {
					$item = Arr::fromObject($item);
				}
				if (!empty($item['item_id'])) {
					$item['installed'] = plugin_check_purchase_code($item);
				}
				
				return $item;
			})->toArray();
		
		config()->set('plugins', $plugins);
		config()->set('plugins.installed', collect($plugins)->whereStrict('installed', true)->toArray());
	}
}
