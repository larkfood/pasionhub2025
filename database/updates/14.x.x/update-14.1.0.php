<?php

use Illuminate\Support\Facades\File;

// ===| FILES |===
try {
	
	File::delete(app_path('Http/Controllers/Api/Base/ApiResponseTrait.php'));
	File::delete(app_path('Http/Controllers/Api/Base/StaticApiResponseTrait.php'));
	
} catch (\Throwable $e) {
}
