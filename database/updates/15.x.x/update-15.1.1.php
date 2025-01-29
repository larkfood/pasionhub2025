<?php

use App\Exceptions\Custom\CustomException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

// ===| FILES |===
try {
	
	$icoDir = public_path('assets/ico/');
	if (File::exists($icoDir) && File::isDirectory($icoDir)) {
		File::deleteDirectory($icoDir);
	}
	
	File::delete(app_path('Providers/DropboxServiceProvider.php'));
	File::delete(app_path('Providers/MacrosServiceProvider.php'));
	File::delete(app_path('Providers/PluginsServiceProvider.php'));
	
	File::delete(public_path('assets/js/adsdetails.js'));
	File::delete(public_path('assets/js/footable.all.min.js'));
	File::delete(public_path('assets/js/footable.bookmarkable.js'));
	File::delete(public_path('assets/js/footable.filter.js'));
	File::delete(public_path('assets/js/footable.js'));
	File::delete(public_path('assets/js/footable.paginate.js'));
	File::delete(public_path('assets/js/footable.plugin.template.js'));
	File::delete(public_path('assets/js/footable.sort.js'));
	File::delete(public_path('assets/js/footable.sortable.js'));
	File::delete(public_path('assets/js/footable.striping.js'));
	File::delete(public_path('assets/js/form-validation.js'));
	File::delete(public_path('assets/js/grids.js'));
	File::delete(public_path('assets/js/hideMaxListItem-min.js'));
	File::delete(public_path('assets/js/hideMaxListItem.js'));
	File::delete(public_path('assets/js/jquery.easing.1.3.js'));
	File::delete(public_path('assets/js/jquery.matchHeight-min.js'));
	File::delete(public_path('assets/js/jquery.parallax-1.1.js'));
	File::delete(public_path('assets/js/jquery.scrollto.js'));
	File::delete(public_path('assets/js/popper.min.js'));
	File::delete(public_path('assets/js/vendors.js'));
	File::delete(public_path('assets/js/vendors.min.js'));
	
	File::deleteDirectory(public_path('assets/plugins/forms/'));
	
} catch (\Exception $e) {
}

// ===| DATABASE |===
try {
	
	// Drop backed enums tables if exists
	Schema::dropIfExists('continents');
	Schema::dropIfExists('gender');
	Schema::dropIfExists('post_types');
	Schema::dropIfExists('user_types');
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
