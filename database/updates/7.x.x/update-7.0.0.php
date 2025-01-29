<?php

use Illuminate\Support\Facades\File;

// ===| FILES |===
try {
	
	File::deleteDirectory(base_path('packages/torann/'));
	File::delete(app_path('Http/Controllers/InstallController.php'));
	File::delete(app_path('Http/Controllers/UpgradeController.php'));
	
} catch (\Throwable $e) {
}
