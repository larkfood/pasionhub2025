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

use App\Exceptions\Custom\CustomException;
use App\Helpers\Arr;
use App\Helpers\DotenvEditor;
use App\Http\Controllers\Web\Setup\Install\Traits\Checker\Components\PhpTrait;

/**
 * Create the "installed" file
 *
 * @param bool $stopOnException
 * @return void
 * @throws \App\Exceptions\Custom\CustomException
 */
function createTheInstalledFile(bool $stopOnException = false): void
{
	$filePath = storage_path('installed');
	$content = '';
	
	if (!file_exists($filePath)) {
		try {
			file_put_contents($filePath, $content);
		} catch (\Throwable $e) {
		}
	}
	
	if (!file_exists($filePath)) {
		try {
			$fp = fopen($filePath, 'w');
			fwrite($fp, $content);
			fclose($fp);
		} catch (\Throwable $e) {
			if ($stopOnException) {
				throw new CustomException($e->getMessage());
			}
		}
	}
}

/**
 * Check if the app's .env file exists
 *
 * @return bool
 */
function appEnvFileExists(): bool
{
	return file_exists(base_path('.env'));
}

/**
 * Check if the app's installation files exist
 *
 * @return bool
 */
function appInstallFilesExist(): bool
{
	// Check if the '.env' and 'storage/installed' files exist
	if (appEnvFileExists() && file_exists(storage_path('installed'))) {
		return true;
	}
	
	return false;
}

/**
 * Check if the app is installed
 *
 * @return bool
 */
function appIsInstalled(): bool
{
	// Check if the app's installation files exist
	return appInstallFilesExist();
}

/**
 * Check if the app is being installed or upgraded
 *
 * @return bool
 */
function appIsBeingInstalledOrUpgraded(): bool
{
	return (appIsBeingInstalled() || appIsBeingUpgraded());
}

/**
 * Check if the app is being installed
 *
 * @return bool
 */
function appIsBeingInstalled(): bool
{
	return str_contains(currentRouteAction(), '\Setup\Install\\');
}

/**
 * Check if the app is being upgraded
 *
 * @return bool
 */
function appIsBeingUpgraded(): bool
{
	return str_contains(currentRouteAction(), '\Setup\Update\\');
}

/**
 * Check if an update is available
 *
 * @return bool
 */
function updateIsAvailable(): bool
{
	// Check if the '.env' file exists
	if (!appEnvFileExists()) {
		return false;
	}
	
	$updateIsAvailable = false;
	
	// Get eventual new version value & the current (installed) version value
	$lastVersion = getLatestVersion();
	$currentVersion = getCurrentVersion();
	
	// Check the update
	if (version_compare($lastVersion, $currentVersion, '>')) {
		$updateIsAvailable = true;
	}
	
	return $updateIsAvailable;
}

/**
 * Get the current version value
 *
 * @return null|string
 */
function getCurrentVersion(): ?string
{
	$version = DotenvEditor::getValue('APP_VERSION');
	
	return checkAndUseSemVer($version);
}

/**
 * Get the app's latest version
 *
 * @return string
 */
function getLatestVersion(): string
{
	return checkAndUseSemVer(config('version.app'));
}

/**
 * Get a given update file version
 *
 * @param string $filePath
 * @return string
 */
function getUpdateFileVersion(string $filePath): string
{
	return str($filePath)->lower()->between('update-', '.php')->toString();
}

/**
 * Check and use semver version num format
 *
 * @param string|null $version
 * @return string
 */
function checkAndUseSemVer(?string $version): string
{
	$defaultSemver = '0.0.0';
	
	if (empty($version)) {
		return $defaultSemver;
	}
	
	$semver = null;
	
	if (empty($semver)) {
		$numPattern = '([0-9]+)';
		$hasValidFormat = preg_match('#^' . $numPattern . '\.' . $numPattern . '\.' . $numPattern . '$#', $version);
		$semver = $hasValidFormat ? $version : $semver;
	}
	if (empty($semver)) {
		$hasValidFormat = preg_match('#^' . $numPattern . '\.' . $numPattern . '$#', $version);
		$semver = $hasValidFormat ? $version . '.0' : $semver;
	}
	if (empty($semver)) {
		$hasValidFormat = preg_match('#^' . $numPattern . '$#', $version);
		$semver = $hasValidFormat ? $version . '.0.0' : $semver;
	}
	if (empty($semver)) {
		$semver = $defaultSemver;
	}
	
	return $semver;
}

