<?php

use App\Exceptions\Custom\CustomException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

// ===| FILES |===
try {
	
	$oldLangBaseDir = str(resource_path('lang'))->finish('/')->toString();
	$newLangBaseDir = str(base_path('lang'))->finish('/')->toString();
	$oldLangDirsArray = array_filter(glob($oldLangBaseDir . '*'), 'is_dir');
	if (!empty($oldLangDirsArray)) {
		foreach ($oldLangDirsArray as $oldLangDir) {
			$newLangDir = $newLangBaseDir . basename($oldLangDir);
			if (!File::exists($newLangDir)) {
				File::moveDirectory($oldLangDir, $newLangDir, false);
			}
		}
	}
	
	$oldDir = resource_path('lang/');
	$newDir = resource_path('lang-depreciated/');
	if (File::exists($oldDir)) {
		File::moveDirectory($oldDir, $newDir, true);
	}
	
	File::deleteDirectory(resource_path('docs/'));
	File::delete(app_path('Http/Resources/SavedPostsResource.php'));
	
} catch (\Throwable $e) {
}

// ===| DATABASE |===
try {
	
	// categories
	if (Schema::hasColumn('categories', 'type')) {
		Schema::table('categories', function (Blueprint $table) {
			$table->enum('type', ['classified', 'job-offer', 'job-search', 'rent', 'not-salable'])->default('classified')->change();
		});
	}
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
