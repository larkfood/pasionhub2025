<?php

use App\Exceptions\Custom\CustomException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

// Get the current migration list
$currentMigrationsNames = [
	'create_languages_table'              => '01_00_create_languages_table',
	'create_advertising_table'            => '02_00_create_advertising_table',
	'create_blacklist_table'              => '03_00_create_blacklist_table',
	'create_cache_table'                  => '04_00_create_cache_table',
	'create_categories_table'             => '05_01_create_categories_table',
	'create_fields_table'                 => '05_02_create_fields_table',
	'create_fields_options_table'         => '05_03_create_fields_options_table',
	'create_category_field_table'         => '05_04_create_category_field_table',
	'create_currencies_table'             => '06_00_create_currencies_table',
	'create_home_sections_table'          => '07_00_create_home_sections_table',
	'create_meta_tags_table'              => '08_00_create_meta_tags_table',
	'create_packages_table'               => '09_00_create_packages_table',
	'create_pages_table'                  => '10_00_create_pages_table',
	'create_payment_methods_table'        => '11_00_create_payment_methods_table',
	'create_report_types_table'           => '12_00_create_report_types_table',
	'create_settings_table'               => '13_00_create_settings_table',
	'create_countries_table'              => '14_01_create_countries_table',
	'create_subadmin1_table'              => '14_02_create_subadmin1_table',
	'create_subadmin2_table'              => '14_03_create_subadmin2_table',
	'create_cities_table'                 => '14_04_create_cities_table',
	'create_permission_tables'            => '15_00_create_permission_tables',
	'create_users_table'                  => '16_00_create_users_table',
	'create_posts_table'                  => '17_01_create_posts_table',
	'create_pictures_table'               => '17_02_create_pictures_table',
	'create_post_values_table'            => '17_03_create_post_values_table',
	'create_saved_posts_table'            => '18_00_create_saved_posts_table',
	'create_saved_search_table'           => '19_00_create_saved_search_table',
	'create_threads_table'                => '20_01_create_threads_table',
	'create_threads_messages_table'       => '20_02_create_threads_messages_table',
	'create_threads_participants_table'   => '20_03_create_threads_participants_table',
	'create_payments_table'               => '21_00_create_payments_table',
	'create_personal_access_tokens_table' => '22_00_create_personal_access_tokens_table',
	'create_jobs_table'                   => '23_00_create_jobs_table',
];

// Get the current migrations files
$currentMigrationsFiles = collect($currentMigrationsNames)
	->mapWithKeys(function ($migration) {
		$migrationsDir = str(database_path('migrations'))->finish('/')->toString();
		$migrationFilePath = $migrationsDir . $migration . '.php';
		
		return [$migrationFilePath => $migration];
	});

// ===| FILES |===
try {
	
	$oldDir = resource_path('lang/');
	$newDir = resource_path('lang-depreciated/');
	if (File::exists($oldDir)) {
		File::moveDirectory($oldDir, $newDir, true);
	}
	
	// 'continents' table files
	File::delete(app_path('Models/Continent.php'));
	File::delete(app_path('Observers/ContinentObserver.php'));
	File::delete(database_path('seeders/ContinentSeeder.php'));
	
	// 'genders' table files
	File::delete(app_path('Http/Controllers/Web/Admin/GenderController.php'));
	File::delete(app_path('Http/Requests/Admin/GenderRequest.php'));
	File::delete(app_path('Http/Resources/GenderResource.php'));
	File::delete(app_path('Models/Gender.php'));
	File::delete(app_path('Observers/GenderObserver.php'));
	File::delete(database_path('seeders/GenderSeeder.php'));
	
	// 'post_types' table files
	File::delete(app_path('Http/Controllers/Web/Admin/PostTypeController.php'));
	File::delete(app_path('Http/Requests/Admin/PostTypeRequest.php'));
	File::delete(app_path('Http/Resources/PostTypeResource.php'));
	File::delete(app_path('Models/PostType.php'));
	File::delete(app_path('Observers/PostTypeObserver.php'));
	File::delete(database_path('seeders/PostTypeSeeder.php'));
	
	// 'user_types' table files
	File::delete(app_path('Http/Resources/UserTypeResource.php'));
	File::delete(app_path('Models/UserType.php'));
	File::delete(database_path('seeders/UserTypeSeeder.php'));
	
	// 'list' & 'single' settings files
	File::delete(app_path('Models/Setting/ListSetting.php'));
	File::delete(app_path('Models/Setting/SingleSetting.php'));
	File::delete(app_path('Observers/Traits/Setting/ListTrait.php'));
	File::delete(app_path('Observers/Traits/Setting/SingleTrait.php'));
	
	if (config('plugins.domainmapping.installed')) {
		File::delete(base_path('extras/plugins/domainmapping/app/Models/Setting/ListSetting.php'));
		File::delete(base_path('extras/plugins/domainmapping/app/Models/Setting/SingleSetting.php'));
		File::delete(base_path('extras/plugins/domainmapping/app/Observers/Traits/Setting/ListTrait.php'));
		File::delete(base_path('extras/plugins/domainmapping/app/Observers/Traits/Setting/SingleTrait.php'));
	}
	
	File::deleteDirectory(app_path('Exceptions/Traits/'));
	File::deleteDirectory(app_path('Models/Setting/Traits/'));
	// @UpdateNote: The lines below need to be commented
	// if this directory is reintroduced in a future version
	File::deleteDirectory(app_path('Macros/'));
	
	File::delete(app_path('config/dotenv-editor.php'));
	File::deleteDirectory(storage_path('dotenv-editor/'));
	
	File::delete(resource_path('views/install/site_info/mailersend.blade.php'));
	File::delete(resource_path('views/install/site_info/mailgun.blade.php'));
	File::delete(resource_path('views/install/site_info/postmark.blade.php'));
	File::delete(resource_path('views/install/site_info/sendmail.blade.php'));
	File::delete(resource_path('views/install/site_info/ses.blade.php'));
	File::delete(resource_path('views/install/site_info/smtp.blade.php'));
	File::delete(resource_path('views/install/site_info/sparkpost.blade.php'));
	
	// Delete old migrations files ============================================================
	// Get all (current & old) migrations files path
	$allMigrationsFiles = collect(File::glob(database_path('migrations') . '/*.php'));
	
	// Get obsolete migrations files (to delete)
	$fnIsNotObsolete = fn ($filePath) => $currentMigrationsFiles->keys()->contains($filePath);
	$obsoleteMigrationsFiles = $allMigrationsFiles->reject($fnIsNotObsolete)->toArray();
	
	// Delete the unused migrations files
	File::delete($obsoleteMigrationsFiles);
	// ========================================================================================
	
} catch (\Exception $e) {
}

