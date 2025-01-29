<?php

use Illuminate\Support\Facades\File;

// ===| FILES |===
try {
	
	File::delete(app_path('Http/Middleware/CheckBrowserLanguage.php'));
	File::delete(app_path('Http/Middleware/CheckCountryLanguage.php'));
	File::delete(app_path('Http/Middleware/SetAppLocale.php'));
	
} catch (\Throwable $e) {
}
