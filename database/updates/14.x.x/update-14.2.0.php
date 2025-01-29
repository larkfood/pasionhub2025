<?php

use App\Exceptions\Custom\CustomException;
use App\Helpers\DBTool\DBIndex;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

// ===| FILES |===
try {
	
	File::delete(app_path('Http/Controllers/Api/Base/LocalizationTrait.php'));
	File::delete(app_path('Http/Controllers/Web/Public/Traits/LocalizationTrait.php'));
	
	File::delete(app_path('Models/Setting/GeoLocationSetting.php'));
	File::delete(app_path('Observers/Traits/Setting/GeoLocationTrait.php'));
	File::delete(app_path('Providers/AppService/ConfigTrait/GeolocationConfig.php'));
	if (File::exists(storage_path('framework/plugins/domainmapping'))) {
		File::delete(plugin_path('domainmapping', 'app/Models/Setting/GeoLocationSetting.php'));
	}
	
	File::delete(config_path('currency-symbols.php'));
	File::delete(config_path('languages.php'));
	File::delete(config_path('locales.php'));
	File::delete(config_path('time-zones.php'));
	File::delete(config_path('tlds.php'));
	
	File::deleteDirectory(public_path('images/flags/16/'));
	File::deleteDirectory(public_path('images/flags/24/'));
	File::deleteDirectory(public_path('images/flags/32/'));
	File::deleteDirectory(public_path('images/flags/48/'));
	File::deleteDirectory(public_path('images/flags/64/'));
	
} catch (\Throwable $e) {
}

// ===| DATABASE |===
try {
	
	// languages
	DBIndex::dropIndexIfExists('languages', 'abbr');
	
	if (
		Schema::hasColumn('languages', 'abbr')
		&& !Schema::hasColumn('languages', 'code')
	) {
		Schema::table('languages', function (Blueprint $table) {
			$table->renameColumn('abbr', 'code');
		});
	}
	
	DBIndex::createIndexIfNotExists('languages', 'code');
	
	if (Schema::hasColumn('languages', 'app_name')) {
		Schema::table('languages', function (Blueprint $table) {
			$table->dropColumn('app_name');
		});
	}
	
	// settings
	$setting = \App\Models\Setting::where('key', 'geo_location')->first();
	if (!empty($setting)) {
		$setting->key = 'localization';
		$setting->name = 'Localization';
		$setting->description = 'Localization Configuration';
		$setting->save();
	}
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