// ===| DATABASE |===
try {
	
	// Drop backed enums tables if exists
	Schema::dropIfExists('continents');
	Schema::dropIfExists('gender');
	Schema::dropIfExists('post_types');
	Schema::dropIfExists('user_types');
	
	// Get the 'single' setting value
	$singleSetting = \App\Models\Setting::where('key', 'single')->first();
	
	// Convert 'single' setting 'value' column from array to string
	$singleSettingValue = !empty($singleSetting) ? $singleSetting->value : null;
	$singleSettingValue = !empty($singleSettingValue)
		? (is_array($singleSettingValue) ? json_encode($singleSettingValue) : $singleSettingValue)
		: null;
	
	// Add 'listing_form' as new setting
	$setting = \App\Models\Setting::where('key', 'listing_form')->first();
	if (empty($setting)) {
		$data = [
			'key'         => 'listing_form',
			'name'        => 'Listing Form',
			'field'       => null,
			'value'       => $singleSettingValue,
			'description' => 'Listing Form Options',
			'parent_id'   => null,
			'lft'         => 6,
			'rgt'         => 7,
			'depth'       => 1,
			'active'      => 1,
		];
		DB::table('settings')->insert($data);
	}
	
	// Add 'listing_page' as new setting
	$setting = \App\Models\Setting::where('key', 'listing_page')->first();
	if (empty($setting)) {
		$data = [
			'key'         => 'listing_page',
			'name'        => 'Listing Page',
			'field'       => null,
			'value'       => $singleSettingValue,
			'description' => 'Listing Details Page Options',
			'parent_id'   => 0,
			'lft'         => 10,
			'rgt'         => 11,
			'depth'       => 1,
			'active'      => 1,
		];
		DB::table('settings')->insert($data);
	}
	
	// Delete the 'single' setting (If exists)
	if (!empty($singleSetting)) {
		$singleSetting->delete();
	}
	
	// settings (rename 'list' to 'listings_list')
	$setting = \App\Models\Setting::where('key', 'list')->first();
	if (!empty($setting)) {
		$setting->key = 'listings_list';
		$setting->name = 'Listings List';
		$setting->description = 'Listings List Options';
		$setting->lft = 8;
		$setting->rgt = 9;
		$setting->save();
	}
	
	// settings (app)
	$setting = \App\Models\Setting::where('key', 'app')->first();
	if (!empty($setting)) {
		$setting->description = 'Application Global Options';
		$setting->save();
	}
	
	// settings (optimization)
	$setting = \App\Models\Setting::where('key', 'optimization')->first();
	if (!empty($setting)) {
		$setting->description = 'Optimization Options';
		$setting->save();
	}
	
	// settings (seo)
	$setting = \App\Models\Setting::where('key', 'seo')->first();
	if (!empty($setting)) {
		$setting->description = 'SEO Options';
		$setting->save();
	}
	
	// settings (cron)
	$setting = \App\Models\Setting::where('key', 'cron')->first();
	if (!empty($setting)) {
		$setting->description = 'Cron Job Options';
		$setting->save();
	}
	
	// permissions
	DB::table('permissions')->where('name', 'LIKE', 'gender%')->delete();
	DB::table('permissions')->where('name', 'LIKE', 'post-type%')->delete();
	
	// Delete the enums migrations (Optional)
	$obsoleteMigrationsNames = [
		'create_continents_table',
		'create_gender_table',
		'create_post_types_table',
		'create_user_types_table',
	];
	foreach ($obsoleteMigrationsNames as $endingOfOldName => $newName) {
		DB::table('migrations')->where('migration', 'LIKE', '%' . $endingOfOldName)->delete();
	}
	
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
