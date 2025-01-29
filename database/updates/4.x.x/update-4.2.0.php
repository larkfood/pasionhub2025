<?php

use App\Exceptions\Custom\CustomException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// ===| DATABASE |===
try {
	
	// countries
	if (!Schema::hasColumn('countries', 'background_image') && Schema::hasColumn('countries', 'equivalent_fips_code')) {
		Schema::table('countries', function (Blueprint $table) {
			$table->string('background_image', 255)->nullable()->after('equivalent_fips_code');
		});
	}
	
	// languages
	if (!Schema::hasColumn('languages', 'direction') && Schema::hasColumn('languages', 'script')) {
		Schema::table('languages', function (Blueprint $table) {
			$table->enum('direction', ['ltr', 'rtl'])->default('ltr')->after('script');
		});
	}
	
	// pictures
	if (!Schema::hasColumn('pictures', 'position') && Schema::hasColumn('pictures', 'filename')) {
		Schema::table('pictures', function (Blueprint $table) {
			$table->integer('position')->unsigned()->default(0)->after('filename');
		});
	}
	
	// settings
	DB::table('settings')->where('key', 'tiktok_url')->delete();
	DB::table('settings')->where('key', 'linkedin_url')->delete();
	DB::table('settings')->where('key', 'pinterest_url')->delete();
	
	$allData = [
		[
			'key'         => 'tiktok_url',
			'name'        => 'Tiktok URL',
			'value'       => '#',
			'description' => 'Website Tiktok URL',
			'field'       => '{"name":"value","label":"Value","type":"text"}',
			'parent_id'   => 0,
			'lft'         => 51,
			'rgt'         => 52,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2017-11-14 11:35:16',
		],
		[
			'key'         => 'linkedin_url',
			'name'        => 'LinkedIn URL',
			'value'       => '#',
			'description' => 'Website LinkedIn URL',
			'field'       => '{"name":"value","label":"Value","type":"text"}',
			'parent_id'   => 0,
			'lft'         => 52,
			'rgt'         => 53,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2017-11-14 11:35:16',
		],
		[
			'key'         => 'pinterest_url',
			'name'        => 'Pinterest URL',
			'value'       => '#',
			'description' => 'Website Pinterest URL',
			'field'       => '{"name":"value","label":"Value","type":"text"}',
			'parent_id'   => 0,
			'lft'         => 53,
			'rgt'         => 54,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2017-11-14 11:35:16',
		],
	];
	foreach ($allData as $item) {
		$key = $item['key'] ?? '';
		
		$setting = \App\Models\Setting::where('key', $key)->first();
		if (empty($setting)) {
			DB::table('settings')->insert($item);
		}
	}
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
