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

use App\Http\Controllers\Web\Front\Traits\TravelWizardTrait;
use App\Http\Controllers\Web\Setup\Install\CronController;
use App\Http\Controllers\Web\Setup\Install\DbImportController;
use App\Http\Controllers\Web\Setup\Install\DbInfoController;
use App\Http\Controllers\Web\Setup\Install\FinishController;
use App\Http\Controllers\Web\Setup\Install\RequirementsController;
use App\Http\Controllers\Web\Setup\Install\SiteInfoController;
use App\Http\Controllers\Web\Setup\Install\StartingController;

trait WizardTrait
{
	use TravelWizardTrait;
	
	/**
	 * Get the installation navigation links
	 * Note: GET method routes
	 *
	 * @return array
	 */
	protected function getNavItems(): array
	{
		$installUrl = $this->baseUrl . '/install';
		$uriPath = request()->segment($this->stepsSegment);
		$envFileErrorMessage = trans('messages.database_env_file_required');
		
		$navItems = [];
		
		// Install Boot URL
		// Not included in the nav. items
		// Auto-redirect to the system requirements checking step
		$navItems[StartingController::class] = [
			'step'        => 0,
			'label'       => null,
			'icon'        => null,
			'url'         => $installUrl,
			'class'       => '',
			'parentClass' => '',
			'included'    => false,
			'lockMessage' => null,
			'unlocked'    => true, // Unlocked by default
		];
		
		$navItems[RequirementsController::class] = [
			'step'        => 1,
			'label'       => trans('messages.requirements_checking'),
			'icon'        => 'bi bi-info-circle',
			'url'         => $installUrl . '/system_requirements',
			'class'       => '',
			'parentClass' => '',
			'included'    => true,
			'lockMessage' => null,
			'unlocked'    => true, // Unlocked by default
		];
		
		$navItems[SiteInfoController::class] = [
			'step'        => 2,
			'label'       => trans('messages.site_info'),
			'icon'        => 'bi bi-gear',
			'url'         => $installUrl . '/site_info',
			'class'       => '',
			'parentClass' => '',
			'included'    => true,
			'lockMessage' => trans('messages.requirements_checking_required'),
			'unlocked'    => !empty(session('requirementsVerified')),
		];
		
		$navItems[DbInfoController::class] = [
			'step'        => 3,
			'label'       => trans('messages.database_info'),
			'icon'        => 'bi bi-plugin',
			'url'         => $installUrl . '/database_info',
			'class'       => '',
			'parentClass' => '',
			'included'    => true,
			'lockMessage' => (!appEnvFileExists() && !empty(session('databaseInfo')))
				? $envFileErrorMessage
				: trans('messages.site_info_required'),
			'unlocked'    => (
				!empty(session('requirementsVerified'))
				&& !empty(session('siteInfo'))
			),
		];
		
		$navItems[DbImportController::class] = [
			'step'        => 4,
			'label'       => trans('messages.database_import'),
			'icon'        => 'bi bi-database-up',
			'url'         => $installUrl . '/database_import',
			'class'       => '',
			'parentClass' => '',
			'included'    => true,
			'lockMessage' => !appEnvFileExists()
				? $envFileErrorMessage
				: trans('messages.database_info_required'),
			'unlocked'    => (
				!empty(session('requirementsVerified'))
				&& !empty(session('siteInfo'))
				&& !empty(session('databaseInfo'))
				&& appEnvFileExists()
			),
		];
		
		$navItems[CronController::class] = [
			'step'        => 5,
			'label'       => trans('messages.cron_jobs'),
			'icon'        => 'bi bi-clock',
			'url'         => $installUrl . '/cron_jobs',
			'class'       => '',
			'parentClass' => '',
			'included'    => true,
			'lockMessage' => !appEnvFileExists()
				? $envFileErrorMessage
				: trans('messages.database_import_required'),
			'unlocked'    => (
				!empty(session('requirementsVerified'))
				&& !empty(session('siteInfo'))
				&& !empty(session('databaseInfo'))
				&& appEnvFileExists()
				&& !empty(session('databaseImported'))
			),
		];
		
		$navItems[FinishController::class] = [
			'step'        => 6,
			'label'       => trans('messages.finish'),
			'icon'        => 'bi bi-check-circle',
			'url'         => $installUrl . '/finish',
			'class'       => '',
			'parentClass' => '',
			'included'    => true,
			'lockMessage' => !appEnvFileExists()
				? $envFileErrorMessage
				: trans('messages.cron_jobs_required'),
			'unlocked'    => (
				!empty(session('requirementsVerified'))
				&& !empty(session('siteInfo'))
				&& !empty(session('databaseInfo'))
				&& appEnvFileExists()
				&& !empty(session('databaseImported'))
				&& !empty(session('cronJobsInfoSeen'))
			),
		];
		
		// Save the original menu before formatting it
		$this->rawNavItems = $navItems;
		
		return $this->formatAllNavItems($navItems, $uriPath);
	}
}
