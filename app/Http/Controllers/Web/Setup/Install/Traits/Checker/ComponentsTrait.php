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

namespace App\Http\Controllers\Web\Setup\Install\Traits\Checker;

use App\Http\Controllers\Web\Setup\Install\Traits\Checker\Components\ExtensionTrait;
use App\Http\Controllers\Web\Setup\Install\Traits\Checker\Components\PhpTrait;

trait ComponentsTrait
{
	use PhpTrait, ExtensionTrait;
	
	/**
	 * @return array
	 */
	protected function getComponents(): array
	{
		// PHP version
		$requiredPhpVersion = $this->getComposerRequiredPhpVersion();
		$phpBinaryVersion = $this->getPhpBinaryVersion();
		
		// PHP & its components (modules)
		$components = [];
		
		$components[] = [
			'type'              => 'component',
			'name'              => 'PHP (CGI/FPM) version',
			'required'          => true,
			'isOk'              => version_compare(PHP_VERSION, $requiredPhpVersion, '>='),
			'permanentChecking' => false,
			'warning'           => 'PHP (CGI/FPM) <code>' . $requiredPhpVersion . '</code> or higher is required.',
			'success'           => 'The PHP (CGI/FPM) version <code>' . PHP_VERSION . '</code> is valid.',
		];
		
		$components[] = [
			'type'              => 'component',
			'name'              => 'PHP-CLI version',
			'required'          => false,
			'isOk'              => version_compare($phpBinaryVersion, $requiredPhpVersion, '>='),
			'permanentChecking' => false,
			'warning'           => 'PHP-CLI <code>' . $requiredPhpVersion . '</code> or higher is required.',
			'success'           => 'The PHP-CLI version <code>' . $phpBinaryVersion . '</code> is valid.',
		];
		
		$components[] = [
			'type'              => 'component',
			'name'              => 'PHP bcmath extension',
			'required'          => true,
			'isOk'              => extension_loaded('bcmath'),
			'permanentChecking' => true,
			'warning'           => $this->getExtensionWarning('bcmath'),
			'success'           => $this->getExtensionMessage('bcmath'),
		];
		
		$components[] = [
			'type'              => 'component',
			'name'              => 'PHP ctype extension',
			'required'          => true,
			'isOk'              => extension_loaded('ctype'),
			'permanentChecking' => true,
			'warning'           => $this->getExtensionWarning('ctype'),
			'success'           => $this->getExtensionMessage('ctype'),
		];
		
		$requiredCurlVersion = '7.34.0';
		$currentCurlVersion = $this->getExtensionVersion('curl', fallback: '1.0.0', strict: true);
		$components[] = [
			'type'              => 'component',
			'name'              => 'PHP curl extension',
			'required'          => true,
			'isOk'              => (
				extension_loaded('curl')
				&& version_compare($currentCurlVersion, $requiredCurlVersion, '>=')
			),
			'permanentChecking' => true,
			'warning'           => $this->getExtensionWarning('curl', $requiredCurlVersion),
			'success'           => $this->getExtensionMessage('curl'),
		];
		
		$components[] = [
			'type'              => 'component',
			'name'              => 'PHP dom extension',
			'required'          => true,
			'isOk'              => extension_loaded('dom'),
			'permanentChecking' => true,
			'warning'           => $this->getExtensionWarning('dom'),
			'success'           => $this->getExtensionMessage('dom'),
		];
		
		$components[] = [
			'type'              => 'component',
			'name'              => 'PHP fileinfo extension',
			'required'          => true,
			'isOk'              => extension_loaded('fileinfo'),
			'permanentChecking' => true,
			'warning'           => $this->getExtensionWarning('fileinfo'),
			'success'           => $this->getExtensionMessage('fileinfo'),
		];
		
		$components[] = [
			'type'              => 'component',
			'name'              => 'PHP filter extension',
			'required'          => true,
			'isOk'              => extension_loaded('filter'),
			'permanentChecking' => true,
			'warning'           => $this->getExtensionWarning('filter'),
			'success'           => $this->getExtensionMessage('filter'),
		];
		
		$components[] = [
			'type'              => 'component',
			'name'              => 'PHP hash extension',
			'required'          => true,
			'isOk'              => extension_loaded('hash'),
			'permanentChecking' => true,
			'warning'           => $this->getExtensionWarning('hash'),
			'success'           => $this->getExtensionMessage('hash'),
		];
		
		$components[] = [
			'type'              => 'component',
			'name'              => 'PHP json extension',
			'required'          => true,
			'isOk'              => extension_loaded('json'),
			'permanentChecking' => true,
			'warning'           => $this->getExtensionWarning('json'),
			'success'           => $this->getExtensionMessage('json'),
		];
		
		$components[] = [
			'type'              => 'component',
			'name'              => 'PHP mbstring extension',
			'required'          => true,
			'isOk'              => extension_loaded('mbstring'),
			'permanentChecking' => true,
			'warning'           => $this->getExtensionWarning('mbstring'),
			'success'           => $this->getExtensionMessage('mbstring'),
		];
		
		$components[] = [
			'type'              => 'component',
			'name'              => 'PHP openssl extension',
			'required'          => true,
			'isOk'              => extension_loaded('openssl'),
			'permanentChecking' => true,
			'warning'           => $this->getExtensionWarning('openssl'),
			'success'           => $this->getExtensionMessage('openssl'),
		];
		
		$components[] = [
			'type'              => 'component',
			'name'              => 'PHP pcre extension',
			'required'          => true,
			'isOk'              => extension_loaded('pcre'),
			'permanentChecking' => true,
			'warning'           => $this->getExtensionWarning('pcre'),
			'success'           => $this->getExtensionMessage('pcre'),
		];
		
		$components[] = [
			'type'              => 'component',
			'name'              => 'PHP pdo extension',
			'required'          => true,
			'isOk'              => extension_loaded('pdo'),
			'permanentChecking' => true,
			'warning'           => $this->getExtensionWarning('pdo'),
			'success'           => $this->getExtensionMessage('pdo'),
		];
		
		$components[] = [
			'type'              => 'component',
			'name'              => 'PHP pdo_mysql extension',
			'required'          => true,
			'isOk'              => extension_loaded('pdo_mysql'),
			'permanentChecking' => true,
			'warning'           => $this->getExtensionWarning('pdo_mysql'),
			'success'           => $this->getExtensionMessage('pdo_mysql'),
		];
		
		$components[] = [
			'type'              => 'component',
			'name'              => 'MySQL driver for PHP pdo extension',
			'required'          => true,
			'isOk'              => (
				class_exists('\PDO')
				&& method_exists(\PDO::class, 'getAvailableDrivers')
				&& in_array('mysql', \PDO::getAvailableDrivers())
			),
			'permanentChecking' => true,
			'warning'           => 'MySQL driver for PHP pdo extension is required.',
			'success'           => 'MySQL driver for PHP pdo extension is installed.',
		];
		
		$components[] = [
			'type'              => 'component',
			'name'              => 'PHP session extension',
			'required'          => true,
			'isOk'              => extension_loaded('session'),
			'permanentChecking' => true,
			'warning'           => $this->getExtensionWarning('session'),
			'success'           => $this->getExtensionMessage('session'),
		];
		
		$components[] = [
			'type'              => 'component',
			'name'              => 'PHP tokenizer extension',
			'required'          => true,
			'isOk'              => extension_loaded('tokenizer'),
			'permanentChecking' => true,
			'warning'           => $this->getExtensionWarning('tokenizer'),
			'success'           => $this->getExtensionMessage('tokenizer'),
		];
		
		$components[] = [
			'type'              => 'component',
			'name'              => 'PHP xml extension',
			'required'          => true,
			'isOk'              => extension_loaded('xml'),
			'permanentChecking' => true,
			'warning'           => $this->getExtensionWarning('xml'),
			'success'           => $this->getExtensionMessage('xml'),
		];
		
		$isGdEnabled = (extension_loaded('gd') && function_exists('gd_info'));
		// $currentGdVersion = $this->getExtensionVersion('gd', fallback: '1.0.0', strict: true);
		$gdChecker = [
			'type'              => 'component',
			'name'              => 'PHP gd extension',
			'required'          => true,
			'isOk'              => $isGdEnabled,
			'permanentChecking' => true,
			'warning'           => $this->getExtensionWarning('gd'),
			'success'           => $this->getExtensionMessage('gd'),
		];
		
		$isImagickEnabled = (extension_loaded('imagick') && class_exists('\Imagick') && class_exists('\ImagickDraw'));
		// $currentImagickVersion = $this->getExtensionVersion('imagick', fallback: '1.0.0', strict: true);
		$imagickChecker = [
			'type'              => 'component',
			'name'              => 'PHP imagick extension',
			'required'          => true,
			'isOk'              => $isImagickEnabled,
			'permanentChecking' => true,
			'warning'           => $this->getExtensionWarning('imagick'),
			'success'           => $this->getExtensionMessage('imagick'),
		];
		
		if ($isGdEnabled && $isImagickEnabled) {
			$components[] = $gdChecker;
			$components[] = $imagickChecker;
		} else {
			$components[] = $isImagickEnabled ? $imagickChecker : $gdChecker;
		}
		
		// Other components
		$otherComponents = [];
		
		$otherComponents[] = [
			'type'              => 'component',
			'name'              => 'PHP intl extension',
			'required'          => false,
			'isOk'              => (
				extension_loaded('intl')
				&& class_exists('\Locale')
				&& class_exists('\NumberFormatter')
				&& class_exists('\Collator')
			),
			'permanentChecking' => false,
			'warning'           => $this->getExtensionWarning('intl'),
			'success'           => $this->getExtensionMessage('intl'),
		];
		
		// Check if PhpRedis is installed on the server
		if (config('database.redis.client') === 'phpredis') {
			$otherComponents[] = [
				'type'              => 'component',
				'name'              => 'PHP PhpRedis extension',
				'required'          => true,
				'isOk'              => extension_loaded('redis'),
				'permanentChecking' => true,
				'warning'           => $this->getExtensionWarning('PhpRedis'),
				'success'           => $this->getExtensionMessage('PhpRedis'),
			];
		}
		
		$otherComponents[] = [
			'type'              => 'component',
			'name'              => 'PHP zip extension',
			'required'          => false,
			'isOk'              => (extension_loaded('zip') && class_exists('\ZipArchive')),
			'permanentChecking' => false,
			'warning'           => $this->getExtensionWarning('zip'),
			'success'           => $this->getExtensionMessage('zip'),
		];
		
		$otherComponents[] = [
			'type'              => 'component',
			'name'              => 'PHP open_basedir setting',
			'required'          => false,
			'isOk'              => empty(ini_get('open_basedir')),
			'permanentChecking' => false,
			'warning'           => 'The PHP <code>open_basedir</code> setting must be disabled.',
			'success'           => 'The PHP <code>open_basedir</code> setting is disabled.',
		];
		
		$otherComponents[] = [
			'type'              => 'component',
			'name'              => 'PHP escapeshellarg() function',
			'required'          => true,
			'isOk'              => isFunctionEnabled('escapeshellarg'),
			'permanentChecking' => true,
			'warning'           => 'The PHP <code>escapeshellarg()</code> function must be enabled.',
			'success'           => 'The PHP <code>escapeshellarg()</code> function is enabled.',
		];
		
		$otherComponents[] = [
			'type'              => 'component',
			'name'              => 'PHP exec() function',
			'required'          => true,
			'isOk'              => isFunctionEnabled('exec'),
			'permanentChecking' => true,
			'warning'           => 'The PHP <code>exec()</code> function must be enabled.',
			'success'           => 'The PHP <code>exec()</code> function is enabled.',
		];
		
		return array_merge($components, $otherComponents);
	}
}