/**
 * @param string $phpCmd
 * @param string|null $schedule
 * @param string|null $return
 * @param bool $withHint
 * @param bool $wrapped
 * @return string|null
 */
function getRightPathsForCmd(
	string  $phpCmd,
	?string $schedule = '* * * * *',
	?string $return = '>> /dev/null 2>&1',
	bool    $withHint = true,
	bool    $wrapped = true
): ?string
{
	$splitCmd = explode(' ', $phpCmd, 2);
	
	// Get the script path
	$scriptName = trim(Arr::first($splitCmd));
	if ($scriptName == 'php') {
		$cmd = trim(Arr::last($splitCmd));
		$splitCmd = explode(' ', $cmd, 2);
		$scriptName = trim(Arr::first($splitCmd));
	}
	$scriptPath = base_path($scriptName);
	if (!file_exists($scriptPath)) return null;
	$scriptPath = relativeAppPath($scriptPath);
	
	// Get the command
	$cmd = trim(Arr::last($splitCmd));
	$splitCmd = explode('>', $cmd, 2);
	$cmd = trim(Arr::first($splitCmd));
	
	// Get PHP bin path
	$phpBinaryDefaultPath = '/path/to/php';
	$phpTrait = new class {
		use PhpTrait;
	};
	$phpBinaryPath = $phpTrait->getPhpBinaryPath();
	$requiredPhpVersion = $phpTrait->getComposerRequiredPhpVersion();
	
	// Get hint when the PHP binary path cannot be found
	$hint = '';
	if (empty($phpBinaryPath)) {
		$phpBinaryPath = '/usr/bin/php';
		if ($withHint) {
			$hint = trans('messages.cron_jobs_hint', ['phpVersion' => $requiredPhpVersion]);
			if ($wrapped) {
				$alertBg = isAdminPanel() ? 'alert-light-warning' : 'alert-warning';
				$hint .= '<div class="alert ' . $alertBg . '">';
				$hint .= $hint;
				$hint .= '</div>';
			}
		}
	}
	$phpBinaryPath = !isDemoDomain() ? $phpBinaryPath : $phpBinaryDefaultPath;
	
	// Schedule
	$schedule = is_null($schedule) ? '* * * * *' : $schedule;
	
	// Return
	$return = is_null($return) ? '>> /dev/null 2>&1' : $return;
	$return = (trim($schedule) == '') ? '' : $return;
	
	// Get cron job command
	$cron = $schedule . ' ' . $phpBinaryPath . ' ' . $scriptPath . ' ' . $cmd . ' ' . $return;
	$cron = trim($cron);
	
	// Build output
	if ($wrapped) {
		$out = '<div class="alert alert-light">';
		$out .= '<code>' . $cron . '</code>';
		$out .= '</div>';
	} else {
		$out = '<code>' . $cron . '</code><br><br>';
	}
	$out .= $hint;
	
	return $out;
}

/**
 * @return string
 */
function getHintForPhpCmd(): string
{
	// Get PHP required version
	$phpTrait = new class {
		use PhpTrait;
	};
	$requiredPhpVersion = $phpTrait->getComposerRequiredPhpVersion();
	
	// Get hint for PHP binary path
	$alertBg = isAdminPanel() ? 'alert-light-warning' : 'alert-warning';
	$hint = '<div class="alert ' . $alertBg . '">';
	$hint .= trans('messages.cron_jobs_hint', ['phpVersion' => $requiredPhpVersion]);
	$hint .= '</div>';
	
	return $hint;
}

/**
 * @param string|null $purchaseCode
 * @param string|null $itemId
 * @return string
 */
function getPurchaseCodeApiEndpoint(?string $purchaseCode, string $itemId = null): string
{
	$baseUrl = getAsString(config('larapen.core.purchaseCodeCheckerUrl'));
	
	return $baseUrl . $purchaseCode . '&domain=' . getDomain() . '&item_id=' . $itemId;
}
