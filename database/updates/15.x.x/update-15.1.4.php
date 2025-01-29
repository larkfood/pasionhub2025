<?php

use App\Exceptions\Custom\CustomException;
use Illuminate\Support\Facades\File;

// ===| FILES |===
try {
	
	File::delete(app_path('Observers/Traits/Setting/MailTrait.php'));
	File::delete(app_path('Observers/Traits/Setting/SecurityTrait.php'));
	
} catch (\Exception $e) {
}

// ===| DATABASE |===
try {
	
	//...
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
