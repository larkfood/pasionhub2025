<?php

use App\Exceptions\Custom\CustomException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

// ===| FILES |===
try {
	
	File::deleteDirectory(app_path('Helpers/Validator/'));
	File::delete(app_path('Providers/ValidatorServiceProvider.php'));
	
} catch (\Throwable $e) {
}

// ===| DATABASE |===
try {
	
	// posts
	if (!Schema::hasColumn('posts', 'archived_manually')) {
		Schema::table('posts', function (Blueprint $table) {
			$table->boolean('archived_manually')->unsigned()->nullable()->default(0)->after('archived_at');
		});
	}
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
