<?php

use App\Exceptions\Custom\CustomException;
use App\Helpers\DBTool\DBIndex;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

// ===| DATABASE |===
try {
	
	// time_zones
	DBIndex::dropIndexIfExists('time_zones', 'country_code');
	DBIndex::dropIndexIfExists('time_zones', 'time_zone_id');
	
	DBIndex::createIndexIfNotExists('time_zones', 'country_code');
	DBIndex::createIndexIfNotExists('time_zones', 'time_zone_id');
	
	// cities
	$tablePermuted = storage_path('app/public/files/permuted-cities-geo-tables.txt');
	if (!File::exists($tablePermuted)) {
		if (
			Schema::hasColumn('cities', 'longitude')
			&& Schema::hasColumn('cities', 'latitude')
		) {
			Schema::table('cities', function (Blueprint $table) use ($tablePermuted) {
				$table->renameColumn('longitude', 'longitude_tmp');
			});
			Schema::table('cities', function (Blueprint $table) use ($tablePermuted) {
				$table->renameColumn('latitude', 'longitude');
			});
			Schema::table('cities', function (Blueprint $table) use ($tablePermuted) {
				$table->renameColumn('longitude_tmp', 'latitude');
			});
			
			File::put($tablePermuted, '"cities.longitude" & "cities.latitude" permuted');
		}
	}
	
	// ads
	$tablePermuted = storage_path('app/public/files/permuted-ads-geo-tables.txt');
	if (!File::exists($tablePermuted)) {
		if (Schema::hasTable('ads')) {
			if (
				Schema::hasColumn('ads', 'lon')
				&& Schema::hasColumn('ads', 'lat')
			) {
				Schema::table('ads', function (Blueprint $table) use ($tablePermuted) {
					$table->renameColumn('lon', 'lon_tmp');
				});
				Schema::table('ads', function (Blueprint $table) use ($tablePermuted) {
					$table->renameColumn('lat', 'lon');
				});
				Schema::table('ads', function (Blueprint $table) use ($tablePermuted) {
					$table->renameColumn('lon_tmp', 'lat');
				});
				
				File::put($tablePermuted, '"ads.lon" & "ads.lat" permuted');
			}
		}
	}
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}

