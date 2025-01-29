<?php

use App\Exceptions\Custom\CustomException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ===| DATABASE |===
try {
	
	// posts
	if (Schema::hasColumn('posts', 'ip_addr')) {
		Schema::table('posts', function (Blueprint $table) {
			$table->string('ip_addr', 50)->nullable()->comment('IP address of creation')->change();
		});
	}
	if (
		Schema::hasColumn('posts', 'ip_addr')
		&& !Schema::hasColumn('posts', 'create_from_ip')
	) {
		Schema::table('posts', function (Blueprint $table) {
			$table->renameColumn('ip_addr', 'create_from_ip');
		});
	}
	if (
		!Schema::hasColumn('posts', 'latest_update_ip')
		&& Schema::hasColumn('posts', 'create_from_ip')
	) {
		Schema::table('posts', function (Blueprint $table) {
			$table->string('latest_update_ip', 50)->nullable()->comment('Latest update IP address')->after('create_from_ip');
		});
	}
	
	// users
	if (Schema::hasColumn('users', 'ip_addr')) {
		Schema::table('users', function (Blueprint $table) {
			$table->string('ip_addr', 50)->nullable()->comment('IP address of creation')->change();
		});
	}
	if (
		Schema::hasColumn('users', 'ip_addr')
		&& !Schema::hasColumn('users', 'create_from_ip')
	) {
		Schema::table('users', function (Blueprint $table) {
			$table->renameColumn('ip_addr', 'create_from_ip');
		});
	}
	if (
		!Schema::hasColumn('users', 'latest_update_ip')
		&& Schema::hasColumn('users', 'create_from_ip')
	) {
		Schema::table('users', function (Blueprint $table) {
			$table->string('latest_update_ip', 50)->nullable()->comment('Latest update IP address')->after('create_from_ip');
		});
	}
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
