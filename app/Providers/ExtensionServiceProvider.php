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

namespace App\Providers;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use Spatie\Dropbox\Client as DropboxClient;
use Spatie\FlysystemDropbox\DropboxAdapter;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;

class ExtensionServiceProvider extends ServiceProvider
{
	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot(): void
	{
		// Filesystem Adapter for: Dropbox
		Storage::extend('dropbox', function ($app, $config) {
			$client = new DropboxClient($config['authorization_token']);
			$adapter = new DropboxAdapter($client);
			
			return new FilesystemAdapter(
				new Filesystem($adapter, $config),
				$adapter,
				$config
			);
		});
		
		// Additional Symfony Transports
		// Symfony Transport for: Brevo
		Mail::extend('brevo', function () {
			return (new BrevoTransportFactory)->create(
				new Dsn(
					'brevo+api',
					'default',
					config('services.brevo.key')
				)
			);
		});
	}
}
