<?php

use App\Exceptions\Custom\CustomException;
use App\Helpers\DotenvEditor;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

// ===| FILES |===
try {
	
	File::delete(app_path('Helpers/Functions/migration.php'));
	
	// Laravel 11.*
	File::delete(app_path('Console/Commands/Inspire.php'));
	File::delete(app_path('Http/Middleware/VerifyCsrfToken.php'));
	File::delete(app_path('Providers/RouteServiceProvider.php'));
	File::delete(config_path('hashing.php'));
	File::delete(database_path('migrations/2020_10_12_160715_create_password_resets_table.php'));
	File::delete(database_path('migrations/2020_10_12_160715_create_sessions_table.php'));
	File::delete(base_path('tests/CreatesApplication.php'));
	
	//...
	File::delete(resource_path('views/errors/405.blade.php'));
	File::delete(resource_path('views/errors/408.blade.php'));
	File::delete(resource_path('views/errors/419.blade.php'));
	File::delete(resource_path('views/errors/429.blade.php'));
	
	File::deleteDirectory(database_path('upgrade/'));
	File::delete(File::glob(database_path('migrations') . '/2019_12_14_*.php'));
	File::delete(File::glob(database_path('migrations') . '/2020_10_12_*.php'));
	
	// Assets
	$sourceFile = public_path('css/custom.css');
	$targetFile = public_path('dist/public/custom.css');
	if (File::exists($sourceFile)) {
		if (File::exists($targetFile)) {
			File::delete($targetFile);
		}
		File::move($sourceFile, $targetFile);
	}
	
	File::deleteDirectory(public_path('assets/bootstrap/css/'));
	File::deleteDirectory(public_path('assets/bootstrap/fonts/'));
	File::deleteDirectory(public_path('assets/bootstrap/js/'));
	File::deleteDirectory(public_path('assets/plugins/pnotify/5.2.0/modules/bootstrap4/'));
	File::deleteDirectory(public_path('css/'));
	File::deleteDirectory(public_path('js/'));
	File::deleteDirectory(public_path('assets/plugins/SocialShare/'));
	
	File::delete(storage_path('app/public/app/default/ico/apple-touch-icon-57-precomposed.png'));
	File::delete(storage_path('app/public/app/default/ico/apple-touch-icon-72-precomposed.png'));
	File::delete(storage_path('app/public/app/default/ico/apple-touch-icon-114-precomposed.png'));
	File::delete(storage_path('app/public/app/default/ico/apple-touch-icon-144-precomposed.png'));
	
	// .ENV
	$needToBeSaved = false;
	if (DotenvEditor::keyExists('PURCHASE_CODE')) {
		$envPurchaseCode = DotenvEditor::getValue('PURCHASE_CODE');
		if (empty($envPurchaseCode)) {
			$purchaseCode = config('settings.app.purchase_code');
			DotenvEditor::setKey('PURCHASE_CODE', $purchaseCode);
			$needToBeSaved = true;
		}
	}
	if (DotenvEditor::keyExists('CACHE_DRIVER')) {
		$cacheStore = DotenvEditor::getValue('CACHE_DRIVER');
		DotenvEditor::setKey('CACHE_STORE', $cacheStore);
		DotenvEditor::deleteKey('CACHE_DRIVER');
		$needToBeSaved = true;
	}
	if (DotenvEditor::keyExists('DATABASE_URL')) {
		$dbUrl = DotenvEditor::getValue('DATABASE_URL');
		DotenvEditor::setKey('DB_URL', $dbUrl);
		DotenvEditor::deleteKey('DATABASE_URL');
		$needToBeSaved = true;
	}
	if ($needToBeSaved) {
		DotenvEditor::save();
	}
	
} catch (\Exception $e) {
}

