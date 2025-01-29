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

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

trait EnvTrait
{
	/**
	 * Write configuration values to file
	 *
	 * @param array $siteInfo
	 * @param array $databaseInfo
	 * @return void
	 */
	private function writeEnv(array $siteInfo = [], array $databaseInfo = []): void
	{
		// Get .env file path
		$filePath = base_path('.env');
		
		// Remove the old .env file (If exists)
		if (File::exists($filePath)) {
			File::delete($filePath);
		}
		
		// Set app key
		$appKey = generateAppKey();
		$appKey = config('app.key', $appKey);
		
		// Get app host
		$appHost = getUrlHost($this->baseUrl);
		
		// Get app version
		$appVersion = getLatestVersion();
		
		// API Token (for API calls)
		$apiToken = generateApiToken();
		
		// Get site & database info
		$siteInfo = !empty($siteInfo) ? $siteInfo : (array)session('siteInfo');
		$databaseInfo = !empty($databaseInfo) ? $databaseInfo : (array)session('databaseInfo');
		
		// Get the purchase code
		$purchaseCode = data_get($siteInfo, 'settings.app.purchase_code');
		
		// Get database parameters
		// $dbConnection = $databaseInfo['connection'] ?? 'mysql';
		$dbConnection = 'mysql';
		$dbHost = $databaseInfo['host'] ?? '';
		$dbPort = $databaseInfo['port'] ?? '';
		$dbSocket = $databaseInfo['socket'] ?? '';
		$dbPrefix = $databaseInfo['prefix'] ?? '';
		$dbDatabase = $databaseInfo['database'] ?? '';
		$dbUsername = isset($databaseInfo['username']) ? addcslashes($databaseInfo['username'], '"') : '';
		$dbPassword = isset($databaseInfo['password']) ? addcslashes($databaseInfo['password'], '"') : '';
		$dbCharset = $databaseInfo['charset'] ?? 'utf8mb4';
		$dbCollation = $databaseInfo['collation'] ?? 'utf8mb4_unicode_ci';
		
		/*
		 * Database URL
		 *
		 * Note:
		 * Some managed database providers such as AWS and Heroku provide a single database "URL"
		 * that contains all of the connection information for the database in a single string.
		 * An example database URL may look something like the following:
		 * driver://username:password@host:port/database?options
		 *
		 * Example:
		 * mysql://root:password@127.0.0.1/forge?charset=UTF-8
		 *
		 * For convenience, Laravel supports these URLs as an alternative to configuring your database with multiple configuration options.
		 * If the url (or corresponding DB_URL environment variable) configuration option is present,
		 * it will be used to extract the database connection and credential information.
		 */
		$dbUrl = '';
		/*
		$options = "charset=$dbCharset&collation=$dbCollation&prefix=$dbPrefix";
		if (!empty($dbPort)) {
			$dbUrl = "$dbConnection://$dbUsername:$dbPassword@$dbHost:$dbPort/$dbDatabase?$options";
		} else {
			$dbUrl = "$dbConnection://$dbUsername:$dbPassword@$dbHost/$dbDatabase?$options";
		}
		*/
		
		$timezone = config('app.timezone', 'UTC');
		$forceHttps = str_starts_with($this->baseUrl, 'https://') ? 'true' : 'false';
		
		// Generate .env file content
		$content = 'APP_ENV=production' . "\n";
		$content .= 'APP_KEY=' . $appKey . "\n";
		$content .= 'APP_DEBUG=false' . "\n";
		$content .= 'APP_URL="' . $this->baseUrl . '"' . "\n";
		$content .= 'APP_LOCALE=en' . "\n";
		$content .= 'FALLBACK_LOCALE_FOR_DB=en' . "\n";
		$content .= 'APP_VERSION=' . $appVersion . "\n";
		$content .= "\n";
		$content .= 'PURCHASE_CODE=' . $purchaseCode . "\n";
		$content .= 'TIMEZONE=' . $timezone . "\n";
		$content .= 'FORCE_HTTPS=' . $forceHttps . "\n";
		$content .= "\n";
		$content .= 'DB_CONNECTION=' . $dbConnection . "\n";
		$content .= 'DB_SOCKET=' . $dbSocket . "\n";
		$content .= 'DB_URL=' . $dbUrl . "\n";
		$content .= 'DB_HOST=' . $dbHost . "\n";
		$content .= 'DB_PORT=' . $dbPort . "\n";
		$content .= 'DB_DATABASE=' . $dbDatabase . "\n";
		$content .= 'DB_USERNAME="' . $dbUsername . '"' . "\n";
		$content .= 'DB_PASSWORD="' . $dbPassword . '"' . "\n";
		$content .= 'DB_TABLES_PREFIX=' . $dbPrefix . "\n";
		$content .= 'DB_CHARSET=' . $dbCharset . "\n";
		$content .= 'DB_COLLATION=' . $dbCollation . "\n";
		$content .= 'DB_DUMP_BINARY_PATH=' . "\n";
		$content .= "\n";
		$content .= 'APP_API_TOKEN="' . $apiToken . '"' . "\n";
		$content .= 'APP_HTTP_CLIENT=none' . "\n";
		$content .= "\n";
		$content .= 'IMAGE_DRIVER=gd' . "\n";
		$content .= "\n";
		$content .= 'CACHE_STORE=file' . "\n";
		$content .= 'CACHE_PREFIX=lc_' . "\n";
		$content .= "\n";
		$content .= 'QUEUE_CONNECTION=sync' . "\n";
		$content .= "\n";
		$content .= 'REDIS_CLIENT=predis' . "\n"; // phpredis, predis
		$content .= 'REDIS_CLUSTER=redis' . "\n";
		$content .= 'REDIS_SCHEME=tcp' . "\n";
		$content .= 'REDIS_HOST=127.0.0.1' . "\n";
		$content .= 'REDIS_USERNAME=null' . "\n";
		$content .= 'REDIS_PASSWORD=null' . "\n";
		$content .= 'REDIS_PORT=6379' . "\n";
		$content .= "\n";
		$content .= 'SESSION_DRIVER=file' . "\n";
		$content .= 'SESSION_LIFETIME=360' . "\n";
		$content .= 'SESSION_ENCRYPT=false' . "\n";
		$content .= 'SESSION_PATH=/' . "\n";
		$content .= 'SESSION_DOMAIN=null' . "\n";
		$content .= "\n";
		$content .= 'LOG_CHANNEL=daily' . "\n";
		$content .= 'LOG_STACK=single' . "\n";
		$content .= 'LOG_DEPRECATIONS_CHANNEL=null' . "\n";
		$content .= 'LOG_LEVEL=debug' . "\n";
		$content .= 'LOG_DAILY_DAYS=2' . "\n";
		$content .= "\n";
		$content .= 'DISABLE_USERNAME=true' . "\n";
		
		// Save the new .env file
		File::put($filePath, $content);
		
		// Reload the app's config
		// Clear config: The system will load the created .env file's values into config again
		Artisan::call('config:clear');
	}
}
