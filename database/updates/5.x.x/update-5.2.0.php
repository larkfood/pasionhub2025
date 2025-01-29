<?php

use App\Exceptions\Custom\CustomException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

// ===| FILES |===
try {
	
	File::delete(app_path('Http/Controllers/Admin/AjaxController.php'));
	
	File::deleteDirectory(base_path('packages/larapen/admin/src/app/Http/Controllers/Auth/'));
	File::deleteDirectory(base_path('packages/larapen/admin/src/app/Http/Middleware/'));
	File::deleteDirectory(base_path('packages/larapen/admin/src/app/Http/Requests/'));
	File::deleteDirectory(base_path('packages/larapen/admin/src/database/'));
	
	File::delete(base_path('packages/larapen/admin/src/app/Http/Controllers/BackupController.php'));
	File::delete(base_path('packages/larapen/admin/src/app/Http/Controllers/DashboardController.php'));
	File::delete(base_path('packages/larapen/admin/src/app/Http/Controllers/LanguageController.php'));
	File::delete(base_path('packages/larapen/admin/src/app/Http/Controllers/SettingController.php'));
	File::delete(base_path('packages/larapen/admin/src/config/backup.php'));
	File::delete(base_path('packages/larapen/admin/src/RouteCrud.php'));
	
} catch (\Throwable $e) {
}

// ===| DATABASE |===
try {
	
	// user_types
	DB::table('user_types')->truncate();
	DB::table('user_types')->insert([
		['id' => 1, 'name' => 'Professional', 'active' => 1],
		['id' => 2, 'name' => 'Individual', 'active' => 1],
	]);
	
	// users
	DB::table('users')->where('user_type_id', 1)->update(['user_type_id' => null]);
	DB::table('users')->where('user_type_id', 2)->update(['user_type_id' => 1]);
	DB::table('users')->where('user_type_id', 3)->update(['user_type_id' => 2]);
	
	// permissions
	if (!Schema::hasTable('permissions')) {
		Schema::create('permissions', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('name')->nullable();
			$table->string('guard_name')->nullable();
			$table->timestamp('updated_at')->nullable();
			$table->timestamp('created_at')->nullable();
		});
	}
	
	// roles
	if (!Schema::hasTable('roles')) {
		Schema::create('roles', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('name')->nullable();
			$table->string('guard_name')->nullable();
			$table->timestamp('updated_at')->nullable();
			$table->timestamp('created_at')->nullable();
		});
	}
	
	// model_has_permissions
	if (!Schema::hasTable('model_has_permissions')) {
		Schema::create('model_has_permissions', function (Blueprint $table) {
			$table->bigInteger('permission_id')->unsigned();
			$table->bigInteger('model_id')->unsigned();
			$table->string('model_type');
			
			$table->primary(['permission_id', 'model_id', 'model_type']);
			
			$table->foreign('permission_id')
				->references('id')->on('permissions')
				->onDelete('cascade');
		});
	}
	
	// model_has_roles
	if (!Schema::hasTable('model_has_roles')) {
		Schema::create('model_has_roles', function (Blueprint $table) {
			$table->bigInteger('role_id')->unsigned();
			$table->bigInteger('model_id')->unsigned();
			$table->string('model_type');
			
			$table->primary(['role_id', 'model_id', 'model_type']);
			
			$table->foreign('role_id')
				->references('id')->on('roles')
				->onDelete('cascade');
		});
	}
	
	// role_has_permissions
	if (!Schema::hasTable('role_has_permissions')) {
		Schema::create('role_has_permissions', function (Blueprint $table) {
			$table->bigInteger('permission_id')->unsigned();
			$table->bigInteger('role_id')->unsigned();
			
			$table->primary(['permission_id', 'role_id']);
			$table->index('role_id');
			
			$table->foreign('permission_id')
				->references('id')->on('permissions')
				->onDelete('cascade');
			
			$table->foreign('role_id')
				->references('id')->on('roles')
				->onDelete('cascade');
		});
	}
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
