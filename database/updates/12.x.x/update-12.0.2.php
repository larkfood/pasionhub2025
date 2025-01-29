<?php

use App\Exceptions\Custom\CustomException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

// ===| FILES |===
try {
	
	$sourceFile = public_path('images/user.png');
	$targetFile = storage_path('app/public/app/default/user.png');
	if (File::exists($sourceFile)) {
		if (File::exists($targetFile)) {
			File::delete($sourceFile);
		} else {
			File::move($sourceFile, $targetFile);
		}
	}
	
} catch (\Throwable $e) {
}

// ===| DATABASE |===
try {
	
	// blacklist
	if (Schema::hasColumn('blacklist', 'type')) {
		Schema::table('blacklist', function (Blueprint $table) {
			$table->enum('type', ['domain', 'email', 'phone', 'ip', 'word'])
				->nullable()
				->default(null)
				->change();
		});
	}
	
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
