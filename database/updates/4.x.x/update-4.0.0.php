<?php

use App\Exceptions\Custom\CustomException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// ===| DATABASE |===
try {
	
	// home_sections
	$tableName = 'home_sections';
	if (Schema::hasTable($tableName)) {
		DB::table($tableName)->where('method', 'getSearchForm')->update(['name' => 'Search Form Area']);
		DB::table($tableName)->where('method', 'getBottomAdvertising')->update(['name' => 'Advertising #2']);
		DB::table($tableName)->where('method', 'getCategories')->update(['options' => '{"max_items":null,"cache_expiration":null}']);
		
		$homeSection = DB::table($tableName)->where('method', 'getTopAdvertising')->first();
		if (empty($homeSection)) {
			$data = [
				'name'      => 'Advertising #1',
				'method'    => 'getTopAdvertising',
				'options'   => null,
				'view'      => 'layouts.inc.advertising.top',
				'parent_id' => 0,
				'lft'       => 11,
				'rgt'       => 12,
				'depth'     => 1,
				'active'    => 1,
			];
			DB::table($tableName)->insert($data);
		}
	}
	
	// pages
	if (!Schema::hasColumn('pages', 'external_link') && Schema::hasColumn('pages', 'content')) {
		Schema::table('pages', function (Blueprint $table) {
			$table->string('external_link', 255)->nullable()->after('content');
		});
	}
	if (!Schema::hasColumn('pages', 'name_color') && Schema::hasColumn('pages', 'depth')) {
		Schema::table('pages', function (Blueprint $table) {
			$table->string('name_color', 10)->nullable()->after('depth');
		});
	}
	if (!Schema::hasColumn('pages', 'title_color') && Schema::hasColumn('pages', 'name_color')) {
		Schema::table('pages', function (Blueprint $table) {
			$table->string('title_color', 10)->nullable()->after('name_color');
		});
	}
	if (!Schema::hasColumn('pages', 'target_blank') && Schema::hasColumn('pages', 'title_color')) {
		Schema::table('pages', function (Blueprint $table) {
			$table->boolean('target_blank')->unsigned()->nullable()->default(0)->after('title_color');
		});
	}
	
	// settings
	$hint = 'By enabling this option you have to add this entry: <strong>DISABLE_EMAIL=false</strong> in the /.env file.';
	$field = '{"name":"value","label":"Required","type":"checkbox","hint":"' . $hint . '"}';
	DB::table('settings')->where('key', 'email_verification')->update(['field' => $field]);
	
	$hint = 'By enabling this option you have to add this entry: <strong>DISABLE_PHONE=false</strong> in the /.env file.';
	$field = '{"name":"value","label":"Required","type":"checkbox","hint":"' . $hint . '"}';
	DB::table('settings')->where('key', 'phone_verification')->update(['field' => $field]);
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
