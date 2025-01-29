<?php

use App\Exceptions\Custom\CustomException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// ===| DATABASE |===
try {
	
	// home_sections
	$tableName = 'home_sections';
	if (Schema::hasTable($tableName)) {
		$homeSection = DB::table($tableName)->where('method', 'getSearchForm')->first();
		if (empty($homeSection)) {
			$data = [
				'name'      => 'Search Form (Always in Top)',
				'method'    => 'getSearchForm',
				'options'   => '{"enable_extended_form_area":"1","background_color":null,"background_image":null,"form_border_color":null,"form_border_size":null,"form_btn_background_color":null,"form_btn_text_color":null,"hide_titles":"0","big_title_color":null,"sub_title_color":null}',
				'view'      => 'home.inc.search',
				'parent_id' => 0,
				'lft'       => 0,
				'rgt'       => 0,
				'depth'     => 1,
				'active'    => 1,
			];
			DB::table($tableName)->insert($data);
		}
	}
	
	// settings
	$allData = [
		[
			'key'         => 'tiktok_url',
			'name'        => 'Tiktok URL',
			'value'       => '#',
			'description' => 'Website Tiktok URL',
			'field'       => '{"name":"value","label":"Value","type":"text"}',
			'parent_id'   => 0,
			'lft'         => 51,
			'rgt'         => 52,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2017-10-10 14:10:16',
		],
		[
			'key'         => 'linkedin_url',
			'name'        => 'LinkedIn URL',
			'value'       => '#',
			'description' => 'Website LinkedIn URL',
			'field'       => '{"name":"value","label":"Value","type":"text"}',
			'parent_id'   => 0,
			'lft'         => 52,
			'rgt'         => 53,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2017-10-10 14:10:16',
		],
		[
			'key'         => 'pinterest_url',
			'name'        => 'Pinterest URL',
			'value'       => '#',
			'description' => 'Website Pinterest URL',
			'field'       => '{"name":"value","label":"Value","type":"text"}',
			'parent_id'   => 0,
			'lft'         => 53,
			'rgt'         => 54,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2017-10-10 14:10:16',
		],
	];
	foreach ($allData as $item) {
		$key = $item['key'] ?? '';
		
		$setting = \App\Models\Setting::where('key', $key)->first();
		if (empty($setting)) {
			DB::table('settings')->insert($item);
		}
	}
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
