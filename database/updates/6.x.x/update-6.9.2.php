<?php

use App\Exceptions\Custom\CustomException;
use Illuminate\Support\Facades\DB;

// ===| DATABASE |===
try {
	
	// Drop MySQL custom geo functions & Remove its cache
	$functions = ['haversine', 'orthodromy'];
	foreach ($functions as $function) {
		// DB function
		DB::statement('DROP FUNCTION IF EXISTS ' . $function . ';');
		
		// Function's cache
		$cacheId = 'checkIfMySQLFunctionExists.' . $function;
		if (cache()->has($cacheId)) {
			cache()->forget($cacheId);
		}
	}
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
