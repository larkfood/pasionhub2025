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

namespace App\Http\Middleware\Install;

trait CheckInstallation
{
	/**
	 * Check if the website has already been installed
	 *
	 * @return bool
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	protected function isInstalled(): bool
	{
		if ($this->installationIsComplete()) {
			createTheInstalledFile(true);
			$this->clearInstallationSession();
		}
		
		// Check if the app is installed
		return appIsInstalled();
	}
	
	/**
	 * @return bool
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	protected function isNotInstalled(): bool
	{
		return !$this->isInstalled();
	}
	
	/**
	 * Check if installation is processing
	 *
	 * @return bool
	 */
	protected function installationIsInProgress(): bool
	{
		return (
			!empty(session('databaseImported'))
			|| !empty(session('cronJobsInfoSeen'))
			|| !empty(session('installationCompleted'))
		);
	}
	
	/**
	 * @return bool
	 */
	protected function installationIsNotInProgress(): bool
	{
		return !$this->installationIsInProgress();
	}
	
	// PRIVATE
	
	/**
	 * Check if the installation is complete
	 * If the session contains "installationCompleted" which is equal to 1, this means that the website has just been installed.
	 *
	 * @return bool
	 */
	private function installationIsComplete(): bool
	{
		return (session('installationCompleted') == 1);
	}
	
	/**
	 * Clear the installation session
	 * Remove the "installationCompleted" key from the session
	 *
	 * @return void
	 */
	private function clearInstallationSession(): void
	{
		session()->forget('installationCompleted');
		session()->flush();
	}
}
