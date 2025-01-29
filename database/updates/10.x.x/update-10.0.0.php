<?php

use App\Exceptions\Custom\CustomException;
use App\Helpers\DBTool\DBIndex;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

// ===| FILES |===
try {
	
	File::delete(resource_path('views/auth/login.blade.php'));
	File::delete(resource_path('views/layouts/inc/modal/login.blade.php'));
	
} catch (\Throwable $e) {
}

// ===| DATABASE |===
try {
	
	// categories
	if (!Schema::hasColumn('categories', 'hide_description')) {
		Schema::table('categories', function (Blueprint $table) {
			if (Schema::hasColumn('categories', 'description')) {
				$table->boolean('hide_description')->nullable()->after('description');
			} else {
				$table->boolean('hide_description')->nullable()->after('icon_class');
			}
		});
	}
	if (!Schema::hasColumn('categories', 'seo_title')) {
		Schema::table('categories', function (Blueprint $table) {
			$table->text('seo_title')->nullable()->after('icon_class');
		});
	}
	if (!Schema::hasColumn('categories', 'seo_description')) {
		Schema::table('categories', function (Blueprint $table) {
			$table->text('seo_description')->nullable()->after('seo_title');
		});
	}
	if (!Schema::hasColumn('categories', 'seo_keywords')) {
		Schema::table('categories', function (Blueprint $table) {
			$table->text('seo_keywords')->nullable()->after('seo_description');
		});
	}
	
	// pages
	if (!Schema::hasColumn('pages', 'seo_title')) {
		Schema::table('pages', function (Blueprint $table) {
			$table->text('seo_title')->nullable()->after('target_blank');
		});
	}
	if (!Schema::hasColumn('pages', 'seo_description')) {
		Schema::table('pages', function (Blueprint $table) {
			$table->text('seo_description')->nullable()->after('seo_title');
		});
	}
	if (!Schema::hasColumn('pages', 'seo_keywords')) {
		Schema::table('pages', function (Blueprint $table) {
			$table->text('seo_keywords')->nullable()->after('seo_description');
		});
	}
	
	// posts
	DBIndex::dropIndexIfExists('posts', 'tags');
	
	if (Schema::hasColumn('posts', 'tags')) {
		Schema::table('posts', function (Blueprint $table) {
			$table->text('tags')->nullable()->change();
		});
	}
	
	// pictures
	if (!Schema::hasColumn('pictures', 'mime_type')) {
		Schema::table('pictures', function (Blueprint $table) {
			$table->string('mime_type', 200)->nullable()->after('filename');
		});
	}
	
	// home_sections
	$tableName = 'home_sections';
	if (Schema::hasTable($tableName)) {
		$homeSection = DB::table($tableName)->where('method', 'getTextArea')->first();
		if (empty($homeSection)) {
			$homeTxtAreaData = [
				'method'    => 'getTextArea',
				'name'      => 'Text Area',
				'value'     => null,
				'view'      => 'home.inc.text-area',
				'field'     => null,
				'parent_id' => null,
				'lft'       => '12',
				'rgt'       => '13',
				'depth'     => '1',
				'active'    => '0',
			];
			DB::table($tableName)->insert($homeTxtAreaData);
		}
	}
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
