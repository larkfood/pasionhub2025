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

namespace App\Models\Builders\Classes;

use App\Helpers\DBTool\DBEncoding;
use App\Models\Builders\Classes\Helpers\JsonHelper;
use App\Helpers\DBTool;

/*
 * Builder for translatable models ***
 * The create(), update(), find(), findOrFail(), findMany(), findBySlug(), and findBySlugOrFail() methods
 * for translatable models are implemented in the:
 * 'app/Http/Controllers/Web/Admin/Panel/Library/Traits/Models/SpatieTranslatable/HasTranslations.php' file
 */
class TranslationsBuilder extends GlobalBuilder
{
	public function where($column, $operator = null, $value = null, $boolean = 'and'): static
	{
		if ($column instanceof \Closure) {
			return parent::where($column, $operator, $value, $boolean);
		}
		
		// Is it a translatable model? If so, check if the column is translatable
		// Model or column not translatable
		$model = $this->model ?? null;
		if (!isTranslatableColumn($model, $column)) {
			return parent::where($column, $operator, $value, $boolean);
		}
		
		// Translatable model and column
		if (func_num_args() == 2 && empty($value)) {
			$value = $operator;
		}
		
		$locale = $locale ?? app()->getLocale();
		$masterLocale = config('translatable.fallback_locale') ?? config('app.fallback_locale');
		
		// Escaping Quote
		$value = str_replace(['\''], ['\\\''], $value);
		
		// JSON columns manipulation is only available in:
		// MySQL 5.7 or above & MariaDB 10.2.3 or above
		$jsonMethodsAreAvailable = (
			(!DBTool::isMariaDB() && DBTool::isMySqlMinVersion('5.7'))
			|| (DBTool::isMariaDB() && DBTool::isMySqlMinVersion('10.2.3'))
		);
		if ($jsonMethodsAreAvailable) {
			
			return parent::where(function (self $query) use ($column, $locale, $value, $masterLocale) {
				/*
				 * Get the DB collation to apply it to raw SQLs
				 * (Laravel seems automatically apply it through non-raw queries)
				 *
				 * Important:
				 * To make accent-insensitive searches, we need to use an accent-insensitive collation
				 * MySQL provides collations that are accent-insensitive (ignoring accents and diacritics like ț, ț, á, etc.).
				 * For example, we can use the utf8mb4_unicode_ci or utf8mb4_general_ci collation,
				 * which treats diacritics and non-diacritics equivalently in comparisons.
				 */
				$isDiacriticsEnabled = (config('settings.listings_list.enable_diacritics') == '1');
				$collation = $isDiacriticsEnabled ? DBTool::getDatabaseConnectionInfo('collation') : null;
				
				// Get the where's column
				$jsonColumn = JsonHelper::jsonExtract($column, $locale);
				$jsonColumn = 'LOWER(' . $jsonColumn . ')';
				// $jsonColumn = 'BINARY ' . $jsonColumn; // Enforce a case-sensitive comparison in MySQL
				$jsonColumn = !empty($collation) ? $jsonColumn . ' COLLATE ' . $collation : $jsonColumn;
				
				// Get the where's value
				$value = 'LOWER(\'' . $value . '\')';
				// $value = 'BINARY ' . $value; // Enforce a case-sensitive comparison in MySQL
				$value = !empty($collation) ? $value . ' COLLATE ' . $collation : $value;
				
				// Get the where's raw SQL
				$rawSql = $jsonColumn . ' LIKE ' . $value;
				
				// Fire the where's raw query
				$query->whereRaw($rawSql);
				
				if (!empty($masterLocale) && $locale != $masterLocale) {
					// Get the where's column
					$jsonColumn = JsonHelper::jsonExtract($column, $masterLocale);
					$jsonColumn = 'LOWER(' . $jsonColumn . ')';
					// $jsonColumn = 'BINARY ' . $jsonColumn; // Enforce a case-sensitive comparison in MySQL
					$jsonColumn = !empty($collation) ? $jsonColumn . ' COLLATE ' . $collation : $jsonColumn;
					
					// Get the where's raw SQL
					$rawSqlOr = $jsonColumn . ' LIKE ' . $value;
					
					// Fire the orWhere's raw query
					$query->orWhereRaw($rawSqlOr);
				}
			});
			
		} else {
			
			$value = str($value)->start('%')->toString();
			$value = str($value)->finish('%')->toString();
			
			return parent::where($column, 'LIKE', $value, $boolean);
			
		}
	}
	