// ===| DATABASE |===
try {
	
	// password_reset_tokens
	// Rename the 'password_resets' table to 'password_reset_tokens'
	try {
		if (Schema::hasTable('password_resets') && !Schema::hasTable('password_reset_tokens')) {
			Schema::rename('password_resets', 'password_reset_tokens');
		}
	} catch (\Throwable $e) {
	}
	
	// Make sure that the 'password_resets' table is renamed to 'password_reset_tokens'
	Schema::dropIfExists('password_resets');
	
	if (!Schema::hasTable('password_reset_tokens')) {
		Schema::create('password_reset_tokens', function (Blueprint $table) {
			$table->string('email', 191)->nullable();
			$table->string('phone', 191)->nullable();
			$table->string('phone_country', 2)->nullable();
			$table->string('token', 191)->nullable();
			$table->timestamp('created_at')->nullable();
			
			$table->index(['email']);
			$table->index(['phone']);
			$table->index(['token']);
		});
	}
	
	// cache_locks
	if (!Schema::hasTable('cache_locks')) {
		Schema::create('cache_locks', function (Blueprint $table) {
			$table->string('key')->primary();
			$table->string('owner');
			$table->integer('expiration');
		});
	}
	
	// jobs, job_batches, failed_jobs
	if (!Schema::hasTable('jobs') || !Schema::hasTable('job_batches') || !Schema::hasTable('failed_jobs')) {
		Schema::dropIfExists('jobs');
		Schema::dropIfExists('job_batches');
		Schema::dropIfExists('failed_jobs');
		
		// migrations
		$res = DB::table('migrations')->where('migration', 'LIKE', '%create_jobs_table%')->delete();
		
		// jobs, job_batches, failed_jobs
		$path = 'database/migrations/30_create_jobs_table.php';
		if (file_exists(base_path($path))) {
			Artisan::call('migrate', ['--path' => $path, '--force' => true]);
		}
	}
	
	// users
	if (!Schema::hasColumn('users', 'dark_mode') && Schema::hasColumn('users', 'accept_marketing_offers')) {
		Schema::table('users', function (Blueprint $table) {
			$table->boolean('dark_mode')->nullable()->default('0')->after('accept_marketing_offers');
		});
	}
	
	// settings (social_share)
	$setting = \App\Models\Setting::where('key', 'social_share')->first();
	if (empty($setting)) {
		$data = [
			'key'         => 'social_share',
			'name'        => 'Social Share',
			'field'       => null,
			'value'       => null,
			'description' => 'Social Media Sharing',
			'parent_id'   => null,
			'lft'         => 24,
			'rgt'         => 25,
			'depth'       => 1,
			'active'      => 1,
		];
		DB::table('settings')->insert($data);
	}
	
	// settings (pagination)
	$setting = \App\Models\Setting::where('key', 'pagination')->first();
	if (empty($setting)) {
		$data = [
			'key'         => 'pagination',
			'name'        => 'Pagination',
			'field'       => null,
			'value'       => null,
			'description' => 'Pagination & Limit Options',
			'parent_id'   => 0,
			'lft'         => 30,
			'rgt'         => 31,
			'depth'       => 1,
			'active'      => 1,
		];
		DB::table('settings')->insert($data);
	}
	
	// categories (icon_class)
	$categoriesArray = [
		'automobiles'          => ['old' => 'fas fa-car', 'new' => 'fa-solid fa-car'],
		'phones-and-tablets'   => ['old' => 'fas fa-mobile-alt', 'new' => 'fa-solid fa-mobile-screen-button'],
		'electronics'          => ['old' => 'fas fa-laptop', 'new' => 'fa-solid fa-laptop'],
		'furniture-appliances' => ['old' => 'fas fa-couch', 'new' => 'fa-solid fa-couch'],
		'real-estate'          => ['old' => 'fas fa-home', 'new' => 'fa-solid fa-house'],
		'animals-and-pets'     => ['old' => 'fas fa-paw', 'new' => 'fa-solid fa-paw'],
		'fashion'              => ['old' => 'fas fa-tshirt', 'new' => 'fa-solid fa-shirt'],
		'beauty-well-being'    => ['old' => 'fas fa-spa', 'new' => 'fa-solid fa-spa'],
		'jobs'                 => ['old' => 'fas fa-briefcase', 'new' => 'fa-solid fa-briefcase'],
		'services'             => ['old' => 'fas fa-clipboard-list', 'new' => 'fa-solid fa-clipboard-list'],
		'learning'             => ['old' => 'fas fa-graduation-cap', 'new' => 'fa-solid fa-graduation-cap'],
		'local-events'         => ['old' => 'far fa-calendar-alt', 'new' => 'fa-regular fa-calendar-days'],
	];
	foreach ($categoriesArray as $slug => $iconClass) {
		$oldClass = $iconClass['old'] ?? null;
		$newClass = $iconClass['new'] ?? null;
		
		if (!empty($oldClass) && !empty($newClass)) {
			$category = \App\Models\Category::query()
				->root()
				->where('slug', $slug)
				->where('icon_class', $oldClass)
				->first();
			
			if (!empty($category)) {
				$category->icon_class = $newClass;
				$category->saveQuietly();
			}
		}
	}
	
	// migrations
	$currentMigrationsNames = [
		'create_languages_table'              => '01_create_languages_table',
		'create_advertising_table'            => '02_create_advertising_table',
		'create_blacklist_table'              => '03_create_blacklist_table',
		'create_cache_table'                  => '04_create_cache_table',
		'create_categories_table'             => '05_1_create_categories_table',
		'create_fields_table'                 => '05_2_create_fields_table',
		'create_fields_options_table'         => '05_3_create_fields_options_table',
		'create_category_field_table'         => '05_4_create_category_field_table',
		'create_continents_table'             => '06_create_continents_table',
		'create_currencies_table'             => '07_create_currencies_table',
		'create_gender_table'                 => '08_create_gender_table',
		'create_home_sections_table'          => '09_create_home_sections_table',
		'create_meta_tags_table'              => '10_create_meta_tags_table',
		'create_packages_table'               => '11_create_packages_table',
		'create_pages_table'                  => '12_create_pages_table',
		'create_payment_methods_table'        => '13_create_payment_methods_table',
		'create_post_types_table'             => '14_create_post_types_table',
		'create_report_types_table'           => '15_create_report_types_table',
		'create_settings_table'               => '16_create_settings_table',
		'create_user_types_table'             => '17_create_user_types_table',
		'create_countries_table'              => '18_create_countries_table',
		'create_subadmin1_table'              => '19_create_subadmin1_table',
		'create_subadmin2_table'              => '20_create_subadmin2_table',
		'create_cities_table'                 => '21_create_cities_table',
		'create_users_table'                  => '22_1_create_users_table',
		'create_permission_tables'            => '23_create_permission_tables',
		'create_posts_table'                  => '24_1_create_posts_table',
		'create_pictures_table'               => '24_2_create_pictures_table',
		'create_post_values_table'            => '24_3_create_post_values_table',
		'create_saved_posts_table'            => '25_create_saved_posts_table',
		'create_saved_search_table'           => '26_create_saved_search_table',
		'create_threads_table'                => '27_1_create_threads_table',
		'create_threads_messages_table'       => '27_2_create_threads_messages_table',
		'create_threads_participants_table'   => '27_3_create_threads_participants_table',
		'create_payments_table'               => '28_create_payments_table',
		'create_personal_access_tokens_table' => '29_create_personal_access_tokens_table',
		'create_jobs_table'                   => '30_create_jobs_table',
	];
	
	// Get the current migrations files
	$currentMigrationsFiles = collect($currentMigrationsNames)
		->mapWithKeys(function ($migration) {
			$migrationsDir = str(database_path('migrations'))->finish('/')->toString();
			$migrationFilePath = $migrationsDir . $migration . '.php';
			
			return [$migrationFilePath => $migration];
		});
	
	// Get the migrations that can be saved in database
	$fnFileExists = fn ($item, $filePath) => File::exists($filePath);
	$migrationsToSaveInDb = $currentMigrationsFiles->filter($fnFileExists)->toArray();
	
	// Truncate the migrations table & Insert the migrations
	if (!empty($migrationsToSaveInDb)) {
		DB::table('migrations')->truncate();
		
		foreach ($migrationsToSaveInDb as $migrationFilePath => $migration) {
			DB::table('migrations')->insert(['migration' => $migration, 'batch' => 1]);
		}
	}
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
