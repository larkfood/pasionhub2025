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

namespace App\Helpers;

use App\Exceptions\Custom\CustomException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DBTool
{
	/**
	 * Get PDO Connexion
	 *
	 * @param array|null $config
	 * @return \PDO
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	public static function getPdoConnection(?array $config = []): \PDO
	{
		// Retrieve Database Parameters from the /.env file,
		// If they are not set during the function call.
		if (empty($config)) {
			$config = self::getDatabaseConnectionInfo();
		}
		
		// Retrieve database & its server parameters
		$host = $config['host'] ?? '';
		$port = $config['port'] ?? '';
		$database = $config['database'] ?? '';
		$username = $config['username'] ?? '';
		$password = $config['password'] ?? '';
		$socket = $config['socket'] ?? '';
		
		try {
			// Database connexion's configuration
			$driver = $config['driver'] ?? 'mysql';
			$charset = $config['charset'] ?? null;
			$options = $config['options'] ?? [
				\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
				\PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
				\PDO::ATTR_EMULATE_PREPARES   => true,
				\PDO::ATTR_CURSOR             => \PDO::CURSOR_FWDONLY,
			];
			
			// Get the connexion's host info
			$hostInfo = !empty($socket)
				? 'unix_socket=' . $socket
				: 'host=' . $host . ';port=' . $port;
			
			// Get the charset parameter
			$charsetInfo = !empty($charset) ? ';charset=' . $charset : '';
			
			// Get the connexion's DSN
			$dsn = $driver . ':' . $hostInfo . ';dbname=' . $database . $charsetInfo;
			
			// Connect to the database server
			return new \PDO($dsn, $username, $password, $options);
			
		} catch (\PDOException $e) {
			$errorMessage = trans('messages.database_pdo_connection_failed');
			$exceptionMessage = $e->getMessage();
		} catch (\Throwable $e) {
			$errorMessage = trans('messages.database_connection_failed');
			$exceptionMessage = $e->getMessage();
		}
		
		$errorMessage ??= '';
		if (!empty($exceptionMessage)) {
			$exceptionMessageFormat = ' ERROR: <span class="fw-bold">%s</span>';
			$errorMessage .= sprintf($exceptionMessageFormat, $exceptionMessage);
		}
		
		throw new CustomException($errorMessage);
	}
	
	/**
	 * Database Connection Info
	 *
	 * @param string|null $param
	 * @return array|string|null
	 */
	public static function getDatabaseConnectionInfo(?string $param = null): array|string|null
	{
		$config = self::getLaravelDatabaseConfig();
		
		$databaseParams = $config['connections'][$config['default']];
		$databaseParams = is_array($databaseParams) ? $databaseParams : [];
		
		// Update some database parameters
		if (!empty($databaseParams)) {
			$databaseParams['port'] = (int)$databaseParams['port'];
			$databaseParams['socket'] = $databaseParams['unix_socket'];
			$databaseParams['options'] = [
				\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
				\PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
				\PDO::ATTR_EMULATE_PREPARES   => true,
				\PDO::ATTR_CURSOR             => \PDO::CURSOR_FWDONLY,
			];
		}
		
		if (!empty($param)) {
			$value = $databaseParams[$param] ?? null;
			
			return getAsStringOrNull($value);
		}
		
		return $databaseParams;
	}
	
	/**
	 * @return array
	 */
	public static function getLaravelDatabaseConfig(): array
	{
		return (array)include realpath(__DIR__ . '/../../config/database.php');
	}
	
	/**
	 * Close PDO Connexion
	 *
	 * @param $pdo
	 * @return void
	 */
	public static function closePdoConnection(&$pdo): void
	{
		$pdo = null;
	}
	
	/**
	 * @return string
	 */
	public static function getRawTablePrefix(): string
	{
		$config = self::getLaravelDatabaseConfig();
		$defaultConnection = $config['default'] ?? '';
		$defaultDatabase = $config['connections'][$defaultConnection] ?? [];
		$prefix = $defaultDatabase['prefix'] ?? '';
		
		return getAsString($prefix);
	}
	
	/**
	 * Get full table name by adding the DB prefix
	 *
	 * @param string $name
	 * @return string
	 */
	public static function table(string $name): string
	{
		return DB::getTablePrefix() . $name;
	}
	
	/**
	 * Get full table name by adding the DB prefix
	 *
	 * @param string $name
	 * @return string
	 */
	public static function getRawTable(string $name): string
	{
		return self::getRawTablePrefix() . $name;
	}
	
	/**
	 * Quote a value with apostrophe to inject to an SQL statement
	 *
	 * @param $value
	 * @return false|string
	 */
	public static function quote($value): false|string
	{
		return DB::getPdo()->quote($value);
	}
	
	/**
	 * Get the selected database name
	 *
	 * @param \PDO $pdo
	 * @return string
	 */
	public static function getRawDatabaseName(\PDO $pdo): string
	{
		$query = $pdo->query("SELECT DATABASE()");
		$databaseName = $query->fetchColumn();
		
		return getAsString($databaseName);
	}
	
	/**
	 * Check if a table exists in the current database (Using PDO)
	 *
	 * @param \PDO $pdo
	 * @param string $table
	 * @param string|null $tablesPrefix
	 * @return bool
	 */
	public static function rawTableExists(\PDO $pdo, string $table, string $tablesPrefix = null): bool
	{
		// Try a select statement against the table
		// Run it in try/catch in case PDO is in ERRMODE_EXCEPTION.
		try {
			if (!empty($tablesPrefix)) {
				$result = $pdo->query('SELECT 1 FROM ' . $tablesPrefix . $table . ' LIMIT 1');
			} else {
				$result = $pdo->query('SELECT 1 FROM ' . $table . ' LIMIT 1');
			}
		} catch (\Throwable $e) {
			// We got an exception == table not found
			return false;
		}
		
		// Result is either boolean FALSE (no table found) or PDOStatement Object (table found)
		return $result !== false;
	}
	
	/**
	 * Get the app database's tables (By using PDO)
	 *
	 * @param \PDO $pdo
	 * @param string $database
	 * @param string|null $tablesPrefix
	 * @return array
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	public static function getRawDatabaseTables(\PDO $pdo, string $database, string $tablesPrefix = null): array
	{
		$tables = [];
		
		try {
			$sql = 'SELECT GROUP_CONCAT(table_name) AS table_names
					FROM information_schema.tables
					WHERE table_schema = "' . $database . '"';
			if (!empty($tablesPrefix)) {
				$sql = $sql . ' AND table_name LIKE "' . $tablesPrefix . '%"';
			}
			$query = $pdo->query($sql);
			$obj = $query->fetch();
			
			if (isset($obj->table_names)) {
				$tables = array_merge($tables, explode(',', $obj->table_names));
			}
		} catch (\Throwable $e) {
			throw new CustomException($e->getMessage());
		}
		
		return $tables;
	}
	
	/**
	 * Get the app database's tables (Using Laravel)
	 *
	 * @param string|null $tablesPrefix
	 * @param bool $withPrefix
	 * @return array
	 */
	public static function getDatabaseTables(string $tablesPrefix = null, bool $withPrefix = true): array
	{
		$tables = collect(Schema::getTableListing());
		
		// If a table prefix is provided,
		// Get only tables starting with the given prefix table
		if (!empty($tablesPrefix)) {
			$tables = $tables->filter(fn ($table) => str_starts_with($table, $tablesPrefix));
		}
		
		// If the tables name without prefix is requested,
		// Remove the prefix from the name of the tables found
		if (!$withPrefix) {
			$tablesPrefix = empty($tablesPrefix) ? DB::getTablePrefix() : $tablesPrefix;
			$tables = $tables->map(fn ($item) => str($item)->replaceFirst($tablesPrefix, '')->toString());
		}
		
		return $tables->toArray();
	}
	
	/**
	 * Get SQL combined with bindings values
	 *
	 * @param string $sql
	 * @param array $bindings
	 * @return string
	 */
	public static function getRealSql(string $sql, array $bindings = []): string
	{
		$sql = str_replace(['?'], ["'%s'"], $sql);
		
		return vsprintf($sql, $bindings);
	}
	
	/**
	 * Get the MySQL full version
	 *
	 * @param \PDO|null $pdo
	 * @return string
	 */
	public static function getMySqlFullVersion(\PDO $pdo = null): string
	{
		$version = '0';
		
		try {
			if (empty($pdo)) {
				$pdo = DB::connection()->getPdo();
			}
			
			if ($pdo instanceof \PDO) {
				$version = $pdo->query('SELECT VERSION()')->fetchColumn();
			}
		} catch (\Throwable $e) {
		}
		
		return getAsString($version);
	}
	
	/**
	 * Get the MySQL version
	 *
	 * @param \PDO|null $pdo
	 * @return string
	 */
	public static function getMySqlVersion(\PDO $pdo = null): string
	{
		$version = self::getMySqlFullVersion($pdo);
		
		$matches = [];
		preg_match('/^[\d.]+/', $version, $matches);
		
		return !empty($matches[0]) ? trim($matches[0]) : $version;
	}
	
	/**
	 * Check if the entered value is the MySQL minimal version
	 *
	 * @param string $min
	 * @return bool
	 */
	public static function isMySqlMinVersion(string $min): bool
	{
		// Get the MySQL version
		$version = self::getMySqlVersion();
		
		return (version_compare($version, $min) >= 0);
	}
	
	/**
	 * Check if the database is MariaDB
	 *
	 * @param \PDO|null $pdo
	 * @return bool
	 */
	public static function isMariaDB(\PDO $pdo = null): bool
	{
		$version = self::getMySqlFullVersion($pdo);
		
		return str_contains($version, 'MariaDB');
	}
	
	/**
	 * Get MySQL/MariaDB max connections and max user connections.
	 *
	 * @param \PDO|null $pdo
	 * @return array An associative array with 'max_connections' and 'max_user_connections' values.
	 */
	public static function getMySQLConnectionLimits(\PDO $pdo = null): array
	{
		try {
			if (empty($pdo)) {
				$pdo = DB::connection()->getPdo();
			}
			
			// Query to get max_connections and max_user_connections
			$query = "SHOW VARIABLES WHERE Variable_name IN ('max_connections', 'max_user_connections')";
			$stmt = $pdo->query($query);
			$variables = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
			
			// Return the values as an associative array
			return [
				'max_connections'      => $variables['max_connections'] ?? null,
				'max_user_connections' => $variables['max_user_connections'] ?? null,
			];
		} catch (\PDOException $e) {
			return [];
		}
	}
	
	/**
	 * Import SQL File
	 *
	 * @param \PDO $pdo
	 * @param string $sqlFile
	 * @param string|null $tablePrefix
	 * @param string|null $InFilePath
	 * @return void
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	public static function importSqlFile(\PDO $pdo, string $sqlFile, string $tablePrefix = null, string $InFilePath = null): void
	{
		// Enable LOAD LOCAL INFILE
		$pdo->setAttribute(\PDO::MYSQL_ATTR_LOCAL_INFILE, true);
		
		$errorDetect = false;
		$errors = '';
		
		// Temporary variable, used to store current query
		$tmpLine = '';
		
		// Read in entire file
		$lines = file($sqlFile);
		
		// Loop through each line
		foreach ($lines as $line) {
			// Skip it if it's a comment
			if (str_starts_with($line, '--') || trim($line) == '') {
				continue;
			}
			
			// Read & replace prefix
			$line = str_replace(['<<prefix>>', '<<InFilePath>>'], [$tablePrefix, $InFilePath], $line);
			$line = str_replace(['__PREFIX__', '__INFILE_PATH__'], [$tablePrefix, $InFilePath], $line);
			
			// Add this line to the current segment
			$tmpLine .= $line;
			
			// If it has a semicolon at the end, it's the end of the query
			if (str_ends_with(trim($line), ';')) {
				try {
					// Perform the Query
					$pdo->exec($tmpLine);
				} catch (\PDOException $e) {
					$errors .= 'Error occurred in the file: ' . $sqlFile;
					$errors .= ' with the query: "' . $tmpLine . '" - Info: ' . $e->getMessage() . "\n";
					$errorDetect = true;
				}
				
				// Reset temp variable to empty
				$tmpLine = '';
			}
		}
		
		// Check if error is detected
		if ($errorDetect) {
			throw new CustomException($errors);
		}
	}
	
	/**
	 * Get the app's model files
	 *
	 * @return array
	 */
	public static function getAppModelFiles(): array
	{
		$modelFiles = [];
		try {
			// Get all files available in the "app/Models/" directory
			$modelDirPath = app_path('Models') . DIRECTORY_SEPARATOR;
			$files = array_filter(glob($modelDirPath . '*.php'), 'is_file');
			
			if (!empty($files)) {
				foreach ($files as $filePath) {
					$isModelFile = self::isModelFile($filePath);
					if (!$isModelFile) {
						continue;
					}
					
					$modelFiles[] = $filePath;
				}
			}
		} catch (\Throwable $e) {
		}
		
		return $modelFiles;
	}
	
	/**
	 * Get the app's model classes
	 *
	 * @param bool $translatable
	 * @return array
	 */
	public static function getAppModelClasses(bool $translatable = false): array
	{
		// Get model files paths
		$files = self::getAppModelFiles();
		$files = collect($files);
		
		// Get class full name from path
		$pathToClass = function ($path) {
			$namespace = '\App\Models\\';
			$modelName = pathinfo(basename($path), PATHINFO_FILENAME);
			
			return $namespace . $modelName;
		};
		
		// Convert file paths to fully qualified class names
		// & Get valid model classes
		$classes = $files->map($pathToClass)->filter(fn ($class) => self::isModelClass($class));
		
		// Check if the model is translatable
		if ($translatable) {
			// Check if model is translatable
			$modelIsTranslatable = function ($modelClass) {
				try {
					$model = new $modelClass;
					
					return (method_exists($model, 'translationEnabledForModel') && $model->translationEnabledForModel());
				} catch (\Throwable $e) {
					return false;
				}
			};
			
			// Get translatable model classes
			$classes = $classes->filter($modelIsTranslatable);
		}
		
		return $classes->toArray();
	}
	
	/**
	 * Check if a class is a model class
	 *
	 * @param string $modelClass
	 * @return bool
	 */
	public static function isModelClass(string $modelClass): bool
	{
		try {
			$model = new $modelClass;
			
			return $model instanceof Model;
		} catch (\Throwable $e) {
			return false;
		}
	}
	
	/**
	 * Check if a file is a model
	 *
	 * @param string $fileFullPath
	 * @return bool
	 */
	public static function isModelFile(string $fileFullPath): bool
	{
		if (!file_exists($fileFullPath)) {
			return false;
		}
		
		if (!str_ends_with(strtolower($fileFullPath), '.php')) {
			return false;
		}
		
		// Check models that does not have a table propriety
		$modelsWithoutTableName = ['Permission', 'Role'];
		$modelName = pathinfo(basename($fileFullPath), PATHINFO_FILENAME);
		if (in_array($modelName, $modelsWithoutTableName)) {
			return true;
		}
		
		// Check models that have a table propriety
		$table = self::getModelTableName($fileFullPath);
		
		return !empty($table);
	}
	
	/**
	 * Get Model table name by parsing its file
	 *
	 * @param string $fileFullPath
	 * @param string|null $tablesPrefix
	 * @return string|null
	 */
	public static function getModelTableName(string $fileFullPath, string $tablesPrefix = null): ?string
	{
		if (!file_exists($fileFullPath)) {
			return null;
		}
		
		if (!str_ends_with(strtolower($fileFullPath), '.php')) {
			return null;
		}
		
		$content = file_get_contents($fileFullPath);
		
		// Get the model table's name
		$matches = [];
		preg_match('#\$table[^=]*=[^\']*\'([^\']+)\';#i', $content, $matches);
		$table = !empty($matches[1]) ? $matches[1] : null;
		
		if (!empty($tablesPrefix) && !empty($table)) {
			$table = $tablesPrefix . $table;
		}
		
		return getAsStringOrNull($table);
	}
	
	/**
	 * Convert table's columns from string to json
	 *
	 * @param string $tableName
	 * @param array $columns
	 * @param string $locale
	 * @return void
	 */
	public static function convertTranslatableDataToJson(string $tableName, array $columns, string $locale = 'en'): void
	{
		if (count($columns) > 0) {
			foreach ($columns as $column) {
				$statement = 'CONCAT("{\"' . $locale . '\":\"", ' . $column . ', "\"}")';
				DB::table($tableName)
					->where($column, 'NOT LIKE', '%{%')
					->where($column, 'NOT LIKE', '%}%')
					->update([
						$column => DB::raw($statement),
					]);
			}
		}
	}
}
