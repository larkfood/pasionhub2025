<?php

use App\Exceptions\Custom\CustomException;
use Illuminate\Support\Facades\DB;

// ===| DATABASE |===
try {
	
	// settings
	DB::table('settings')->where('key', 'purchase_code')->delete();
	
	$setting = \App\Models\Setting::where('key', 'purchase_code')->first();
	if (empty($setting)) {
		$data = [
			'key'         => 'purchase_code',
			'name'        => 'Purchase Code',
			'value'       => '',
			'description' => 'LaraClassifier Purchase Code',
			'field'       => '{"name":"value","label":"Value","type":"text"}',
			'parent_id'   => 1,
			'lft'         => 6,
			'rgt'         => 7,
			'depth'       => 2,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-11-10 19:29:35',
		];
		DB::table('settings')->insert($data);
	}
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
