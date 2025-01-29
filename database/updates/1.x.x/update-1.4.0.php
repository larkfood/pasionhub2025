<?php

use App\Exceptions\Custom\CustomException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// ===| DATABASE |===
try {
	
	// payment_methods
	DB::table('payment_methods')->truncate();
	$data = [
		'id'          => 1,
		'name'        => 'Paypal',
		'description' => 'Paypal Express',
		'lft'         => null,
		'rgt'         => null,
		'depth'       => null,
		'active'      => 1,
	];
	if (Schema::hasColumn('payment_methods', 'country_code')) {
		$data['country_code'] = null;
	}
	DB::table('payment_methods')->insert($data);
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
