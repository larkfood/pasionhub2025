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

namespace App\Providers\AppService\ConfigTrait;

trait BackupConfig
{
	private function updateBackupConfig(?array $settings = []): void
	{
		config()->set('backup.backup.name', config('app.name'));
		config()->set('backup.monitor_backups.name', config('app.name'));
		
		// Set the backup system disks
		$disks = config('backup.backup.destination.disks');
		if (data_get($settings, 'storage_disk') == '1') {
			$disks = [config('filesystems.cloud')];
		} else if (data_get($settings, 'storage_disk') == '2') {
			$disks = array_merge($disks, [config('filesystems.cloud')]);
		}
		$disks = array_unique($disks);
		config()->set('backup.backup.destination.disks', $disks);
		
		// Flags (Depreciated)
		config()->set('backup.backup.admin_flags', [
			'--disable-notifications' => (bool)data_get($settings, 'disable_notifications'),
		]);
		
		// Notifications
		config()->set('backup.notifications.mail.from.address', config('mail.from.address'));
		config()->set('backup.notifications.mail.from.name', config('mail.from.name'));
		if (config('settings.app.email')) {
			config()->set('backup.notifications.mail.to', config('settings.app.email'));
		}
		
		// Backup Cleanup Settings
		$keepAllBackupsForDays = (int)data_get($settings, 'keep_all_backups_for_days');
		if ($keepAllBackupsForDays > 0) {
			config()->set('backup.cleanup.default_strategy.keep_all_backups_for_days', $keepAllBackupsForDays);
		}
		$keepDailyBackupsForDays = (int)data_get($settings, 'keep_daily_backups_for_days');
		if ($keepDailyBackupsForDays > 0) {
			config()->set('backup.cleanup.default_strategy.keep_daily_backups_for_days', $keepDailyBackupsForDays);
		}
		$keepWeeklyBackupsForWeeks = (int)data_get($settings, 'keep_weekly_backups_for_weeks');
		if ($keepWeeklyBackupsForWeeks > 0) {
			config()->set('backup.cleanup.default_strategy.keep_weekly_backups_for_weeks', $keepWeeklyBackupsForWeeks);
		}
		$keepMonthlyBackupsForMonths = (int)data_get($settings, 'keep_monthly_backups_for_months');
		if ($keepMonthlyBackupsForMonths > 0) {
			config()->set('backup.cleanup.default_strategy.keep_monthly_backups_for_months', $keepMonthlyBackupsForMonths);
		}
		$keepYearlyBackupsForYears = (int)data_get($settings, 'keep_yearly_backups_for_years');
		if ($keepYearlyBackupsForYears > 0) {
			config()->set('backup.cleanup.default_strategy.keep_yearly_backups_for_years', $keepYearlyBackupsForYears);
		}
		$maximumStorageInMegabytes = (int)data_get($settings, 'maximum_storage_in_megabytes');
		if ($maximumStorageInMegabytes > 0) {
			config()->set('backup.cleanup.default_strategy.delete_oldest_backups_when_using_more_megabytes_than', $maximumStorageInMegabytes);
		}
		
		// Monitor Backups
		$maximumAgeInDays = ($keepAllBackupsForDays > 0) ? $keepAllBackupsForDays : 1;
		$maximumStorageInMegabytes = ($maximumStorageInMegabytes > 0) ? $maximumStorageInMegabytes : 5000;
		$monitorBackups = [
			[
				'name'          => config('app.name'),
				'disks'         => $disks,
				'health_checks' => [
					\Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class          => $maximumAgeInDays,
					\Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => $maximumStorageInMegabytes,
				],
			],
		];
		config()->set('backup.monitor_backups', $monitorBackups);
	}
}
