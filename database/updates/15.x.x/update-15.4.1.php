<?php

use App\Exceptions\Custom\CustomException;
use App\Helpers\DBTool\DBEncoding;
use App\Helpers\DotenvEditor;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

// ===| FILES |===
try {
	
	// Events & Listeners
	File::delete(app_path('Providers/EventServiceProvider.php'));
	File::delete(app_path('Listeners/UpdateThePostCounter.php'));
	File::delete(app_path('Listeners/UpdateUserLastLogin.php'));
	
	// User Account
	File::delete(app_path('Http/Controllers/Web/Front/Account/EditController.php'));
	File::delete(resource_path('views/front/account/edit.blade.php'));
	
	// Assets
	$sourceFile = public_path('dist/public/custom.css');
	$targetFile = public_path('dist/front/custom.css');
	if (File::exists($sourceFile)) {
		if (File::exists($targetFile)) {
			File::delete($targetFile);
		}
		File::move($sourceFile, $targetFile);
	}
	File::deleteDirectory(public_path('dist/public/'));
	
	// Errors views
	File::delete(resource_path('views/front/errors/503.blade.php'));
	
} catch (\Exception $e) {
}

// ===| DATABASE |===
try {
	
	//...
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
