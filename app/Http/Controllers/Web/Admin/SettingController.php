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

// Increase the server resources
$iniConfigFile = __DIR__ . '/../../../Helpers/Functions/ini.php';
if (file_exists($iniConfigFile)) {
	include_once $iniConfigFile;
}

/*
------------------------------------------------------------------------------------
The "field" field value for "settings" table
------------------------------------------------------------------------------------
text            => {"name":"value","label":"Value","type":"text"}
textarea        => {"name":"value","label":"Value","type":"textarea"}
checkbox        => {"name":"value","label":"Activation","type":"checkbox"}
upload (image)  => {"name":"value","label":"Value","type":"image","upload":"true","disk":"uploads","default":"images/logo@2x.png"}
selectbox       => {"name":"value","label":"Value","type":"select_from_array","options":OPTIONS}
                => {"default":"Default","blue":"Blue","yellow":"Yellow","green":"Green","red":"Red"}
                => {"smtp":"SMTP","mailgun":"Mailgun","ses":"Amazon SES","mail":"PHP Mail","sendmail":"Sendmail"}
                => {"sandbox":"sandbox","live":"live"}
------------------------------------------------------------------------------------
*/

use App\Http\Controllers\Web\Admin\Traits\SettingsTrait;
use App\Http\Requests\Admin\SettingRequest as StoreRequest;
use App\Http\Requests\Admin\SettingRequest as UpdateRequest;
use App\Http\Controllers\Web\Admin\Panel\PanelController;
use App\Models\Setting;

class SettingController extends PanelController
{
	use SettingsTrait;
	
	public function setup()
	{
		/*
		|--------------------------------------------------------------------------
		| BASIC CRUD INFORMATION
		|--------------------------------------------------------------------------
		*/
		$this->xPanel->setModel(Setting::class);
		$this->xPanel->addClause('where', 'active', 1);
		$this->xPanel->setEntityNameStrings(trans('admin.general setting'), trans('admin.general settings'));
		$this->xPanel->setRoute(admin_uri('settings'));
		$this->xPanel->enableReorder('name', 1);
		$this->xPanel->allowAccess(['reorder']);
		$this->xPanel->denyAccess(['create', 'delete']);
		$this->xPanel->setDefaultPageLength(100);
		if (!request()->input('order')) {
			$this->xPanel->orderBy('lft');
			$this->xPanel->orderBy('id');
		}
		
		$this->xPanel->removeButton('update');
		$this->xPanel->addButtonFromModelFunction('line', 'configure', 'configureButton', 'beginning');
		
		/*
		|--------------------------------------------------------------------------
		| COLUMNS AND FIELDS
		|--------------------------------------------------------------------------
		*/
		// COLUMNS
		$this->xPanel->addColumn([
			'name'          => 'name',
			'label'         => 'Setting',
			'type'          => 'model_function',
			'function_name' => 'getNameHtml',
		]);
		
		$this->xPanel->addColumn([
			'name'  => 'description',
			'label' => '',
		]);
		
		// FIELDS
		// ...
	}
	
	public function store(StoreRequest $request)
	{
		return parent::storeCrud($request);
	}
	
	public function update(UpdateRequest $request)
	{
		$setting = Setting::find(request()->segment(3));
		if (!empty($setting)) {
			// Get the right Setting class
			$classKey = $setting->key ?? '';
			
			// Get class name
			$className = str($classKey)->camel()->ucfirst()->append('Setting');
			
			// Get class full qualified name (i.e. with namespace)
			$namespace = '\App\Models\Setting\\';
			$class = $className->prepend($namespace)->toString();
			
			// If the class doesn't exist in the core app, try to get it from add-ons
			if (!class_exists($class)) {
				$namespace = plugin_namespace($classKey) . '\app\Models\Setting\\';
				$class = $className->prepend($namespace)->toString();
			}
			
			if (class_exists($class)) {
				if (method_exists($class, 'passedValidation')) {
					$request = $class::passedValidation($request);
				}
			}
		}
		
		return $this->updateTrait($request);
	}
	
	/**
	 * Find a setting's real URL
	 * admin_url('settings/find/{key}')
	 *
	 * @param $key
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function find($key): \Illuminate\Http\RedirectResponse
	{
		$setting = Setting::where('key', $key)->first();
		if (empty($setting)) {
			$message = trans('admin.setting_not_found', ['setting' => $key]);
			notification($message, 'error');
			
			return redirect()->back();
		}
		
		$url = admin_url('settings/' . $setting->id . '/edit');
		
		return redirect()->to($url);
	}
	
	/**
	 * Reset a setting by its key
	 * admin_url('settings/reset/{key}')
	 *
	 * @param $key
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function reset($key): \Illuminate\Http\RedirectResponse
	{
		// Allow only the 'pagination' setting (for the moment... waiting the full feature)
		if ($key != 'pagination') {
			$message = trans('admin.setting_reset_not_allowed', ['setting' => $key]);
			notification($message, 'info');
			
			return redirect()->back();
		}
		
		// $key is always 'pagination' here. @todo: Add support for the other setting groups
		try {
			$setting = Setting::where('key', $key)->first();
			
			if (!empty($setting)) {
				$purchaseCode = null;
				if ($key == 'app') {
					$purchaseCode = $setting->value['purchase_code'] ?? null;
				}
				
				$setting->value = !empty($purchaseCode) ? ['purchase_code' => $purchaseCode] : null;
				$setting->save();
				
				// Clear all the cache
				cache()->flush();
				
				$message = trans('admin.setting_reset_success', ['setting' => $setting->name]);
				notification($message, 'success');
			} else {
				$message = trans('admin.setting_not_found', ['setting' => $key]);
				notification($message, 'warning');
			}
		} catch (\Throwable $e) {
			notification($e->getMessage(), 'warning');
		}
		
		return redirect()->back();
	}
}
