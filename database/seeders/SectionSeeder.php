<?php

namespace Database\Seeders;

use App\Models\Section;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SectionSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$timezone = config('app.timezone', 'UTC');
		
		$entries = [
			[
				'belongs_to'  => 'home',
				'key'         => 'search_form',
				'name'        => 'Search Form Area',
				'field'       => null,
				'value'       => null,
				'description' => 'Search Form Area Section',
				'parent_id'   => null,
				'lft'         => '0',
				'rgt'         => '1',
				'depth'       => '1',
				'active'      => '1',
				'created_at'  => now($timezone)->format('Y-m-d H:i:s'),
				'updated_at'  => null,
			],
			[
				'belongs_to'  => 'home',
				'key'         => 'categories',
				'name'        => 'Categories',
				'field'       => null,
				'value'       => null,
				'description' => 'Categories Section',
				'parent_id'   => null,
				'lft'         => '2',
				'rgt'         => '3',
				'depth'       => '1',
				'active'      => '1',
				'created_at'  => now($timezone)->format('Y-m-d H:i:s'),
				'updated_at'  => null,
			],
			[
				'belongs_to'  => 'home',
				'key'         => 'premium_listings',
				'name'        => 'Premium Listings',
				'field'       => null,
				'value'       => null,
				'description' => 'Premium Listings Section',
				'parent_id'   => null,
				'lft'         => '4',
				'rgt'         => '5',
				'depth'       => '1',
				'active'      => '1',
				'created_at'  => now($timezone)->format('Y-m-d H:i:s'),
				'updated_at'  => null,
			],
			[
				'belongs_to'  => 'home',
				'key'         => 'locations',
				'name'        => 'Locations & SVG Map',
				'field'       => null,
				'value'       => null,
				'description' => 'Locations & Country\'s SVG Map Section',
				'parent_id'   => null,
				'lft'         => '6',
				'rgt'         => '7',
				'depth'       => '1',
				'active'      => '1',
				'created_at'  => now($timezone)->format('Y-m-d H:i:s'),
				'updated_at'  => null,
			],
			[
				'belongs_to'  => 'home',
				'key'         => 'latest_listings',
				'name'        => 'Latest Listings',
				'field'       => null,
				'value'       => null,
				'description' => 'Latest Listings Section',
				'parent_id'   => null,
				'lft'         => '8',
				'rgt'         => '9',
				'depth'       => '1',
				'active'      => '1',
				'created_at'  => now($timezone)->format('Y-m-d H:i:s'),
				'updated_at'  => null,
			],
			[
				'belongs_to'  => 'home',
				'key'         => 'stats',
				'name'        => 'Mini Stats',
				'field'       => null,
				'value'       => null,
				'description' => 'Mini Stats Section',
				'parent_id'   => null,
				'lft'         => '10',
				'rgt'         => '11',
				'depth'       => '1',
				'active'      => '1',
				'created_at'  => now($timezone)->format('Y-m-d H:i:s'),
				'updated_at'  => null,
			],
			[
				'belongs_to'  => 'home',
				'key'         => 'text_area',
				'name'        => 'Text Area',
				'field'       => null,
				'value'       => null,
				'description' => 'Text Area Section',
				'parent_id'   => null,
				'lft'         => '12',
				'rgt'         => '13',
				'depth'       => '1',
				'active'      => '0',
				'created_at'  => now($timezone)->format('Y-m-d H:i:s'),
				'updated_at'  => null,
			],
			[
				'belongs_to'  => 'home',
				'key'         => 'top_ad',
				'name'        => 'Advertising #1',
				'field'       => null,
				'value'       => null,
				'description' => 'Advertising #1 Section',
				'parent_id'   => null,
				'lft'         => '14',
				'rgt'         => '15',
				'depth'       => '1',
				'active'      => '0',
				'created_at'  => now($timezone)->format('Y-m-d H:i:s'),
				'updated_at'  => null,
			],
			[
				'belongs_to'  => 'home',
				'key'         => 'bottom_ad',
				'name'        => 'Advertising #2',
				'field'       => null,
				'value'       => null,
				'description' => 'Advertising #2 Section',
				'parent_id'   => null,
				'lft'         => '16',
				'rgt'         => '17',
				'depth'       => '1',
				'active'      => '0',
				'created_at'  => now($timezone)->format('Y-m-d H:i:s'),
				'updated_at'  => null,
			],
		];
		
		$tableName = (new Section())->getTable();
		foreach ($entries as $entry) {
			DB::table($tableName)->insert($entry);
		}
	}
}
