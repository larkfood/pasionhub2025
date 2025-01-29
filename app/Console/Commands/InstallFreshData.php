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

namespace App\Console\Commands;

// Increase the server resources
$iniConfigFile = __DIR__ . '/../../Helpers/Functions/ini.php';
if (file_exists($iniConfigFile)) {
	include_once $iniConfigFile;
}

use Database\Seeders\SiteInfoSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/*
 * This command run migrations and seeders (including the site info seeder)
 * WARNING: Never run this command on production!
 * NOTE: To run this command, the application must already be installed
 *
 * USAGE:
 * php artisan app:install-fresh-data --purchaseCode=ABC123 --email=admin@domain.tld --country=US
 * Artisan::call('app:install-fresh-data', ['--purchaseCode' => 'ABC123', ..., '--confirm' => true]);
 */

class InstallFreshData extends Command
{
	private ?string $countryCode;
	private ?string $purchaseCode;
	private ?string $email;
	private ?string $password;
	
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'app:install-fresh-data
                            {--country= : The default country code}
                            {--purchaseCode= : The purchase code}
                            {--email= : The admin user\'s email address}
                            {--password= : The admin user\'s email address}
                            {--confirm : Automatically confirm the action}';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Run the fresh install seeder with array data.';
	
	/**
	 * Execute the console command.
	 *
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	public function handle()
	{
		// Allow only local & demo env
		if (!isLocalEnv() && !isDemoEnv()) {
			$this->error('This command can only be executed on local environments.');
			
			return;
		}
		// Exclude production env
		if (app()->isProduction()) {
			$this->error('This command can not be executed on production environments.');
			
			return;
		}
		// Make sure that APP_DEBUG is set to true in the .env file
		if (!config('app.debug')) {
			$this->error('Debug needs to be enabled to execute this command.');
			
			return;
		}
		// Make sure the app is installed
		if (!appIsInstalled()) {
			$this->error('The app is not installed yet. Make sure the app is installed to continue.');
			
			return;
		}
		
		// Get the command arguments values
		$countryCode = $this->option('country');
		$purchaseCode = $this->option('purchaseCode');
		$email = $this->option('email');
		$password = $this->option('password');
		$autoConfirm = $this->option('confirm');
		
		// Ask for confirmation if not auto-confirmed
		if (!$autoConfirm && !$this->confirm('Do you wish to continue?')) {
			$this->info('Action cancelled.');
			
			return;
		}
		
		// Get the site info minimum data
		$this->countryCode = $countryCode ?? 'US';
		$this->purchaseCode = $purchaseCode ?? env('PURCHASE_CODE');
		$this->email = $email ?? 'admin@domain.tld';
		$this->password = $password ?? '123456';
		
		// Display the values to the user
		$this->info('The app will be installed using these information:');
		$this->warn('Default Country Code: ' . ($this->countryCode ?? 'Not provided'));
		$this->warn('Purchase Code: ' . ($this->purchaseCode ?? 'Not provided'));
		$this->warn('Admin Email: ' . ($this->email ?? 'Not provided'));
		$this->warn('Admin Password: ' . ($this->password ?? 'Not provided'));
		
		// Ensure the testing environment is set up correctly
		Artisan::call('cache:clear');
		Artisan::call('config:clear');
		
		// Run migrations
		Artisan::call('migrate:fresh', ['--path' => '/database/migrations', '--force' => true]);
		$output = Artisan::output();
		$this->info($output);
		
		// Seed default data
		Artisan::call('db:seed', ['--force' => true]);
		$output = Artisan::output();
		$this->info($output);
		
		// Seed site info data
		$siteInfoSeeder = new SiteInfoSeeder();
		$siteInfoSeeder->run($this->getSiteInfoData());
	}
	
	/**
	 * Get site info data
	 *
	 * @return array
	 */
	private function getSiteInfoData(): array
	{
		$data = [];
		
		// settings.app
		$app = [
			'name'          => 'Site Name',
			'slogan'        => 'Your website\'s slogan',
			'purchase_code' => $this->purchaseCode,
			'email'         => $this->email,
		];
		
		// settings.localization
		$localization = [
			'default_country_code' => $this->countryCode,
		];
		
		// settings.mail
		$mail = [];
		
		// settings
		$data['settings']['app'] = $app;
		$data['settings']['localization'] = $localization;
		$data['settings']['mail'] = $mail;
		
		// user
		$data['user'] = [
			'name'         => 'Admin User',
			'email'        => $this->email,
			'password'     => $this->password,
			'country_code' => $this->countryCode,
		];
		
		return $data;
	}
}
