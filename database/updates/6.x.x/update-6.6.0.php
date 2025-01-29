<?php

use App\Exceptions\Custom\CustomException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

// ===| FILES |===
try {
	
	File::delete(app_path('Http/Middleware/TransformInput.php'));
	File::delete(app_path('Http/Requests/RegisterRequest.php'));
	
} catch (\Throwable $e) {
}

// ===| DATABASE |===
try {
	
	// users
	if (!Schema::hasColumn('users', 'email_verified_at')) {
		Schema::table('users', function (Blueprint $table) {
			$table->timestamp('email_verified_at')->nullable()->after('email');
		});
	}
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
