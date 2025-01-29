<?php

use Illuminate\Support\Facades\File;

// ===| FILES |===
try {
	
	File::deleteDirectory(base_path('packages/larapen/admin/'));
	File::deleteDirectory(public_path('vendor/admin/'));
	File::deleteDirectory(public_path('vendor/admin-theme/'));
	File::deleteDirectory(public_path('vendor/icon-fonts/'));
	File::deleteDirectory(resource_path('views/vendor/admin/'));
	
} catch (\Throwable $e) {
}
