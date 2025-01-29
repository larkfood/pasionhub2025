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

namespace App\Http\Controllers\Web\Setup\Install\Traits;

use App\Exceptions\Custom\CustomException;
use App\Helpers\DBTool;
use App\Http\Controllers\Web\Setup\Install\DbInfoController;
use App\Http\Controllers\Web\Setup\Install\Traits\Db\MigrationsTrait;
use App\Models\City;
use App\Models\Country;
use Illuminate\Support\Facades\DB;

trait DbTrait
{
	use MigrationsTrait;
	
	/**
	 * STEP 4 - Database Import Submission
	 *
	 * @param array $siteInfo
	 * @param array $databaseInfo
	 * @return void
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	protected function submitDatabaseImport(array $siteInfo = [], array $databaseInfo = []): void
	{
		// Get site & database info
		$siteInfo = !empty($siteInfo) ? $siteInfo : (array)session('siteInfo');
		$databaseInfo = !empty($databaseInfo) ? $databaseInfo : (array)session('databaseInfo');
		
		// Get PDO connexion
		$pdo = $this->getPdoConnectionWithEnvCheck($databaseInfo);
		
		// Get database info as variables
		$databaseName = $databaseInfo['database'];
		$overwriteTables = $databaseInfo['overwrite_tables'] ?? '0';
		$tablesPrefix = $databaseInfo['prefix'] ?? '';
		$tablesPrefix = !empty($tablesPrefix) ? $tablesPrefix : null;
		
		// Is table overwriting enabled?
		$isTablesOverwritingEnabled = ($overwriteTables == '1');
		
		/*
		 * Get the database tables (related to the given prefix),
		 * to check if the database is empty or not.
		 *
		 * NOTE:
		 * - When $tablesPrefix is empty, all the database tables are taken in $tables,
		 *   (even these tables are previously prefixed or not).
		 * - When $tablesPrefix is filled, only tables with that prefix are taken.
		 */
		$tables = DBTool::getRawDatabaseTables($pdo, $databaseName, $tablesPrefix);
		if (!empty($tables)) {
			// The database has tables
			if ($isTablesOverwritingEnabled) {
				// Table overwriting enabled
				// Drop all old tables
				$this->dropExistingTables($pdo, $tables);
				
				// Check if all tables are dropped (Check if database's tables still exist)
				$tablesExist = false;
				$tables = DBTool::getRawDatabaseTables($pdo, $databaseName, $tablesPrefix);
				if (!empty($tables)) {
					$tablesExist = true;
				}
				
				// Some tables still exist. Invalidate the request
				if ($tablesExist) {
					// Deleting all the database tables required
					$message = trans('messages.database_tables_dropping_failed');
					throw new CustomException($message);
				}
			} else {
				// Get the DB info step URL
				$dbInfoStep = $this->getStepByKey(DbInfoController::class);
				$dbInfoUrl = $this->getStepUrl($dbInfoStep);
				
				// Table overwriting is disabled
				if (!empty($tablesPrefix)) {
					// Table prefix filled. Invalidate the request (With th right error message)
					// No existing tables must have the same prefix as the one filled
					$message = trans('messages.database_tables_with_same_prefix_exist', [
						'database'        => $databaseName,
						'prefix'          => $tablesPrefix,
						'databaseInfoUrl' => $dbInfoUrl,
					]);
				} else {
					// Table prefix is empty. Invalidate the request (With th right error message)
					// Having an empty database is required
					$message = trans('messages.database_not_empty_and_prefix_not_filled', [
						'database'        => $databaseName,
						'databaseInfoUrl' => $dbInfoUrl,
					]);
				}
				throw new CustomException($message);
			}
		}
		
		// Create the database structure
		// Import database schema (Migration)
		$this->runMigrations();
		
		// Check if database tables are created
		if (!$this->isAllModelsTablesExist($pdo, $tablesPrefix)) {
			// Creating all the database tables is required
			$message = trans('messages.database_tables_creation_failed');
			throw new CustomException($message);
		}
		
		// Insert the required initial data
		// Import required data (Seeding)
		$this->runSeeders();
		
		// Insert site info & related data
		$this->runSiteInfoSeeder($siteInfo);
		
		// Check if all required data are imported
		$countryCode = data_get($siteInfo, 'settings.localization.default_country_code');
		$countryCode = getAsStringOrNull($countryCode);
		$countCountries = $countCities = 0;
		try {
			$countCountries = DB::table((new Country())->getTable())->count(); // Latest seeder run
			$countCities = DB::table((new City())->getTable())->where('country_code', '=', $countryCode)->count();
		} catch (\Throwable $e) {
		}
		if ($countCountries <= 0 || $countCities <= 0) {
			// Importing the database data is required
			$message = trans('messages.database_data_import_failed');
			throw new CustomException($message);
		}
		
