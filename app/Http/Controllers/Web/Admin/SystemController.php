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

use App\Helpers\DBTool;
use App\Helpers\DBTool\DBEncoding;
use App\Http\Controllers\Web\Setup\Install\Traits\CheckerTrait;
use App\Http\Controllers\Web\Admin\Panel\PanelController;

class SystemController extends PanelController
{
	use CheckerTrait;
	
	public function index()
	{
		$data = [
			'systemInfo'   => $this->getSystemInfo(),
			'databaseInfo' => $this->getDatabaseInfo(),
			'components'   => $this->getComponents(),
			'permissions'  => $this->getPermissions(),
			'imageFormats' => $this->getImageFormats(),
		];
		
		$data['title'] = trans('admin.system_info');
		
		return view('admin.system', $data);
	}
	
	/**
	 * Get system info
	 *
	 * @return array
	 */
	protected function getSystemInfo(): array
	{
		$systemInfo = [];
		
		// User-Agent
		$systemInfo[] = [
			'name'  => "User-Agent",
			'value' => request()->server('HTTP_USER_AGENT'),
		];
		
		// Server Software
		$systemInfo[] = [
			'name'  => "Server Software",
			'value' => request()->server('SERVER_SOFTWARE'),
		];
		
		// Server (Apache or Nginx) default_charset
		$requiredCharset = 'UTF-8';
		$currentCharset = ini_get('default_charset');
		$isValidDefaultCharset = (strtolower(ini_get('default_charset')) == strtolower($requiredCharset));
		$defaultCharsetStatus = !$isValidDefaultCharset ? '<i class="bi bi-exclamation-circle-fill text-warning"></i> ' : '';
		
		$warning = "The server <code>default_charset</code> is: <code>$currentCharset</code>. <code>$requiredCharset</code> is required.";
		$success = $currentCharset;
		$message = $isValidDefaultCharset ? $success : $warning;
		
		$systemInfo[] = [
			'name'  => 'Server Default Charset',
			'value' => $defaultCharsetStatus . $message,
		];
		
		// Document Root
		$documentRoot = request()->server('DOCUMENT_ROOT');
		$systemInfo[] = [
			'name'  => "Document Root",
			'value' => relativeAppPath($documentRoot),
		];
		
		// PHP version
		$requiredPhpVersion = $this->getComposerRequiredPhpVersion();
		$currentPhpVersion = PHP_VERSION;
		$isValidPhpVersion = version_compare($currentPhpVersion, $requiredPhpVersion, '>=');
		$phpVersionStatus = !$isValidPhpVersion ? '<i class="bi bi-exclamation-circle-fill text-danger"></i> ' : '';
		
		$systemInfo[] = [
			'name'  => "PHP (CGI/FPM) version",
			'value' => $phpVersionStatus . $currentPhpVersion,
		];
		
		// PHP-CLI Version Info
		$phpBinaryVersion = $this->getPhpBinaryVersion();
		$isValidPhpBinaryVersion = !empty($phpBinaryVersion);
		$phpBinaryVersionStatus = !$isValidPhpBinaryVersion ? '<i class="bi bi-exclamation-circle-fill text-warning"></i> ' : '';
		
		$requiredPhpVersion = $this->getComposerRequiredPhpVersion();
		
		$warning = "<span class='font-weight-bolder'>IMPORTANT:</span> ";
		$warning .= "You have to check your server's <code>PHP-cli</code> version manually. ";
		$warning .= "This need to be version <code>$requiredPhpVersion or greater</code> to allow you to run the cron job commands. ";
		$warning .= "<a href='https://stackoverflow.com/a/9315749/9869030' target='_blank'>More Info</a>";
		
		$popover = 'data-bs-toggle="popover" data-html="true" title="PHP-CLI" data-bs-content="' . $warning . '"';
		$warning = '<a href="javascript: void(0);" ' . $popover . '>Action Required</a>';
		
		$success = $phpBinaryVersion;
		$message = $isValidPhpBinaryVersion ? $success : $warning;
		
		$systemInfo[] = [
			'name'  => 'PHP-CLI version',
			'value' => $phpBinaryVersionStatus . $message,
		];
		
		return $systemInfo;
	}
	
