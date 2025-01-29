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

namespace App\Console;

use App\Helpers\Date;
use Illuminate\Console\Scheduling\Schedule;

class Kernel
{
	public function __invoke(Schedule $schedule): void
	{
		$tz = Date::getAppTimeZone();
		
		// Pruning Batches
		// Doc: https://laravel.com/docs/11.x/queues#pruning-batches
		// Delete all batches that finished over 48 hours ago
		try {
			$schedule->command('queue:prune-batches --hours=48 --unfinished=72')->daily();
		} catch (\Throwable $e) {
		}
		
		// Deleting Expired Tokens (Resetting Password)
		// Doc: https://laravel.com/docs/11.x/passwords
		$schedule->command('auth:clear-resets')->timezone($tz)->everyFifteenMinutes();
		
		// Clear Listings
		$schedule->command('listings:purge')->timezone($tz)->hourly();
		
		// Backups
		setBackupConfig();
		$disableNotifications = (config('settings.backup.disable_notifications')) ? ' --disable-notifications' : '';
		
		// Taking Backups
		$takingBackup = config('settings.backup.taking_backup');
		if ($takingBackup != 'none') {
			$takingBackupAt = config('settings.backup.taking_backup_at');
			$takingBackupAt = ($takingBackupAt != '') ? $takingBackupAt : '00:00';
			
			if ($takingBackup == 'daily') {
				$schedule->command('backup:run' . $disableNotifications)->timezone($tz)->dailyAt($takingBackupAt);
			}
			if ($takingBackup == 'weekly') {
				$schedule->command('backup:run' . $disableNotifications)->timezone($tz)->weeklyOn(1, $takingBackupAt);
			}
			if ($takingBackup == 'monthly') {
				$schedule->command('backup:run' . $disableNotifications)->timezone($tz)->monthlyOn(1, $takingBackupAt);
			}
			if ($takingBackup == 'yearly') {
				$schedule->command('backup:run' . $disableNotifications)->timezone($tz)->yearlyOn(1, 1, $takingBackupAt);
			}
			
			// Cleaning Up Old Backups
			$schedule->command('backup:clean' . $disableNotifications)->timezone($tz)->daily();
		}
		
		// Clear Cache & Views
		if (!env('DISABLE_CACHE_AUTO_CLEAR') || (int)env('DISABLE_CACHE_AUTO_CLEAR', 0) != 1) {
			$schedule->command('cache:clear')->timezone($tz)->weeklyOn(7, '6:00');
			$schedule->command('cache:clear')->timezone($tz)->weeklyOn(7, '6:05'); // To prevent file lock issues (Optional)
			$schedule->command('view:clear')->timezone($tz)->weeklyOn(7, '6:00');
		}
	}
}