		// Close PDO connexion
		DBTool::closePdoConnection($pdo);
	}
	
	/**
	 * Drop All Existing Tables
	 *
	 * @param \PDO $pdo
	 * @param array|null $tables
	 * @return void
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	private function dropExistingTables(\PDO $pdo, ?array $tables): void
	{
		if (empty($tables)) return;
		
		// Try 4 times
		$try = 5;
		while ($try > 0) {
			$this->flushTables($pdo);
			
			// Drop all tables =================================
			// Extend query max setting
			$pdo->exec('SET group_concat_max_len = 9999999;');
			// Drop tables
			$pdo->exec('SET foreign_key_checks = 0;');
			foreach ($tables as $table) {
				if (DBTool::rawTableExists($pdo, $table)) {
					$pdo->exec('DROP TABLE ' . $table . ';');
				}
			}
			$pdo->exec('SET foreign_key_checks = 1;');
			$try--;
			// =================================================
			
			$this->flushTables($pdo);
		}
	}
	
	/**
	 * Flush Tables
	 *
	 * [ MySQL 5.6 | 5.7 ]
	 * - Closes all open tables, forces all tables in use to be closed, and flushes the query cache and prepared statement cache.
	 * - FLUSH TABLES also removes all query results from the query cache, like the RESET QUERY CACHE statement.
	 *
	 *   For information about query caching and prepared statement caching, see:
	 * - Section 8.10.3, “The MySQL Query Cache”: https://dev.mysql.com/doc/refman/5.7/en/query-cache.html
	 * - and Section 8.10.4, “Caching of Prepared Statements and Stored Programs”: https://dev.mysql.com/doc/refman/5.7/en/statement-caching.html
	 *
	 * [ MySQL 8.0 ]
	 * - Closes all open tables, forces all tables in use to be closed, and flushes the prepared statement cache.
	 * - This operation requires the FLUSH_TABLES or RELOAD privilege.
	 * - More info: https://dev.mysql.com/doc/refman/8.0/en/flush.html
	 *
	 * How MySQL Handles FLUSH TABLES: https://dev.mysql.com/doc/internals/en/flush-tables.html
	 *
	 * [ MariaDB 10.4.8 ]
	 * - The purpose of FLUSH TABLES is to clean up the open table cache and table definition cache from not in use tables.
	 *   This frees up memory and file descriptors. Normally this is not needed as the caches works on a FIFO bases,
	 *   but can be useful if the server seams to use up to much memory for some reason.
	 * - More info: https://mariadb.com/kb/en/flush/#the-different-usage-of-flush-tables
	 *
	 * @param \PDO $pdo
	 * @return void
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	private function flushTables(\PDO $pdo): void
	{
		try {
			$pdo->exec('FLUSH TABLES;');
		} catch (\Throwable $e) {
			$msg = 'ERROR: No privilege to run: "FLUSH TABLES;" - ' . $e->getMessage();
			throw new CustomException($msg);
		}
	}
	
	/**
	 * Check if all models' tables exist
	 *
	 * @param $pdo
	 * @param null $tablesPrefix
	 * @return bool
	 */
	private function isAllModelsTablesExist($pdo, $tablesPrefix = null): bool
	{
		$isAllTablesExist = true;
		try {
			// Check if all database tables exist
			$modelFiles = DBTool::getAppModelFiles();
			
			if (!empty($modelFiles)) {
				foreach ($modelFiles as $filePath) {
					$table = DBTool::getModelTableName($filePath);
					
					if (empty($table)) {
						continue;
					}
					
					if (!DBTool::rawTableExists($pdo, $table, $tablesPrefix)) {
						$isAllTablesExist = false;
					}
				}
			}
		} catch (\PDOException|\Throwable $e) {
			$isAllTablesExist = false;
		}
		
		return $isAllTablesExist;
	}
	
	/**
	 * Get PDO connexion
	 * by ensuring that the .env file exists first,
	 * then, check if the database parameters in the .env is still valid
	 * (In the event that this information has been modified)
	 *
	 * @param array $databaseInfo
	 * @return \PDO
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	protected function getPdoConnectionWithEnvCheck(array $databaseInfo = []): \PDO
	{
		// The .env file is supposed to have been created at this stage
		// So check if it exists
		if (!appEnvFileExists()) {
			session()->forget('databaseImported');
			session()->forget('cronJobsInfoSeen');
			
			$message = trans('messages.database_env_file_required');
			throw new CustomException($message);
		}
		
		// Get the database info
		$databaseInfo = !empty($databaseInfo) ? $databaseInfo : (array)session('databaseInfo');
		
		// Get PDO connexion
		return DBTool::getPdoConnection($databaseInfo);
	}
}
