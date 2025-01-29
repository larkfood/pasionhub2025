<?php

use App\Exceptions\Custom\CustomException;
use Illuminate\Support\Facades\DB;

// ===| DATABASE |===
try {
	
	// settings
	$setting = \App\Models\Setting::where('key', 'backup')->first();
	if (empty($setting)) {
		$data = [
			'key'         => 'backup',
			'name'        => 'Backup',
			'value'       => null,
			'description' => 'Backup Configuration',
			'field'       => null,
			'parent_id'   => 0,
			'lft'         => 34,
			'rgt'         => 35,
			'depth'       => 1,
			'active'      => 1,
		];
		DB::table('settings')->insert($data);
	}
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
