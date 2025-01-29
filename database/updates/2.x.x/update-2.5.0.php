<?php

use App\Exceptions\Custom\CustomException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ===| DATABASE |===
try {
	
	// categories
	Schema::table('categories', function (Blueprint $table) {
		$table->enum('type', ['classified', 'job-offer', 'job-search', 'service', 'no-condition', 'non-salable'])
			->default('classified')
			->comment('Only select this for parent categories')
			->change();
	});
	
	// cities
	if (Schema::hasColumn('cities', 'id')) {
		Schema::table('cities', function (Blueprint $table) {
			$table->integer('id')->unsigned()->autoIncrement()->change();
		});
	}
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
