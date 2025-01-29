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

namespace App\Http\Controllers\Web\Admin;

use App\Helpers\Arr;
use App\Http\Requests\Admin\PluginRequest;

class PluginController extends Controller
{
	private array $data = [];
	
	public function __construct()
	{
		parent::__construct();
		
		$this->data['plugins'] = [];
	}
	
	/**
	 * List all plugins
	 */
	public function index()
	{
		$plugins = [];
		
		try {
			
			// Load all the plugins' services providers
			$plugins = plugin_list();
			
			// Append the Plugin Options
			$plugins = collect($plugins)
				->map(function ($item) {
					try {
						
						$item = is_object($item) ? Arr::fromObject($item) : $item;
						
						// Append formatted name
						$name = $item['name'] ?? null;
						$displayName = $item['display_name'] ?? null;
						$item['formatted_name'] = $displayName . plugin_demo_info($name);
						
						if (!empty($item['item_id'])) {
							$item['activated'] = plugin_check_purchase_code($item);
						}
						
						// Append the Options
						$item['options'] = null;
						if ($item['is_compatible']) {
							$pluginClass = plugin_namespace($item['name'], ucfirst($item['name']));
							$item['options'] = method_exists($pluginClass, 'getOptions')
								? (array)call_user_func($pluginClass . '::getOptions')
								: null;
						}
						
					} catch (\Throwable $e) {
						$message = $e->getMessage();
						if (!empty($message)) {
							notification($message, 'error');
						}
					}
					
					return Arr::toObject($item);
				})->toArray();
			
		} catch (\Throwable $e) {
			$message = $e->getMessage();
			if (!empty($message)) {
				notification($message, 'error');
			}
		}
		
		$this->data['plugins'] = $plugins;
		$this->data['title'] = 'Plugins';
		
		return view('admin.plugins', $this->data);
	}
	
	/**
	 * Install a plugin (with purchase code)
	 *
	 * @param $name
	 * @param \App\Http\Requests\Admin\PluginRequest $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function installWithCode($name, PluginRequest $request): \Illuminate\Http\RedirectResponse
	{
		$pluginListUrl = admin_url('plugins');
		
		// Get plugin details
		$plugin = load_plugin($name);
		if (empty($plugin)) {
			return redirect()->to($pluginListUrl);
		}
		
		// Check if the plugin is compatible with the core app
		if (!$plugin->is_compatible) {
			notification($plugin->compatibility_hint, 'error');
			
			return redirect()->to($pluginListUrl);
		}
		
		// Install the plugin
		$res = call_user_func($plugin->class . '::installed');
		if (!$res) {
			$res = call_user_func($plugin->class . '::install');
		}
		
		if ($res) {
			$message = trans('admin.plugin_installed_successfully', ['plugin_name' => $plugin->display_name]);
			notification($message, 'success');
		} else {
			$message = trans('admin.plugin_installation_failed', ['plugin_name' => $plugin->display_name]);
			notification($message, 'error');
		}
		
		return redirect()->to($pluginListUrl);
	}
	
	/**
	 * Install a plugin (without purchase code)
	 *
	 * @param $name
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function installWithoutCode($name): \Illuminate\Http\RedirectResponse
	{
		$pluginListUrl = admin_url('plugins');
		
		// Get plugin details
		$plugin = load_plugin($name);
		if (empty($plugin)) {
			return redirect()->to($pluginListUrl);
		}
		
		// Check if the plugin is compatible with the core app
		if (!$plugin->is_compatible) {
			notification($plugin->compatibility_hint, 'error');
			
			return redirect()->to($pluginListUrl);
		}
		
		// Install the plugin
		$res = call_user_func($plugin->class . '::install');
		
		if ($res) {
			$message = trans('admin.plugin_installed_successfully', ['plugin_name' => $plugin->display_name]);
			notification($message, 'success');
		} else {
			$message = trans('admin.plugin_installation_failed', ['plugin_name' => $plugin->display_name]);
			notification($message, 'error');
		}
		
		return redirect()->to($pluginListUrl);
	}
	
	/**
	 * Uninstall a plugin
	 *
	 * @param $name
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function uninstall($name): \Illuminate\Http\RedirectResponse
	{
		$pluginListUrl = admin_url('plugins');
		
		// Get plugin details
		$plugin = load_plugin($name);
		if (empty($plugin)) {
			return redirect()->to($pluginListUrl);
		}
		
		// Check if the plugin is compatible with the core app
		if (!$plugin->is_compatible) {
			notification($plugin->compatibility_hint, 'error');
			
			return redirect()->to($pluginListUrl);
		}
		
		// Uninstall the plugin
		$res = call_user_func($plugin->class . '::uninstall');
		
		// Result Notification
		if ($res) {
			plugin_clear_uninstall($name);
			
			$message = trans('admin.plugin_uninstalled_successfully', ['plugin_name' => $plugin->display_name]);
			notification($message, 'success');
		} else {
			$message = trans('admin.plugin_uninstallation_failed', ['plugin_name' => $plugin->display_name]);
			notification($message, 'error');
		}
		
		return redirect()->to($pluginListUrl);
	}
	
	/**
	 * Delete a plugin
	 *
	 * @param $plugin
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function delete($plugin): \Illuminate\Http\RedirectResponse
	{
		$pluginListUrl = admin_url('plugins');
		
		// ...
		// notification(trans('admin.plugin_removed_successfully'), 'success');
		// notification(trans('admin.plugin_removal_failed', ['plugin_name' => $plugin]), 'error');
		
		return redirect()->to($pluginListUrl);
	}
}
