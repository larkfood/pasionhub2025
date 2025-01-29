<?php

use App\Exceptions\Custom\CustomException;
use App\Helpers\DotenvEditor;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

// ===| FILES |===
try {
	
	File::deleteDirectory(base_path('documentation/updates/'));
	File::deleteDirectory(storage_path('database/updates/'));
	
	// .ENV
	if (DotenvEditor::keyExists('DB_PREFIX')) {
		$dbTablesPrefix = DotenvEditor::getValue('DB_PREFIX');
		DotenvEditor::setKey('DB_TABLES_PREFIX', $dbTablesPrefix);
		DotenvEditor::deleteKey('DB_PREFIX');
	}
	DotenvEditor::deleteKey('SESSION_DOMAIN');
	DotenvEditor::save();
	
} catch (\Throwable $e) {
}

// ===| DATABASE |===
try {
	
	if (
		!Schema::hasColumn('category_field', 'disabled_in_subcategories')
		&& Schema::hasColumn('category_field', 'field_id')
	) {
		Schema::table('category_field', function (Blueprint $table) {
			$table->boolean('disabled_in_subcategories')->unsigned()->nullable()->default(0)->after('field_id');
		});
	}
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
