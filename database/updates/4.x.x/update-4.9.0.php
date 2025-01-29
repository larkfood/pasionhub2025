<?php

use App\Exceptions\Custom\CustomException;
use App\Helpers\DotenvEditor;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

// ===| FILES |===
try {
	
	File::delete(app_path('Helpers/Geo.php'));
	File::delete(app_path('Http/Controllers/Admin/CacheController.php'));
	File::delete(app_path('Http/Controllers/Admin/MaintenanceController.php'));
	File::delete(app_path('Http/Controllers/Admin/TestCronController.php'));
	File::delete(app_path('Http/Controllers/Post/PackageController.php'));
	File::delete(app_path('Http/Middleware/ClearCache.php'));
	File::delete(base_path('bootstrap/autoload.php'));
	File::delete(base_path('config/compile.php'));
	File::delete(base_path('config/elfinder.php'));
	
	File::deleteDirectory(base_path('packages/barryvdh/'));
	File::deleteDirectory(base_path('packages/larapen/admin/src/public/'), true);
	File::deleteDirectory(base_path('packages/larapen/admin/src/resources/lang/'), true);
	File::deleteDirectory(base_path('packages/larapen/admin/src/resources/views/'), true);
	File::deleteDirectory(base_path('packages/larapen/admin/src/resources/error_views/'));
	File::deleteDirectory(base_path('packages/larapen/admin/src/resources/views-elfinder/'));
	File::deleteDirectory(public_path('vendor/admin/elfinder/'));
	File::deleteDirectory(public_path('vendor/admin/colorbox/'));
	File::deleteDirectory(storage_path('database/upgrade/'));
	
	if (File::exists(public_path('uploads/app/'))) {
		File::moveDirectory(public_path('uploads/app/'), storage_path('app/public/app/'), true);
	}
	if (File::exists(public_path('uploads/files/'))) {
		File::moveDirectory(public_path('uploads/files/'), storage_path('app/public/files/'), true);
	}
	if (File::exists(public_path('uploads/pictures/'))) {
		File::moveDirectory(public_path('uploads/pictures/'), storage_path('app/public/pictures/'), true);
	}
	if (File::exists(public_path('uploads/resumes/'))) {
		File::moveDirectory(public_path('uploads/resumes/'), storage_path('app/public/resumes/'), true);
	}
	File::move(public_path('uploads/index.html'), public_path('app/public/index.html'));
	
	// .ENV
	DotenvEditor::deleteKey('APP_FALLBACK_LOCALE');
	DotenvEditor::deleteKey('APP_LOG_LEVEL');
	DotenvEditor::deleteKey('APP_LOG');
	DotenvEditor::deleteKey('APP_LOG_MAX_FILES');
	DotenvEditor::deleteKey('MULTI_COUNTRY_SEO_LINKS');
	
	DotenvEditor::setKey('LOG_CHANNEL', 'daily');
	DotenvEditor::setKey('LOG_LEVEL', 'debug');
	DotenvEditor::setKey('LOG_DAYS', 2);
	DotenvEditor::save();
	
} catch (\Throwable $e) {
}

// ===| DATABASE |===
try {
	
	// categories
	if (Schema::hasColumn('categories', 'css_class') && !Schema::hasColumn('categories', 'icon_class')) {
		Schema::table('categories', function (Blueprint $table) {
			$table->renameColumn('css_class', 'icon_class');
		});
	}
	if (Schema::hasColumn('categories', 'type')) {
		Schema::table('categories', function (Blueprint $table) {
			$table->enum('type', ['classified', 'job-offer', 'job-search', 'not-salable'])
				->default('classified')
				->comment('Only select this for parent categories')
				->change();
		});
	}
	
	// payment_methods
	if (!Schema::hasColumn('payment_methods', 'is_compatible_api') && Schema::hasColumn('payment_methods', 'has_ccbox')) {
		Schema::table('payment_methods', function (Blueprint $table) {
			$table->boolean('is_compatible_api')->unsigned()->nullable()->default(0)->after('has_ccbox');
		});
	}
	
	// settings
	if (Schema::hasColumn('settings', 'field')) {
		Schema::table('settings', function (Blueprint $table) {
			$table->text('field')->nullable()->change();
		});
	}
	
	DB::table('settings')->where('key', 'app')->update(['field' => null]);
	DB::table('settings')->where('key', 'style')->update(['field' => null]);
	DB::table('settings')->where('key', 'listing')->update(['field' => null]);
	DB::table('settings')->where('key', 'seo')->update(['field' => null]);
	DB::table('settings')->where('key', 'other')->update(['field' => null]);
	DB::table('settings')->where('key', 'cron')->update(['field' => null]);
	
	// users
	if (!Schema::hasColumn('users', 'language_code') && Schema::hasColumn('users', 'country_code')) {
		Schema::table('users', function (Blueprint $table) {
			$table->string('language_code', 10)->nullable()->after('country_code');
		});
	}
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
