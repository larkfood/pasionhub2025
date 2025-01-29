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

use App\Http\Controllers\Web\Admin\Traits\SettingsTrait;
use App\Models\Section;
use App\Http\Controllers\Web\Admin\Panel\PanelController;
use App\Http\Requests\Admin\Request as StoreRequest;
use App\Http\Requests\Admin\Request as UpdateRequest;

class SectionController extends PanelController
{
	use SettingsTrait;
	
	public function setup()
	{
		/*
		|--------------------------------------------------------------------------
		| BASIC CRUD INFORMATION
		|--------------------------------------------------------------------------
		*/
		$this->xPanel->setModel(Section::class);
		$this->xPanel->setRoute(admin_uri('sections'));
		$this->xPanel->setEntityNameStrings(trans('admin.homepage section'), trans('admin.homepage sections'));
		$this->xPanel->denyAccess(['create', 'delete']);
		$this->xPanel->allowAccess(['reorder']);
		$this->xPanel->enableReorder('name', 1);
		if (!request()->input('order')) {
			$this->xPanel->orderBy('lft');
		}
		
		$this->xPanel->addButtonFromModelFunction('top', 'reset_homepage_reorder', 'resetHomepageReOrderButton', 'end');
		$this->xPanel->addButtonFromModelFunction('top', 'reset_homepage_settings', 'resetHomepageSettingsButton', 'end');
		$this->xPanel->removeButton('update');
		$this->xPanel->addButtonFromModelFunction('line', 'configure', 'configureButton', 'beginning');
		
		// Filters
		// -----------------------
		$this->xPanel->disableSearchBar();
		// -----------------------
		$this->xPanel->addFilter(
			[
				'name'  => 'name',
				'type'  => 'text',
				'label' => mb_ucfirst(trans('admin.Name')),
			],
			false,
			fn ($value) => $this->xPanel->addClause('where', 'name', 'LIKE', "%$value%")
		);
		// -----------------------
		$this->xPanel->addFilter(
			[
				'name'  => 'status',
				'type'  => 'dropdown',
				'label' => trans('admin.Status'),
			],
			[
				1 => trans('admin.Activated'),
				2 => trans('admin.Unactivated'),
			],
			function ($value) {
				if ($value == 1) {
					$this->xPanel->addClause('where', 'active', '=', 1);
				}
				if ($value == 2) {
					$this->xPanel->addClause('where', fn ($query) => $query->columnIsEmpty('active'));
				}
			}
		);
		
		/*
		|--------------------------------------------------------------------------
		| COLUMNS AND FIELDS
		|--------------------------------------------------------------------------
		*/
		// COLUMNS
		$this->xPanel->addColumn([
			'name'          => 'name',
			'label'         => trans('admin.Section'),
			'type'          => 'model_function',
			'function_name' => 'getNameHtml',
		]);
		
		$this->xPanel->addColumn([
			'name'  => 'description',
			'label' => "",
		]);
		
		$this->xPanel->addColumn([
			'name'          => 'active',
			'label'         => trans('admin.Active'),
			'type'          => 'model_function',
			'function_name' => 'getActiveHtml',
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
		$section = Section::find(request()->segment(3));
		if (!empty($section)) {
			// Get the right Setting class
			$belongsTo = $section->belongs_to ?? '';
			$classKey = $section->key ?? '';
			
			// Get class name
			$belongsTo = !empty($belongsTo) ? str($belongsTo)->camel()->ucfirst()->finish('\\')->toString() : '';
			$className = str($classKey)->camel()->ucfirst()->append('Section');
			
			// Get class full qualified name (i.e. with namespace)
			$namespace = '\App\Models\Section\\' . $belongsTo;
			$class = $className->prepend($namespace)->toString();
			
			// If the class doesn't exist in the core app, try to get it from add-ons
			if (!class_exists($class)) {
				$namespace = plugin_namespace($classKey) . '\app\Models\Section\\' . $belongsTo;
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
	 * Find a section's real URL
	 * admin_url('sections/find/{key}')
	 *
	 * @param $key
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function find($key): \Illuminate\Http\RedirectResponse
	{
		$section = Section::where('key', $key)->first();
		if (empty($section)) {
			$message = trans('admin.section_not_found', ['section' => $key]);
			notification($message, 'error');
			
			return redirect()->back();
		}
		
		$url = admin_url('sections/' . $section->id . '/edit');
		
		return redirect()->to($url);
	}
	
	/**
	 * Homepage Sections Actions (Reset Order & Settings)
	 * admin_url('sections/reset/all/{action}')
	 *
	 * @param $action
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function resetAll($action): \Illuminate\Http\RedirectResponse
	{
		// Reset the homepage sections reorder
		if ($action == 'reorder') {
			Section::where('key', 'search_form')->update(['lft' => 0, 'rgt' => 1, 'active' => 1]);
			Section::where('key', 'locations')->update(['lft' => 2, 'rgt' => 3, 'active' => 1]);
			Section::where('key', 'premium_listings')->update(['lft' => 4, 'rgt' => 5, 'active' => 1]);
			Section::where('key', 'categories')->update(['lft' => 6, 'rgt' => 7, 'active' => 1]);
			Section::where('key', 'latest_listings')->update(['lft' => 8, 'rgt' => 9, 'active' => 1]);
			Section::where('key', 'stats')->update(['lft' => 10, 'rgt' => 11, 'active' => 1]);
			Section::where('key', 'text_area')->update(['lft' => 12, 'rgt' => 13, 'active' => 0]);
			Section::where('key', 'top_ad')->update(['lft' => 14, 'rgt' => 15, 'active' => 0]);
			Section::where('key', 'bottom_ad')->update(['lft' => 16, 'rgt' => 17, 'active' => 0]);
			
			$message = trans('admin.sections_reorder_reset_successfully');
			notification($message, 'success');
		}
		
		// Reset all the homepage settings
		if ($action == 'options') {
			Section::where('key', 'search_form')->update(['value' => null, 'active' => 1]);
			Section::where('key', 'locations')->update(['value' => null, 'active' => 1]);
			Section::where('key', 'premium_listings')->update(['value' => null, 'active' => 1]);
			Section::where('key', 'categories')->update(['value' => null, 'active' => 1]);
			Section::where('key', 'latest_listings')->update(['value' => null, 'active' => 1]);
			Section::where('key', 'stats')->update(['value' => null, 'active' => 1]);
			Section::where('key', 'text_area')->update(['value' => null, 'active' => 0]);
			Section::where('key', 'top_ad')->update(['value' => null, 'active' => 0]);
			Section::where('key', 'bottom_ad')->update(['value' => null, 'active' => 0]);
			
			// Delete files which has 'header-' as prefix
			try {
				
				// Get all files in the "app/logo/" path,
				// Filter the ones that match the "*section-*.*" and "*thumb-*-section-*.*" patterns,
				// And delete them.
				$allFiles = $this->disk->files('app/logo/');
				
				$thumbHeaderFiles = preg_grep('/.+\/thumb-.+-section-.+\./', $allFiles);
				$headerFiles = preg_grep('/.+\/section-.+\./', $allFiles);
				$matchingFiles = array_merge($thumbHeaderFiles, $headerFiles);
				
				$this->disk->delete($matchingFiles);
				
			} catch (\Throwable $e) {
			}
			
			$message = trans('admin.sections_value_reset_successfully');
			notification($message, 'success');
		}
		
		if (in_array($action, ['reorder', 'options'])) {
			cache()->flush();
		} else {
			$message = trans('admin.no_action_performed');
			notification($message, 'warning');
		}
		
		return redirect()->back();
	}
}
