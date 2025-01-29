<?php

use App\Exceptions\Custom\CustomException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

// ===| FILES |===
try {
	
	File::deleteDirectory(app_path('Larapen/'));
	File::delete(base_path('gulpfile.js'));
	// File::delete(base_path('package.json'));
	File::delete(base_path('phpspec.yml'));
	File::delete(base_path('phpunit.xml'));
	
} catch (\Throwable $e) {
}

// ===| DATABASE |===
try {
	
	// pages
	if (Schema::hasColumn('pages', 'type')) {
		Schema::table('pages', function (Blueprint $table) {
			$table->enum('type', ['standard', 'terms', 'privacy', 'tips'])->change();
		});
	}
	if (
		!Schema::hasColumn('pages', 'name')
		&& Schema::hasColumn('pages', 'type')
	) {
		Schema::table('pages', function (Blueprint $table) {
			$table->string('name', 200)->nullable()->after('type');
		});
	}
	if (
		!Schema::hasColumn('pages', 'excluded_from_footer')
		&& Schema::hasColumn('pages', 'title_color')
	) {
		Schema::table('pages', function (Blueprint $table) {
			$table->boolean('excluded_from_footer')->unsigned()->default(0)->after('title_color');
		});
	}
	
	// ads
	if (Schema::hasTable('ads')) {
		if (
			!Schema::hasColumn('ads', 'featured')
			&& Schema::hasColumn('ads', 'reviewed')
		) {
			Schema::table('ads', function (Blueprint $table) {
				$table->boolean('featured')->unsigned()->nullable()->default(0)->after('reviewed');
				$table->index('featured');
			});
		}
	}
	
	// settings
	if (Schema::hasColumn('settings', 'value')) {
		Schema::table('settings', function (Blueprint $table) {
			$table->text('value')->nullable()->default(null)->change();
		});
	}
	
	DB::table('settings')->where('key', 'activation_serp_left_sidebar')->delete();
	DB::table('settings')->where('key', 'like', 'paypal%')->delete();
	
	$setting = \App\Models\Setting::where('key', 'upload_image_types')->first();
	if (empty($setting)) {
		$data = [
			'key'         => 'upload_image_types',
			'name'        => 'Upload Image Types',
			'value'       => 'jpg,jpeg,gif,png',
			'description' => 'Upload image types (ex: jpg,jpeg,gif,png,...)',
			'field'       => '{"name":"value","label":"Value","type":"text"}',
			'parent_id'   => 0,
			'lft'         => 20,
			'rgt'         => 21,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2017-02-21 15:02:43',
		];
		DB::table('settings')->insert($data);
	}
	$setting = \App\Models\Setting::where('key', 'upload_file_types')->first();
	if (empty($setting)) {
		$data = [
			'key'         => 'upload_file_types',
			'name'        => 'Upload File Types',
			'value'       => 'pdf,doc,docx,word,rtf,rtx,ppt,pptx,odt,odp,wps,jpeg,jpg,bmp,png',
			'description' => 'Upload file types (ex: pdf,doc,docx,odt,...)',
			'field'       => '{"name":"value","label":"Value","type":"text"}',
			'parent_id'   => 0,
			'lft'         => 20,
			'rgt'         => 21,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2017-02-21 15:03:06',
		];
		DB::table('settings')->insert($data);
	}
	$setting = \App\Models\Setting::where('key', 'app_favicon')->first();
	if (empty($setting)) {
		$data = [
			'key'         => 'app_favicon',
			'name'        => 'Favicon',
			'value'       => null,
			'description' => 'Favicon (extension: png,jpg)',
			'field'       => '{"name":"value","label":"Favicon","type":"image","upload":"true","disk":"uploads","default":"app/default/ico/favicon.png"}',
			'parent_id'   => 0,
			'lft'         => 4,
			'rgt'         => 4,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2017-02-24 9:15:38',
		];
		DB::table('settings')->insert($data);
	}
	$setting = \App\Models\Setting::where('key', 'unactivated_ads_expiration')->first();
	if (empty($setting)) {
		$data = [
			'key'         => 'unactivated_ads_expiration',
			'name'        => 'Unactivated Ads Expiration',
			'value'       => '30',
			'description' => 'In days (Delete the unactivated ads after this expiration) - You need to add "/usr/bin/php -q /path/to/your/website/artisan ads:clean" in your Cron Job tab',
			'field'       => '{"name":"value","label":"Value","type":"text"}',
			'parent_id'   => 0,
			'lft'         => 25,
			'rgt'         => 25,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2017-03-14 19:31:10',
		];
		DB::table('settings')->insert($data);
	}
	$setting = \App\Models\Setting::where('key', 'activated_ads_expiration')->first();
	if (empty($setting)) {
		$data = [
			'key'         => 'activated_ads_expiration',
			'name'        => 'Activated Ads Expiration',
			'value'       => '150',
			'description' => 'In days (Archive the activated ads after this expiration) - You need to add "/usr/bin/php -q /path/to/your/website/artisan ads:clean" in your Cron Job tab',
			'field'       => '{"name":"value","label":"Value","type":"text"}',
			'parent_id'   => 0,
			'lft'         => 25,
			'rgt'         => 25,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2017-03-14 19:31:10',
		];
		DB::table('settings')->insert($data);
	}
	$setting = \App\Models\Setting::where('key', 'archived_ads_expiration')->first();
	if (empty($setting)) {
		$data = [
			'key'         => 'archived_ads_expiration',
			'name'        => 'Archived Ads Expiration',
			'value'       => '7',
			'description' => 'In days (Delete the archived ads after this expiration) - You need to add "/usr/bin/php -q /path/to/your/website/artisan ads:clean" in your Cron Job tab',
			'field'       => '{"name":"value","label":"Value","type":"text"}',
			'parent_id'   => 0,
			'lft'         => 25,
			'rgt'         => 25,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2017-03-14 19:31:10',
		];
		DB::table('settings')->insert($data);
	}
	$setting = \App\Models\Setting::where('key', 'serp_left_sidebar')->first();
	if (empty($setting)) {
		$data = [
			'key'         => 'serp_left_sidebar',
			'name'        => 'Left Sidebar in Search page',
			'value'       => '0',
			'description' => 'Left Sidebar activation in Search page',
			'field'       => '{"name":"value","label":"Activation","type":"checkbox"}',
			'parent_id'   => 0,
			'lft'         => 62,
			'rgt'         => 63,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2017-03-17 13:51:10',
		];
		DB::table('settings')->insert($data);
	}
	$setting = \App\Models\Setting::where('key', 'serp_display_mode')->first();
	if (empty($setting)) {
		$data = [
			'key'         => 'serp_display_mode',
			'name'        => 'Search page display mode',
			'value'       => '.grid-view',
			'description' => 'Search page display mode (Grid, List, Compact) - You need to clear your cookie data, after you are saved your change',
			'field'       => '{"name":"value","label":"Value","type":"select_from_array","options":{".grid-view":"grid-view",".list-view":"list-view",".compact-view":"compact-view"}}',
			'parent_id'   => 0,
			'lft'         => 62,
			'rgt'         => 63,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2017-03-17 13:51:10',
		];
		DB::table('settings')->insert($data);
	}
	$setting = \App\Models\Setting::where('key', 'app_email_sender')->first();
	if (empty($setting)) {
		$data = [
			'key'         => 'app_email_sender',
			'name'        => 'Email Sender',
			'value'       => null,
			'description' => 'Transactional Email Sender. Example: noreply@yoursite.com',
			'field'       => '{"name":"value","label":"Value","type":"email"}',
			'parent_id'   => 0,
			'lft'         => 9,
			'rgt'         => 10,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2017-03-22 09:27:49',
		];
		DB::table('settings')->insert($data);
	}
	
	DB::table('settings')->where('key', 'app_logo')->update([
		'field' => '{"name":"value","label":"Logo","type":"image","upload":"true","disk":"uploads","default":"app/default/logo.png"}',
	]);
	
	DB::table('settings')->where('key', 'custom_css')->update([
		'field' => '{"name":"value","label":"Value","type":"textarea","hint":"Please <strong>do not</strong> include the &lt;style&gt; tags."}',
	]);
	
	DB::table('settings')->where('key', 'seo_google_analytics')->update([
		'key'         => 'tracking_code',
		'name'        => 'Tracking Code',
		'description' => 'Tracking Code (ex: Google Analytics Code)',
		'field'       => '{"name":"value","label":"Value","type":"textarea","hint":"Paste your Google Analytics (or other) tracking code here. This will be added into the footer. <br>Please <strong>do not</strong> include the &lt;script&gt; tags."}',
	]);
	
	// UPDATE `settings` s, (SELECT `key`, `value` FROM `settings` WHERE `key`='app_email') ss SET s.value=ss.value WHERE s.key='app_email_sender';
	$prefix = DB::getTablePrefix();
	$rawTable = $prefix . 'settings';
	DB::table('settings as s')
		->join(DB::raw('(SELECT `key`, `value` FROM `' . $rawTable . '` WHERE `key` = "app_email") as ss'), 's.key', '=', DB::raw('ss.key'))
		->where('s.key', 'app_email_sender')
		->update(['s.value' => DB::raw('ss.value')]);
	
	DB::table('settings')->update(['parent_id' => 0]);
	
	// ad_type
	if (Schema::hasTable('ad_type')) {
		if (!Schema::hasColumn('ad_type', 'lft') && Schema::hasColumn('ad_type', 'name')) {
			Schema::table('ad_type', function (Blueprint $table) {
				$table->integer('lft')->unsigned()->nullable()->after('name');
			});
		}
		if (!Schema::hasColumn('ad_type', 'rgt') && Schema::hasColumn('ad_type', 'lft')) {
			Schema::table('ad_type', function (Blueprint $table) {
				$table->integer('rgt')->unsigned()->nullable()->after('lft');
			});
		}
		if (!Schema::hasColumn('ad_type', 'depth') && Schema::hasColumn('ad_type', 'rgt')) {
			Schema::table('ad_type', function (Blueprint $table) {
				$table->integer('depth')->unsigned()->nullable()->after('rgt');
			});
		}
	}
	
	// payment_methods
	Schema::dropIfExists('payment_methods');
	
	if (!Schema::hasTable('payment_methods')) {
		Schema::create('payment_methods', function (Blueprint $table) {
			$table->increments('id')->unsigned();
			$table->string('name', 100)->nullable()->collation('utf8_unicode_ci');
			$table->string('display_name', 100)->nullable()->collation('utf8_unicode_ci');
			$table->string('description', 255)->default('')->collation('utf8_unicode_ci');
			$table->boolean('has_ccbox')->unsigned()->default(0);
			$table->integer('lft')->unsigned()->nullable();
			$table->integer('rgt')->unsigned()->nullable();
			$table->integer('depth')->unsigned()->nullable();
			$table->boolean('active')->unsigned()->default(0);
		});
	}
	
	if (Schema::hasTable('payment_methods')) {
		$data = [
			'id'           => 1,
			'name'         => 'paypal',
			'display_name' => 'Paypal',
			'description'  => 'Payment with Paypal',
			'has_ccbox'    => 0,
			'lft'          => 0,
			'rgt'          => 0,
			'depth'        => 1,
			'active'       => 1,
		];
		DB::table('payment_methods')->insert($data);
	}
	
	// ads
	if (Schema::hasTable('ads') && Schema::hasTable('payments')) {
		DB::table('ads as a')->join('payments as p', 'p.ad_id', '=', 'a.id')->update(['a.featured' => 1]);
	}
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
