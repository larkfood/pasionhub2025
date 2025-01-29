<?php

use App\Exceptions\Custom\CustomException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

// ===| FILES |===
try {
	
	File::deleteDirectory(app_path('Http/Controllers/Account/'));
	File::deleteDirectory(app_path('Http/Controllers/Ajax/'));
	File::deleteDirectory(app_path('Http/Controllers/Auth/'));
	File::deleteDirectory(app_path('Http/Controllers/Install/'));
	File::deleteDirectory(app_path('Http/Controllers/Locale/'));
	File::deleteDirectory(app_path('Http/Controllers/Post/'));
	File::deleteDirectory(app_path('Http/Controllers/Search/'));
	File::deleteDirectory(app_path('Http/Controllers/Traits/'));
	File::delete(app_path('Http/Controllers/CountriesController.php'));
	File::delete(app_path('Http/Controllers/FileController.php'));
	File::delete(app_path('Http/Controllers/FrontController.php'));
	File::delete(app_path('Http/Controllers/HomeController.php'));
	File::delete(app_path('Http/Controllers/PageController.php'));
	File::delete(app_path('Http/Controllers/SitemapController.php'));
	File::delete(app_path('Http/Controllers/SitemapsController.php'));
	
	File::delete(resource_path('views/post/createOrEdit/multiSteps/packages.blade.php'));
	File::delete(resource_path('views/post/createOrEdit/multiSteps/photos.blade.php'));
	
} catch (\Throwable $e) {
}

// ===| DATABASE |===
try {
	
	// personal_access_tokens
	if (!Schema::hasTable('personal_access_tokens')) {
		// migrations
		$res = DB::table('migrations')->where('migration', 'LIKE', '%personal_access_tokens%')->delete();
		
		// personal_access_tokens
		$path = 'vendor/laravel/sanctum/database/migrations';
		if (!File::exists(base_path($path))) {
			$path = 'database/migrations/29_create_personal_access_tokens_table.php';
		}
		Artisan::call('migrate', ['--path'  => $path, '--force' => true]);
	}
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