	/**
	 * Get database info
	 *
	 * @return array
	 */
	protected function getDatabaseInfo(): array
	{
		$databaseInfo = [];
		
		// Database Server Type
		$databaseInfo[] = [
			'name'  => "Database Server Type",
			'value' => DBTool::isMariaDB() ? 'MariaDB' : 'MySQL',
		];
		
		// Database Server version
		$fullDatabaseVersion = DBTool::getMySqlFullVersion();
		$databaseCurrentVersion = DBTool::getMySqlVersion();
		if (!DBTool::isMariaDB()) {
			$databaseMinVersion = '5.6';
			$databaseRecommendedVersion = '5.7';
			$databaseIsMySqlDeprecatedVersion = (
				(version_compare($databaseCurrentVersion, $databaseMinVersion) >= 0)
				&& (version_compare($databaseCurrentVersion, $databaseMinVersion . '.9') <= 0)
			);
			$databaseIsMySqlRightVersion = DBTool::isMySqlMinVersion($databaseRecommendedVersion);
			$isValidDatabaseVersion = ($databaseIsMySqlDeprecatedVersion || $databaseIsMySqlRightVersion);
			$databaseVersionStatus = $isValidDatabaseVersion
				? (
				$databaseIsMySqlDeprecatedVersion
					? '<i class="bi bi-exclamation-circle-fill text-warning"></i> '
					: ''
				)
				: '<i class="bi bi-x-circle-fill text-danger"></i> ';
			
			$warning = 'The minimum MySQL version required is: <code>' . $databaseMinVersion . '</code>, '
				. 'version <code>' . $databaseRecommendedVersion . '</code> or greater is recommended.';
			$success = $databaseIsMySqlDeprecatedVersion
				? 'Version <code>' . $databaseCurrentVersion . '</code> is not recommended. '
				. 'Upgrade your database to version <code>' . $databaseRecommendedVersion . '</code> or greater.'
				: $fullDatabaseVersion;
		} else {
			$databaseMinVersion = '10.2.3';
			$isValidDatabaseVersion = (DBTool::isMySqlMinVersion($databaseMinVersion));
			$databaseVersionStatus = !$isValidDatabaseVersion ? '<i class="bi bi-x-circle-fill text-danger"></i>' : '';
			
			$warning = 'Version <code>' . $databaseMinVersion . '</code> or greater is required.';
			$success = $fullDatabaseVersion;
		}
		
		$message = $isValidDatabaseVersion ? $success : $warning;
		
		$databaseInfo[] = [
			'name'  => "Database Server version",
			'value' => $databaseVersionStatus . $message,
		];
		
		// Database connections limit
		$connections = DBTool::getMySQLConnectionLimits();
		$minConnections = 30;
		$minUserConnections = 30;
		$maxConnections = (int)($connections['max_connections'] ?? 0);
		$maxUserConnections = (int)($connections['max_user_connections'] ?? 0);
		
		$isValidConnectionsLimit = ($maxConnections > 0 && $maxUserConnections > 0)
			? ($maxConnections >= $minConnections && $maxUserConnections >= $minUserConnections)
			: ($maxConnections > 0 && $maxConnections >= $minConnections);
		$connectionsLimitStatus = !$isValidConnectionsLimit ? '<i class="bi bi-exclamation-circle-fill text-warning"></i> ' : '';
		
		$maxConnectionsTxt = ($maxConnections > 0) ? $maxConnections : '--';
		$maxUserConnectionsTxt = ($maxUserConnections > 0) ? $maxUserConnections : '--';
		
		// Get max connections info
		$connectionInfo = 'max_connections: <code>' . $maxConnectionsTxt . '</code>';
		if ($maxUserConnections > 0) {
			$connectionInfo .= ' • max_user_connections: <code>' . $maxUserConnectionsTxt . '</code>';
		}
		
		// Get max connections hint
		$connectionHint = '<br>';
		$connectionHint .= '<span class="badge bg-info">Note</span> For optimal database performance, set <code>max_user_connections</code> between <code>30</code> and <code>100</code> to control individual user load and <code>max_connections</code> between <code>150</code> and <code>200</code> to handle overall traffic.';
		$connectionHint .= '<br><br>';
		
		$success = ($maxUserConnections > 0) ? $connectionInfo : ($connectionInfo . $connectionHint);
		$warning = $connectionsLimitStatus . $connectionInfo . $connectionHint;
		
		$message = $isValidConnectionsLimit ? $success : $warning;
		
		$databaseInfo[] = [
			'name'  => "Database Connections Limit",
			'value' => $message,
		];
		
		// Get the server's charset & collation using PDO
		$databaseServerEncoding = DBEncoding::getServerCharsetAndCollation();
		if (!empty($databaseServerEncoding)) {
			$charset = data_get($databaseServerEncoding, 'charset');
			$collation = data_get($databaseServerEncoding, 'collation');
			$databaseInfo[] = [
				'name'  => "Database Server Encoding",
				'value' => 'Charset: <code>' . $charset . '</code> • Collation: <code>' . $collation . '</code>',
			];
		}
		
		// Get the database's charset & collation using PDO
		$selectedDatabaseEncoding = DBEncoding::getDatabaseCharsetAndCollation();
		if (!empty($selectedDatabaseEncoding)) {
			$charset = data_get($selectedDatabaseEncoding, 'charset');
			$collation = data_get($selectedDatabaseEncoding, 'collation');
			$databaseInfo[] = [
				'name'  => "Selected Database Encoding",
				'value' => 'Charset: <code>' . $charset . '</code> • Collation: <code>' . $collation . '</code>',
			];
		}
		
		// Get the connection's charset & collation using PDO
		$databaseConnectionEncoding = DBEncoding::getConnectionCharsetAndCollation();
		if (!empty($databaseConnectionEncoding)) {
			$charset = data_get($databaseConnectionEncoding, 'charset');
			$collation = data_get($databaseConnectionEncoding, 'collation');
			$hint = ' (set in the <code>/.env</code> file)';
			$databaseInfo[] = [
				'name'  => "Database Connection Encoding",
				'value' => 'Charset: <code>' . $charset . '</code> • Collation: <code>' . $collation . '</code>' . $hint,
			];
		}
		
		// Database charset & collation validation
		// Get charset & collation status
		$isDiacriticsEnabled = (config('settings.listings_list.enable_diacritics') == '1');
		$isValidCharsetAndCollation = DBEncoding::isValidCharsetAndCollation();
		$charsetAndCollationStatus = !$isValidCharsetAndCollation
			? (
			$isDiacriticsEnabled
				? '<i class="bi bi-x-circle-fill text-danger"></i>'
				: '<i class="bi bi-exclamation-circle-fill text-warning"></i>'
			)
			: '';
		
		// Get the default charset & collation
		$defaultCharset = config('larapen.core.database.encoding.default.charset', 'utf8mb4');
		$defaultCollation = config('larapen.core.database.encoding.default.collation', 'utf8mb4_unicode_ci');
		
		// Get the first recommended charset & collation that is available on the server
		$recommendedCharsetAndCollation = DBEncoding::getFirstValidRecommendedCharsetAndCollation();
		if (!empty($recommendedCharsetAndCollation)) {
			$defaultCharset = $recommendedCharsetAndCollation['charset'] ?? $defaultCharset;
			$defaultCollation = $recommendedCharsetAndCollation['collation'] ?? $defaultCollation;
		}
		
		// Find the charset & collation to set in the /.env file
		$charsetAndCollation = DBEncoding::findConnectionCharsetAndCollation();
		$connectionCharset = $charsetAndCollation['charset'] ?? $defaultCharset;
		$connectionCollation = $charsetAndCollation['collation'] ?? $defaultCollation;
		
		// Get the current /.env file's charset & collation
		$envCharsetAndCollation = DBEncoding::getEnvCharsetAndCollation();
		$envCharset = $envCharsetAndCollation['charset'] ?? null;
		$envCollation = $envCharsetAndCollation['collation'] ?? null;
		
		// Does the charset (and collation) of the /.env file need to be updated?
		$isCharsetNeedToBeUpdated = ($connectionCharset != $envCharset || $connectionCollation != $envCollation);
		
		/*
		$charsetAndCollation = DBEncoding::validateCharsetAndCollation($envCharsetAndCollation, true);
		$envCharset = $charsetAndCollation['charset'] ?? $defaultCharset;
		$envCollation = $charsetAndCollation['collation'] ?? $defaultCollation;
		*/
		
		$charsetVars = [
			'character_set_database',
		];
		$collationVars = [
			'collation_database',
		];
		$envFileVars = [
			'<span class="fw-bold">DB_CHARSET=</span><code>' . $connectionCharset . '</code>',
			'<span class="fw-bold">DB_COLLATION=</span><code>' . $connectionCollation . '</code>',
		];
		
		$and = t('_and_');
		
		$charsetVarsStr = collect($charsetVars)
			->map(fn ($item) => ('<span class="fw-bold">' . $item . '</span>'))
			->join(', ', $and);
		
		$collationVarsStr = collect($collationVars)
			->map(fn ($item) => ('<span class="fw-bold">' . $item . '</span>'))
			->join(', ', $and);
		
		$envFileVarsStr = collect($envFileVars)->join('<br>');
		
		// Get warning message
		$warning = '';
		if ($isCharsetNeedToBeUpdated) {
			$btnUrl = admin_url('actions/update_database_charset_collation');
			$warning .= 'The <code>/.env</code> file\'s charset & collation variables need to be updated like this: ';
			$warning .= '<br>' . $envFileVarsStr;
			$warning .= '<br><br>';
			$warning .= '<a href="' . $btnUrl . '" class="btn btn-primary btn-sm confirm-simple-action">Update the /.env file</a>';
			$warning .= '<br><br>';
			$warning .= 'If the error persists, the database server variable: ' . $charsetVarsStr;
		} else {
			$warning .= 'The database server variable: ' . $charsetVarsStr;
		}
		$warning .= ' must be set to <code>' . $envCharset . '</code> in the server configuration.';
		$warning .= ' Additionally, the variables: ' . $collationVarsStr;
		$warning .= ' must be set to <code>' . $envCollation . '</code> in the server configuration.';
		$warning .= ' Note: These variables may be set through tools like cPanel, phpMyAdmin, etc.';
		
		// Get valid message
		$success = "The charset (<code>$envCharset</code>) and collation (<code>$envCollation</code>) are valid.";
		
		// Get message
		$message = $isValidCharsetAndCollation ? $success : $warning;
		
		$databaseInfo[] = [
			'name'  => "Charset & Collation Validation",
			'value' => $charsetAndCollationStatus . ' ' . $message,
		];
		
		return $databaseInfo;
	}
}
