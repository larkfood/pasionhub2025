<?php

use Illuminate\Support\Facades\File;

// ===| FILES |===
try {
	
	File::delete(app_path('Http/Controllers/Web/Search/Traits/CategoryTrait.php'));
	File::delete(app_path('Http/Controllers/Web/Search/Traits/LocationTrait.php'));
	File::delete(app_path('Http/Middleware/InputRequest/ApiCalls.php'));
	File::delete(resource_path('views/account/saved-search.blade.php'));
	
} catch (\Throwable $e) {
}