	public function orWhere($column, $operator = null, $value = null): static
	{
		// Is it a translatable model? If so, check if the column is translatable
		// Model or column not translatable
		$model = $this->model ?? null;
		if (!isTranslatableColumn($model, $column)) {
			return parent::orWhere($column, $operator, $value);
		}
		
		// Translatable model and column
		return parent::orWhere(fn (self $query) => $query->where($column, $operator, $value));
	}
	
	public function orderBy($column, $direction = 'asc', $locale = null): TranslationsBuilder|static
	{
		// Is it a translatable model? If so, check if the column is translatable
		// Model or column not translatable
		$model = $this->model ?? null;
		if (!isTranslatableColumn($model, $column)) {
			return parent::orderBy($column, $direction);
		}
		
		// Translatable model and column
		$locale = $locale ?? app()->getLocale();
		$masterLocale = config('translatable.fallback_locale') ?? config('app.fallback_locale');
		
		$jsonMethodsAreAvailable = (
			(!DBTool::isMariaDB() && DBTool::isMySqlMinVersion('5.7'))
			|| (DBTool::isMariaDB() && DBTool::isMySqlMinVersion('10.2.3'))
		);
		if ($jsonMethodsAreAvailable) {
			
			$jsonColumn = JsonHelper::jsonExtract($column, $locale);
			$this->orderByRaw($jsonColumn . ' ' . $direction);
			
			if (!empty($masterLocale) && $locale != $masterLocale) {
				$jsonColumn = JsonHelper::jsonExtract($column, $masterLocale);
				$this->orderByRaw($jsonColumn . ' ' . $direction);
			}
			
		} else {
			
			/*
			 * Remove the first part of the column up to and including the first "$locale":"
			 * IMPORTANT: To prevent MySQL limitation use '"en":' instead of '"en":"' that provide wrong result.
			 * DEBUG: SELECT LOCATE('"en":', name) as nPos, SUBSTR(name, LOCATE('"en":', name)+6) as cName FROM lc_categories WHERE parent_id IS NULL;
			 */
			$subStr = '"' . $locale . '":';
			$subStrPos = 'LOCATE(\'' . $subStr . '\', ' . $column . ')';
			$jsonColumn = 'SUBSTR(' . $column . ', ' . $subStrPos . '+' . (strlen($subStr) + 1) . ')';
			$jsonColumn = 'IF(' . $subStrPos . ' > 0, ' . $jsonColumn . ', NULL)';
			// With COALESCE(), returns the first non-NULL value in a specified list of arguments (here 'zz')
			$jsonColumn = 'COALESCE(' . $jsonColumn . ', \'zz\')';
			$this->orderByRaw($jsonColumn . ' ' . $direction);
			
			if (!empty($masterLocale) && $locale != $masterLocale) {
				$subStr = '"' . $masterLocale . '":';
				$subStrPos = 'LOCATE(\'' . $subStr . '\', ' . $column . ')';
				$jsonColumn = 'SUBSTR(' . $column . ', ' . $subStrPos . '+' . (strlen($subStr) + 1) . ')';
				$jsonColumn = 'IF(' . $subStrPos . ' > 0, ' . $jsonColumn . ', ' . $column . ')';
				$this->orderByRaw($jsonColumn . ' ' . $direction);
			}
			
		}
		
		return $this;
	}
}
