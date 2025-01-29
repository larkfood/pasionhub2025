<?php

use App\Exceptions\Custom\CustomException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ===| DATABASE |===
try {
	
	// payment_methods
	if (!Schema::hasColumn('payment_methods', 'parent_id') && Schema::hasColumn('payment_methods', 'depth')) {
		Schema::table('payment_methods', function (Blueprint $table) {
			$table->integer('parent_id')->unsigned()->nullable()->default(0)->after('depth');
		});
	}
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
