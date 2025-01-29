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

namespace App\Http\Controllers\Web\Admin\Panel\Library\Traits\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Methods for working with relationships inside select/relationship fields.
|--------------------------------------------------------------------------
*/
trait HasRelationshipFields
{
	public static function isColumnNullable(string $columnName): bool
	{
		$instance = new static(); // Create an instance of the model to be able to get the table name
		
		$database = config('database.connections.' . config('database.default') . '.database');
		$tableName = DB::getTablePrefix() . $instance->getTable();
		
		try {
			$sql = "SELECT IS_NULLABLE
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_NAME='" . $tableName . "'
                    AND COLUMN_NAME='" . $columnName . "'
                    AND table_schema='" . $database . "'";
			$answer = DB::select($sql)[0];
		} catch (\Throwable $e) {
			return $instance->isColumnNullable2($columnName);
		}
		
		return $answer->IS_NULLABLE === 'YES';
	}
	
	public static function isColumnNullable2(string $columnName): bool
	{
		$instance = new static(); // Create an instance of the model to be able to get the table name
		$tableName = $instance->getTable();
		
		$columns = Schema::getColumns($tableName);
		$columns =  collect($columns)->keyBy('name')->toArray();
		
		return $columns[$columnName]['nullable'] ?? false;
	}
}
