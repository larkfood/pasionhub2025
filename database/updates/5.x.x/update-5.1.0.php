<?php

use App\Exceptions\Custom\CustomException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

// ===| FILES |===
try {
	
	File::delete(config_path('laravel-backup.php'));
	File::delete(base_path('packages/larapen/admin/src/config/laravel-backup.php'));
	
} catch (\Throwable $e) {
}

try {
	
	if (!File::exists(storage_path('framework/plugins'))) {
		File::makeDirectory(storage_path('framework/plugins'));
	}
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}

// ===| DATABASE |===
try {
	
	// home_sections
	if (Schema::hasColumn('home_sections', 'method') && Schema::hasColumn('home_sections', 'id')) {
		Schema::table('home_sections', function (Blueprint $table) {
			$table->string('method', 191)->default('')->after('id')->change();
		});
	}
	if (Schema::hasColumn('home_sections', 'options') && !Schema::hasColumn('home_sections', 'value')) {
		Schema::table('home_sections', function (Blueprint $table) {
			$table->renameColumn('options', 'value');
		});
	}
	if (!Schema::hasColumn('home_sections', 'field') && Schema::hasColumn('home_sections', 'view')) {
		Schema::table('home_sections', function (Blueprint $table) {
			$table->text('field')->nullable()->after('view');
		});
	}
	
	// languages
	if (!Schema::hasColumn('languages', 'parent_id') && Schema::hasColumn('languages', 'default')) {
		Schema::table('languages', function (Blueprint $table) {
			$table->integer('parent_id')->unsigned()->nullable()->after('default');
		});
	}
	if (!Schema::hasColumn('languages', 'lft') && Schema::hasColumn('languages', 'parent_id')) {
		Schema::table('languages', function (Blueprint $table) {
			$table->integer('lft')->unsigned()->nullable()->after('parent_id');
		});
	}
	if (!Schema::hasColumn('languages', 'rgt') && Schema::hasColumn('languages', 'lft')) {
		Schema::table('languages', function (Blueprint $table) {
			$table->integer('rgt')->unsigned()->nullable()->after('lft');
		});
	}
	if (!Schema::hasColumn('languages', 'depth') && Schema::hasColumn('languages', 'rgt')) {
		Schema::table('languages', function (Blueprint $table) {
			$table->integer('depth')->unsigned()->nullable()->after('rgt');
		});
	}
	
	// posts
	if (Schema::hasColumn('posts', 'price')) {
		Schema::table('posts', function (Blueprint $table) {
			$table->decimal('price', 17, 2)->nullable()->change();
		});
	}
	
	// settings
	DB::table('settings')
		->where('key', 'app')
		->update(['value' => DB::raw("REPLACE(`value`, '\"name\":', '\"app_name\":')")]);
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
