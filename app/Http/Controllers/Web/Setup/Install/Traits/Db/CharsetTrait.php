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

namespace App\Http\Controllers\Web\Setup\Install\Traits\Db;

use App\Helpers\DBTool;
use App\Helpers\DBTool\DBEncoding;
use Illuminate\Support\Facades\DB;

trait CharsetTrait
{
	/**
	 * @param \PDO|null $pdo
	 * @param array $databaseInfo
	 * @return array
	 */
	private function setDatabaseConnectionCharsetAndCollation(?\PDO $pdo = null, array $databaseInfo = []): array
	{
		// Try to get PDO connexion
		try {
			if (empty($pdo)) {
				if (!empty($databaseInfo)) {
					$pdo = DBTool::getPdoConnection($databaseInfo);
				}
			}
			if (empty($pdo)) {
				if (appIsInstalled()) {
					$pdo = DB::connection()->getPdo();
				}
			}
		} catch (\Throwable $e) {
		}
		
		// Get default charset & collation
		$defaultCharset = config('larapen.core.database.encoding.default.charset', 'utf8mb4');
		$defaultCollation = config('larapen.core.database.encoding.default.collation', 'utf8mb4_unicode_ci');
		
		if (empty($pdo)) {
			return [
				'charset'   => $defaultCharset,
				'collation' => $defaultCollation,
			];
		}
		
		// Find the charset & collation to set in the /.env file
		$charsetAndCollation = DBEncoding::findConnectionCharsetAndCollation($pdo);
		$databaseInfo['charset'] = $charsetAndCollation['charset'] ?? $defaultCharset;
		$databaseInfo['collation'] = $charsetAndCollation['collation'] ?? $defaultCollation;
		
		return $databaseInfo;
	}
}
