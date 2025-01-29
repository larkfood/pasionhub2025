<?php

use App\Exceptions\Custom\CustomException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

// ===| FILES |===
try {
	
	File::delete(app_path('Console/Commands/AdsClear.php'));
	File::delete(app_path('Http/Controllers/Web/Locale/SetLocaleController.php'));
	File::delete(app_path('Helpers/ArrayHelper.php'));
	
	File::delete(resource_path('views/post/inc/pictures-slider/horizontal-thumb.blade.php'));
	File::delete(resource_path('views/post/inc/pictures-slider/vertical-thumb.blade.php'));
	
} catch (\Throwable $e) {
}

// ===| DATABASE |===
try {
	
	// sessions
	Schema::dropIfExists('sessions');
	if (!Schema::hasTable('sessions')) {
		Schema::create('sessions', function (Blueprint $table) {
			$table->string('id')->primary();
			$table->foreignId('user_id')->nullable()->index();
			$table->string('ip_address', 45)->nullable();
			$table->text('user_agent')->nullable();
			$table->text('payload');
			$table->integer('last_activity')->index();
		});
	}
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
