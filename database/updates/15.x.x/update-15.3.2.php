<?php

use App\Exceptions\Custom\CustomException;
use App\Helpers\DBTool\DBEncoding;
use App\Helpers\DotenvEditor;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

// ===| FILES |===
try {
	
	//...
	
} catch (\Exception $e) {
}

// ===| DATABASE |===
try {
	
	DBEncoding::tryToFixConnectionCharsetAndCollation();
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
