<?php

use App\Exceptions\Custom\CustomException;
use Illuminate\Support\Facades\DB;

// ===| DATABASE |===
try {
	
	// settings
	DB::table('settings')->where('key', '=', 'app_slogan')->update([
		'field' => '{"name":"value","label":"Value","type":"text"}',
	]);
	
	DB::table('settings')->where('key', '=', 'meta_description')->delete();
	
	$setting = \App\Models\Setting::where('key', 'meta_description')->first();
	if (empty($setting)) {
		$data = [
			'key'         => 'meta_description',
			'name'        => 'Meta description',
			'value'       => '',
			'description' => 'Your website meta description',
			'field'       => '{"name":"value","label":"Value","type":"textarea"}',
			'parent_id'   => 1,
			'lft'         => 5,
			'rgt'         => 6,
			'depth'       => 2,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-11-16 19:06:12',
		];
		DB::table('settings')->insert($data);
	}
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
