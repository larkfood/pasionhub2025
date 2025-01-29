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
	
	// Extras themes directory
	$oldDir = base_path('extras/customizations/views/');
	$newDir = base_path('extras/themes/customized/views/');
	if (File::exists($oldDir)) {
		File::moveDirectory($oldDir, $newDir, true);
	}
	
	$oldDirBase = base_path('extras/customizations/');
	$proofFile = base_path('extras/themes/customized/views/.gitignore');
	if (File::exists($oldDirBase) && File::exists($proofFile)) {
		File::deleteDirectory($oldDirBase);
	}
	
	// Default theme views
	File::delete(resource_path('views/countries.blade.php'));
	File::delete(resource_path('views/index.blade.php'));
	File::delete(resource_path('views/token.blade.php'));
	File::deleteDirectory(resource_path('views/account/'));
	File::deleteDirectory(resource_path('views/auth/'));
	File::deleteDirectory(resource_path('views/common/'));
	File::deleteDirectory(resource_path('views/elements/'));
	File::deleteDirectory(resource_path('views/errors/'));
	File::delete(resource_path('views/layouts/inc/modal/change-country.blade.php'));
	File::delete(resource_path('views/layouts/inc/menu/select-language.blade.php'));
	File::deleteDirectory(resource_path('views/layouts/'));
	File::deleteDirectory(resource_path('views/pages/'));
	File::deleteDirectory(resource_path('views/payment/'));
	File::deleteDirectory(resource_path('views/post/'));
	File::deleteDirectory(resource_path('views/search/'));
	File::deleteDirectory(resource_path('views/sections/'));
	File::deleteDirectory(resource_path('views/sitemap/'));
	
	// Multi steps posting controllers
	File::delete(app_path('Http/Controllers/Web/Public/Post/CreateOrEdit/MultiSteps/CreateController.php'));
	File::delete(app_path('Http/Controllers/Web/Public/Post/CreateOrEdit/MultiSteps/EditController.php'));
	File::delete(app_path('Http/Controllers/Web/Public/Post/CreateOrEdit/MultiSteps/PaymentController.php'));
	File::delete(app_path('Http/Controllers/Web/Public/Post/CreateOrEdit/MultiSteps/PhotoController.php'));
	File::deleteDirectory(app_path('Http/Controllers/Web/Public/Post/CreateOrEdit/MultiSteps/Traits/Create/'));
	
	// Old Front controllers directory
	File::deleteDirectory(app_path('Http/Controllers/Web/Public/'));
	
	// Installer & updater old files and directories
	File::delete(base_path('routes/web/install.php'));
	File::deleteDirectory(app_path('Http/Controllers/Web/Install/'));
	File::deleteDirectory(app_path('Http/Requests/Install/'));
	File::deleteDirectory(resource_path('views/install/'));
	
	// Admin dashboard views
	File::delete(resource_path('views/admin/dashboard/index.blade.php'));
	File::deleteDirectory(resource_path('views/admin/dashboard/inc/'));
	
	// Assets
	File::deleteDirectory(public_path('assets/plugins/form-validation/'));
	File::deleteDirectory(public_path('assets/plugins/hideMaxListItems/'));
	
	// Routes
	File::delete(base_path('routes/web/public.php'));
	
} catch (\Exception $e) {
}

// ===| DATABASE |===
try {
	
	//...
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
