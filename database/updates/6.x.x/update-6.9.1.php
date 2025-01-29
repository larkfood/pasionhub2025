<?php

use App\Exceptions\Custom\CustomException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

// ===| FILES |===
try {
	
	File::delete(app_path('Helpers/Search.php'));
	
	$migrationFilesPath = str(database_path('upgrade'))->finish(DIRECTORY_SEPARATOR)->toString();
	
	if (File::exists($migrationFilesPath) && File::isDirectory($migrationFilesPath)) {
		$versionsDirsPaths = array_filter(glob($migrationFilesPath . '*'), 'is_dir');
		foreach ($versionsDirsPaths as $versionPath) {
			$version = last(explode(DIRECTORY_SEPARATOR, $versionPath));
			// Remove old versions directories by keeping new semver directories
			if (!preg_match('#^\d+\.\d+\.\d+$#', $version)) {
				File::deleteDirectory($versionPath);
			}
		}
	}
	
} catch (\Throwable $e) {
}

// ===| DATABASE |===
try {
	
	// users
	if (Schema::hasColumn('users', 'provider_id')) {
		Schema::table('users', function (Blueprint $table) {
			$table->string('provider_id', 50)->nullable()->comment('Provider User ID')->change();
		});
	}
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
