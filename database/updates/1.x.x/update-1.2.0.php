<?php

use App\Exceptions\Custom\CustomException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// ===| DATABASE |===
try {
	
	if (Schema::hasTable('ads')) {
		if (!Schema::hasColumn('ads', 'reviewed')) {
			Schema::table('ads', function ($table) {
				$table->boolean('reviewed')->nullable()->default(0)->index('reviewed')->after('active');
			});
			if (Schema::hasColumn('ads', 'reviewed')) {
				$affected = DB::table('ads')->update(['reviewed' => 1]);
			}
		}
	}
	
	DB::table('settings')->where('key', '=', 'ads_review_activation')->delete();
	DB::table('settings')->where('key', '=', 'facebook_page_fans')->delete();
	
	$setting = \App\Models\Setting::where('key', 'ads_review_activation')->first();
	if (empty($setting)) {
		$data = [
			'key'         => 'ads_review_activation',
			'name'        => 'Ads review activation',
			'value'       => '1',
			'description' => 'Ads review activation',
			'field'       => '{"name":"value","label":"Required","type":"checkbox"}',
			'parent_id'   => 10,
			'lft'         => 23,
			'rgt'         => 24,
			'depth'       => 2,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-14 23:27:49',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'facebook_page_fans')->first();
	if (empty($setting)) {
		$data = [
			'key'         => 'facebook_page_fans',
			'name'        => 'Facebook - Page fans',
			'value'       => '',
			'description' => 'Website Facebook Page number of fans',
			'field'       => '',
			'parent_id'   => 22,
			'lft'         => 41,
			'rgt'         => 42,
			'depth'       => 2,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 01:26:15',
		];
		DB::table('settings')->insert($data);
	}
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
