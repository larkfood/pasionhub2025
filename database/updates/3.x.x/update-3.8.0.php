<?php

use App\Exceptions\Custom\CustomException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// ===| DATABASE |===
try {
	
	if (Schema::hasColumn('payment_methods', 'description')) {
		Schema::table('payment_methods', function (Blueprint $table) {
			$table->text('description')->nullable()->change();
		});
	}
	
	if (!Schema::hasColumn('posts', 'tags') && Schema::hasColumn('posts', 'description')) {
		Schema::table('posts', function (Blueprint $table) {
			$table->string('tags', 255)->nullable()->after('description');
			$table->index('tags');
		});
	}
	
	DB::table('settings')->where('key', 'activation_guests_can_post')->update(['key' => 'guests_can_post_ads']);
	
	$hint = 'Paste your Google Analytics (or other) tracking code here. This will be added into the footer.';
	$field = '{"name":"value","label":"Value","type":"textarea","hint":"' . $hint . '"}';
	DB::table('settings')->where('key', 'tracking_code')->update(['field' => $field]);
	
	$setting = \App\Models\Setting::where('key', 'guests_can_contact_seller')->first();
	if (empty($setting)) {
		$data = [
			'key'         => 'guests_can_contact_seller',
			'name'        => 'Guests can contact Sellers',
			'value'       => '1',
			'description' => 'Guests can contact Sellers',
			'field'       => '{"name":"value","label":"Activation","type":"checkbox"}',
			'parent_id'   => 0,
			'lft'         => 62,
			'rgt'         => 63,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2017-09-15 08:14:08',
		];
		DB::table('settings')->insert($data);
	}
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
