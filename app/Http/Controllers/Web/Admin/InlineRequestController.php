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

use App\Helpers\DBTool;
use App\Http\Controllers\Web\Admin\Traits\InlineRequestTrait;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class InlineRequestController extends Controller
{
	use InlineRequestTrait;
	
	protected string $table = '';
	protected string $columnType = '';
	protected string|int $modelId = '';
	
	/**
	 * @param $table
	 * @param $column
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	public function make($table, $column, Request $request): \Illuminate\Http\JsonResponse
	{
		$modelId = $request->input('dataId');
		$status = 0;
		
		$result = [
			'table'   => $table,
			'column'  => $column,
			'modelId' => $modelId,
			'status'  => $status,
		];
		
		// Check parameters
		if (!auth()->check() || !auth()->user()->can(Permission::getStaffPermissions())) {
			return ajaxResponse()->json($result, 401);
		}
		if (!Schema::hasTable($table)) {
			return ajaxResponse()->json($result, 400);
		}
		if (!Schema::hasColumn($table, $column)) {
			return ajaxResponse()->json($result, 400);
		}
		
		// Get the column type
		$columnType = Schema::getColumnType($table, $column);
		
		// Get the table's model fully qualified class name
		// (i.e. the model's class name with its namespace)
		$modelClass = null;
		$modelClasses = DBTool::getAppModelClasses();
		if (!empty($modelClasses)) {
			foreach ($modelClasses as $class) {
				/**
				 * @var \Illuminate\Database\Eloquent\Model $class
				 */
				$modelTable = (new $class)->getTable();
				
				if ($modelTable == $table) {
					$modelClass = $class;
					break;
				}
			}
		}
		
		// Get the model entry
		$model = null;
		if (!empty($modelClass)) {
			$model = $modelClass::find($modelId);
		}
		
		// Check if the entry is found
		if (empty($model)) {
			return ajaxResponse()->json($result);
		}
		
		// Update attributes
		$this->table = $table;
		$this->columnType = $columnType;
		$this->modelId = $modelId;
		
		// Update the specified column related to its table
		return $this->updateData($model, $column);
	}
}
