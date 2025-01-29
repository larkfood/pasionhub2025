<?php

use App\Exceptions\Custom\CustomException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ===| FILES |===
try {
	
	//...
	
} catch (\Exception $e) {
}

// ===| DATABASE |===
try {
	
	// categories
	$tableName = 'categories';
	if (Schema::hasTable($tableName)) {
		// categories.image_path
		if (
			Schema::hasColumn($tableName, 'picture')
			&& !Schema::hasColumn($tableName, 'image_path')
		) {
			Schema::table($tableName, function (Blueprint $table) {
				$table->renameColumn('picture', 'image_path');
			});
		}
		
		if (Schema::hasColumn($tableName, 'image_path')) {
			Schema::table($tableName, function (Blueprint $table) use ($tableName) {
				$table->string('image_path', 255)->nullable()->change();
			});
		}
	}
	
	// pages
	$tableName = 'pages';
	if (Schema::hasTable($tableName)) {
		// pages.image_path
		if (
			Schema::hasColumn($tableName, 'picture')
			&& !Schema::hasColumn($tableName, 'image_path')
		) {
			Schema::table($tableName, function (Blueprint $table) {
				$table->renameColumn('picture', 'image_path');
			});
		}
		
		if (Schema::hasColumn($tableName, 'image_path')) {
			Schema::table($tableName, function (Blueprint $table) use ($tableName) {
				$table->string('image_path', 255)->nullable()->change();
			});
		}
	}
	
	// countries
	$tableName = 'countries';
	if (Schema::hasTable($tableName)) {
		// countries.background_image_path
		if (
			Schema::hasColumn($tableName, 'background_image')
			&& !Schema::hasColumn($tableName, 'background_image_path')
		) {
			Schema::table($tableName, function (Blueprint $table) {
				$table->renameColumn('background_image', 'background_image_path');
			});
		}
		
		if (Schema::hasColumn($tableName, 'background_image_path')) {
			Schema::table($tableName, function (Blueprint $table) use ($tableName) {
				$table->string('background_image_path', 255)->nullable()->change();
			});
		}
	}
	
	// users
	$tableName = 'users';
	if (Schema::hasTable($tableName)) {
		// users.photo_path
		if (
			Schema::hasColumn($tableName, 'photo')
			&& !Schema::hasColumn($tableName, 'photo_path')
		) {
			Schema::table($tableName, function (Blueprint $table) {
				$table->renameColumn('photo', 'photo_path');
			});
		}
		
		if (Schema::hasColumn($tableName, 'photo_path')) {
			Schema::table($tableName, function (Blueprint $table) use ($tableName) {
				$table->string('photo_path', 255)->nullable()->change();
			});
		}
	}
	
	// pictures
	$tableName = 'pictures';
	if (Schema::hasTable($tableName)) {
		// pictures.file_path
		if (
			Schema::hasColumn($tableName, 'filename')
			&& !Schema::hasColumn($tableName, 'file_path')
		) {
			Schema::table($tableName, function (Blueprint $table) {
				$table->renameColumn('filename', 'file_path');
			});
		}
		
		if (Schema::hasColumn($tableName, 'file_path')) {
			Schema::table($tableName, function (Blueprint $table) use ($tableName) {
				$table->string('file_path', 255)->nullable()->change();
			});
		}
	}
	
	// threads_messages
	$tableName = 'threads_messages';
	if (Schema::hasTable($tableName)) {
		// threads_messages.file_path
		if (
			Schema::hasColumn($tableName, 'filename')
			&& !Schema::hasColumn($tableName, 'file_path')
		) {
			Schema::table($tableName, function (Blueprint $table) {
				$table->renameColumn('filename', 'file_path');
			});
		}
		
		if (Schema::hasColumn($tableName, 'file_path')) {
			Schema::table($tableName, function (Blueprint $table) use ($tableName) {
				$table->string('file_path', 255)->nullable()->change();
			});
		}
	}
	
	// posts
	$tableName = 'posts';
	if (Schema::hasTable($tableName)) {
		// posts.visits
		if (Schema::hasColumn($tableName, 'visits')) {
			Schema::table($tableName, function (Blueprint $table) use ($tableName) {
				$table->bigInteger('visits')->unsigned()->nullable()->default('0')->change();
			});
		}
	}
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
