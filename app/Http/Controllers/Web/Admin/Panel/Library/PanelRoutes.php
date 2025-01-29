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

namespace App\Http\Controllers\Web\Admin\Panel\Library;

use Illuminate\Support\Facades\Route;

class PanelRoutes
{
	/**
	 * @param $name
	 * @param $controller
	 * @param array $options
	 */
	public static function resource($name, $controller, array $options = [])
	{
		$prefix = 'crud.' . $name . '.';
		
		// CRUD Routes
		Route::post($name . '/search', $controller . '@search')->name($prefix . 'search');
		Route::get($name . '/reorder/{lang?}', $controller . '@reorder')->name($prefix . 'reorder');
		Route::post($name . '/reorder/{lang?}', $controller . '@saveReorder')->name($prefix . 'save.reorder');
		Route::get($name . '/{id}/details', $controller . '@showDetailsRow')->name($prefix . 'showDetailsRow');
		Route::get($name . '/{id}/revisions', $controller . '@listRevisions')->name($prefix . 'listRevisions');
		Route::post($name . '/{id}/revisions/{revisionId}/restore', $controller . '@restoreRevision')->name($prefix . 'restoreRevision');
		Route::post($name . '/bulk_actions', $controller . '@bulkActions')->name($prefix . 'bulkActions');
		
		$optionsWithDefaultRouteNames = array_merge([
			'names' => [
				'index'   => $prefix . 'index',
				'create'  => $prefix . 'create',
				'store'   => $prefix . 'store',
				'edit'    => $prefix . 'edit',
				'update'  => $prefix . 'update',
				'show'    => $prefix . 'show',
				'destroy' => $prefix . 'destroy',
			],
		], $options);
		
		Route::resource($name, $controller, $optionsWithDefaultRouteNames);
	}
}
