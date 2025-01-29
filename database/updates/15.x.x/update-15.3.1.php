<?php

use App\Exceptions\Custom\CustomException;
use App\Helpers\DBTool\DBEncoding;
use Illuminate\Support\Facades\File;

// ===| FILES |===
try {
	
	File::delete(app_path('Helpers/DBTool/IndexTrait.php'));
	File::delete(app_path('Helpers/DBTool/RawIndexTrait.php'));
	
} catch (\Exception $e) {
}

// ===| DATABASE |===
try {
	
	DBEncoding::tryToFixConnectionCharsetAndCollation();
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
