<?php

use App\Exceptions\Custom\CustomException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

// ===| FILES |===
try {
	
	$oldFile = public_path('images/maps/uk.svg');
	$newFile = public_path('images/maps/gb.svg');
	if (!File::exists($newFile)) {
		if (File::exists($oldFile)) {
			File::move($oldFile, $newFile);
		}
	}
	$oldDir = storage_path('app/private/resumes/uk/');
	$newDir = storage_path('app/private/resumes/gb/');
	if (File::exists($oldDir)) {
		File::moveDirectory($oldDir, $newDir, true);
	}
	$oldDir = storage_path('app/public/avatars/uk/');
	$newDir = storage_path('app/public/avatars/gb/');
	if (File::exists($oldDir)) {
		File::moveDirectory($oldDir, $newDir, true);
	}
	$oldDir = storage_path('app/public/files/uk/');
	$newDir = storage_path('app/public/files/gb/');
	if (File::exists($oldDir)) {
		File::moveDirectory($oldDir, $newDir, true);
	}
	$oldFile = storage_path('database/geonames/countries/uk.sql');
	$newFile = storage_path('database/geonames/countries/gb.sql');
	if (!File::exists($newFile)) {
		if (File::exists($oldFile)) {
			File::move($oldFile, $newFile);
		}
	}
	if (File::exists($newFile)) {
		$content = File::get($newFile);
		
		$content = str_replace("'UK", "'GB", $content);
		
		File::replace($newFile, $content);
	}
	
} catch (\Throwable $e) {
}

// ===| DATABASE |===
try {
	
	// countries
	DB::table('countries')->where('code', '=', 'UK')->update(['code' => 'GB']);
	
	// subadmin1
	DB::table('subadmin1')
		->where('code', 'LIKE', 'UK.%')
		->update([
			'code'         => DB::raw("REPLACE(`code`, 'UK.', 'GB.')"),
			'country_code' => 'GB',
		]);
	
	// subadmin2
	DB::table('subadmin2')
		->where('code', 'LIKE', 'UK.%')
		->update([
			'code'           => DB::raw("REPLACE(`code`, 'UK.', 'GB.')"),
			'country_code'   => 'GB',
			'subadmin1_code' => DB::raw("REPLACE(`subadmin1_code`, 'UK.', 'GB.')"),
		]);
	
	// cities
	DB::table('cities')
		->where('country_code', '=', 'UK')
		->update([
			'country_code'   => 'GB',
			'subadmin1_code' => DB::raw("REPLACE(`subadmin1_code`, 'UK.', 'GB.')"),
			'subadmin2_code' => DB::raw("REPLACE(`subadmin2_code`, 'UK.', 'GB.')"),
		]);
	
	// posts
	DB::table('posts')->where('country_code', '=', 'UK')->update(['country_code' => 'GB']);
	
	// pictures
	DB::table('pictures')
		->where('filename', 'LIKE', '%/uk/%')
		->update([
			'filename' => DB::raw("REPLACE(`filename`, '/uk/', '/gb/')"),
		]);
	
	// threads_messages
	DB::table('threads_messages')
		->where('filename', 'LIKE', '%/uk/%')
		->update([
			'filename' => DB::raw("REPLACE(`filename`, '/uk/', '/gb/')"),
		]);
	
	// saved_search
	DB::table('saved_search')->where('country_code', '=', 'UK')->update(['country_code' => 'GB']);
	
	// users
	DB::table('users')
		->where('country_code', '=', 'UK')
		->update([
			'country_code' => 'GB',
			'photo'        => DB::raw("REPLACE(`photo`, '/uk/', '/gb/')"),
		]);
	
	// post_values
	DB::table('post_values')
		->where('value', 'LIKE', '%/uk/%')
		->update([
			'value' => DB::raw("REPLACE(`value`, '/uk/', '/gb/')"),
		]);
	
	// domain_home_sections
	if (Schema::hasTable('domain_home_sections')) {
		DB::table('domain_home_sections')->where('country_code', '=', 'UK')->update(['country_code' => 'GB']);
	}
	
	// domain_meta_tags
	if (Schema::hasTable('domain_meta_tags')) {
		DB::table('domain_meta_tags')->where('country_code', '=', 'UK')->update(['country_code' => 'GB']);
	}
	
	// domain_settings
	if (Schema::hasTable('domain_settings')) {
		DB::table('domain_settings')->where('country_code', '=', 'UK')->update(['country_code' => 'GB']);
	}
	
	// domains
	if (Schema::hasTable('domains')) {
		DB::table('domains')->where('country_code', '=', 'UK')->update(['country_code' => 'GB']);
	}
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
