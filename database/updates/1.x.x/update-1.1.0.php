<?php

use App\Exceptions\Custom\CustomException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// ===| DATABASE |===
try {
	
	// ads
	if (Schema::hasTable('ads')) {
		if (!Schema::hasColumn('ads', 'address') && Schema::hasColumn('ads', 'seller_phone_hidden')) {
			Schema::table('ads', function (Blueprint $table) {
				$table->string('address', 255)->nullable()->after('seller_phone_hidden');
				$table->index('address');
			});
		}
	}
	
	// advertising
	DB::table('advertising')->truncate();
	
	// settings
	DB::table('settings')->truncate();
	
	$setting = \App\Models\Setting::where('key', 'app_name')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 1,
			'key'         => 'app_name',
			'name'        => 'App Name',
			'value'       => 'SiteName',
			'description' => 'Website name',
			'field'       => '',
			'parent_id'   => 0,
			'lft'         => 2,
			'rgt'         => 13,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:33:22',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'app_logo')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 2,
			'key'         => 'app_logo',
			'name'        => 'Logo',
			'value'       => '',
			'description' => 'Website Logo',
			'field'       => '{"name":"value","label":"Value","type":"browse"}',
			'parent_id'   => 1,
			'lft'         => 3,
			'rgt'         => 4,
			'depth'       => 2,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-14 22:27:49',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'app_slogan')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 3,
			'key'         => 'app_slogan',
			'name'        => 'App Slogan',
			'value'       => 'SiteName - Hello, World!',
			'description' => 'Website slogan (for Meta Title)',
			'field'       => '',
			'parent_id'   => 1,
			'lft'         => 5,
			'rgt'         => 6,
			'depth'       => 2,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-14 22:27:49',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'app_theme')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 4,
			'key'         => 'app_theme',
			'name'        => 'Theme',
			'value'       => '',
			'description' => 'Supported: blue, yellow, green, red (or empty)',
			'field'       => '',
			'parent_id'   => 1,
			'lft'         => 7,
			'rgt'         => 8,
			'depth'       => 2,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-14 22:27:49',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'app_email')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 5,
			'key'         => 'app_email',
			'name'        => 'Email',
			'value'       => 'contact@larapen.com',
			'description' => 'The email address that all emails from the contact form will go to.',
			'field'       => '{"name":"value","label":"Value","type":"email"}',
			'parent_id'   => 1,
			'lft'         => 9,
			'rgt'         => 10,
			'depth'       => 2,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-14 22:27:49',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'app_phone_number')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 6,
			'key'         => 'app_phone_number',
			'name'        => 'Phone number',
			'value'       => null,
			'description' => 'Website phone number',
			'field'       => '',
			'parent_id'   => 1,
			'lft'         => 11,
			'rgt'         => 12,
			'depth'       => 2,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-14 22:27:49',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'activation_geolocation')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 7,
			'key'         => 'activation_geolocation',
			'name'        => 'Geolocation activation',
			'value'       => '1',
			'description' => 'Geolocation activation',
			'field'       => '{"name":"value","label":"Activation","type":"checkbox"}',
			'parent_id'   => 0,
			'lft'         => 14,
			'rgt'         => 19,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:33:22',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'app_default_country')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 8,
			'key'         => 'app_default_country',
			'name'        => 'Default Country',
			'value'       => 'CA',
			'description' => 'Default country (ISO alpha-2 codes - e.g. US)',
			'field'       => '',
			'parent_id'   => 7,
			'lft'         => 15,
			'rgt'         => 16,
			'depth'       => 2,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-14 22:27:49',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'activation_country_flag')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 9,
			'key'         => 'activation_country_flag',
			'name'        => 'Show country flag on top',
			'value'       => '1',
			'description' => 'Show country flag on top page',
			'field'       => '{"name":"value","label":"Activation","type":"checkbox"}',
			'parent_id'   => 7,
			'lft'         => 17,
			'rgt'         => 18,
			'depth'       => 2,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-14 22:27:49',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'activation_guests_can_post')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 10,
			'key'         => 'activation_guests_can_post',
			'name'        => 'Guests can post Ads',
			'value'       => '1',
			'description' => 'Guest can post Ad',
			'field'       => '{"name":"value","label":"Activation","type":"checkbox"}',
			'parent_id'   => 0,
			'lft'         => 20,
			'rgt'         => 25,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:33:22',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'require_users_activation')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 11,
			'key'         => 'require_users_activation',
			'name'        => 'Users activation required',
			'value'       => '1',
			'description' => 'Users activation required',
			'field'       => '{"name":"value","label":"Required","type":"checkbox"}',
			'parent_id'   => 10,
			'lft'         => 21,
			'rgt'         => 22,
			'depth'       => 2,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-14 22:27:49',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'require_ads_activation')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 12,
			'key'         => 'require_ads_activation',
			'name'        => 'Ads activation required',
			'value'       => '0',
			'description' => 'Ads activation required',
			'field'       => '{"name":"value","label":"Required","type":"checkbox"}',
			'parent_id'   => 10,
			'lft'         => 23,
			'rgt'         => 24,
			'depth'       => 2,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-14 22:27:49',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'activation_social_login')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 13,
			'key'         => 'activation_social_login',
			'name'        => 'Social Login Activation',
			'value'       => '0',
			'description' => 'Allow users to connect via social networks',
			'field'       => '{"name":"value","label":"Required","type":"checkbox"}',
			'parent_id'   => 0,
			'lft'         => 38,
			'rgt'         => 39,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:33:22',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'activation_facebook_comments')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 14,
			'key'         => 'activation_facebook_comments',
			'name'        => 'Facebook Comments activation',
			'value'       => '0',
			'description' => 'Allow Facebook comments on single page',
			'field'       => '{"name":"value","label":"Required","type":"checkbox"}',
			'parent_id'   => 0,
			'lft'         => 36,
			'rgt'         => 37,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:33:22',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'show_powered_by')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 15,
			'key'         => 'show_powered_by',
			'name'        => 'Show Powered by',
			'value'       => '1',
			'description' => 'Show Powered by infos',
			'field'       => '{"name":"value","label":"Activation","type":"checkbox"}',
			'parent_id'   => 0,
			'lft'         => 26,
			'rgt'         => 27,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:33:22',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'google_site_verification')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 16,
			'key'         => 'google_site_verification',
			'name'        => 'Google site verification content',
			'value'       => null,
			'description' => 'Google site verification content',
			'field'       => '',
			'parent_id'   => 0,
			'lft'         => 28,
			'rgt'         => 31,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:33:22',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'msvalidate')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 17,
			'key'         => 'msvalidate',
			'name'        => 'Bing site verification content',
			'value'       => null,
			'description' => 'Bing site verification content',
			'field'       => '',
			'parent_id'   => 18,
			'lft'         => 33,
			'rgt'         => 34,
			'depth'       => 2,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-14 22:28:49',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'alexa_verify_id')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 18,
			'key'         => 'alexa_verify_id',
			'name'        => 'Alexa site verification content',
			'value'       => null,
			'description' => 'Alexa site verification content',
			'field'       => '',
			'parent_id'   => 18,
			'lft'         => 35,
			'rgt'         => 36,
			'depth'       => 2,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-14 22:28:49',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'activation_home_stats')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 19,
			'key'         => 'activation_home_stats',
			'name'        => 'Show Homepage Stats',
			'value'       => '1',
			'description' => 'Show Homepage Stats (bottom page)',
			'field'       => '{"name":"value","label":"Activation","type":"checkbox"}',
			'parent_id'   => 0,
			'lft'         => 32,
			'rgt'         => 33,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:33:22',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'activation_serp_left_sidebar')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 20,
			'key'         => 'activation_serp_left_sidebar',
			'name'        => 'Search left sidebar activation',
			'value'       => '0',
			'description' => 'Search page (Left sidebar activation)',
			'field'       => '{"name":"value","label":"Activation","type":"checkbox"}',
			'parent_id'   => 16,
			'lft'         => 29,
			'rgt'         => 30,
			'depth'       => 2,
			'active'      => 0,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:33:22',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'seo_google_analytics')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 21,
			'key'         => 'seo_google_analytics',
			'name'        => 'Google Analytics tracking code',
			'value'       => null,
			'description' => 'Google Analytics tracking code',
			'field'       => '{"name":"value","label":"Value","type":"textarea"}',
			'parent_id'   => 0,
			'lft'         => 34,
			'rgt'         => 35,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:33:22',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'facebook_page_url')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 22,
			'key'         => 'facebook_page_url',
			'name'        => 'Facebook - Page URL',
			'value'       => 'https://web.facebook.com/larapencom',
			'description' => 'Website Facebook Page URL',
			'field'       => '',
			'parent_id'   => 0,
			'lft'         => 40,
			'rgt'         => 47,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:33:22',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'facebook_page_id')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 23,
			'key'         => 'facebook_page_id',
			'name'        => 'Facebook - Page ID',
			'value'       => '806182476160185',
			'description' => 'Website Facebook Page ID (Not username)',
			'field'       => '',
			'parent_id'   => 22,
			'lft'         => 41,
			'rgt'         => 42,
			'depth'       => 2,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:26:15',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'facebook_client_id')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 24,
			'key'         => 'facebook_client_id',
			'name'        => 'Facebook Client ID',
			'value'       => null,
			'description' => 'Facebook Client ID',
			'field'       => '',
			'parent_id'   => 22,
			'lft'         => 43,
			'rgt'         => 44,
			'depth'       => 2,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:26:15',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'facebook_client_secret')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 25,
			'key'         => 'facebook_client_secret',
			'name'        => 'Facebook Client Secret',
			'value'       => null,
			'description' => 'Facebook Client Secret',
			'field'       => '',
			'parent_id'   => 22,
			'lft'         => 45,
			'rgt'         => 46,
			'depth'       => 2,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:26:15',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'google_client_id')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 26,
			'key'         => 'google_client_id',
			'name'        => 'Google Client ID',
			'value'       => null,
			'description' => 'Google Client ID',
			'field'       => '',
			'parent_id'   => 0,
			'lft'         => 48,
			'rgt'         => 49,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:33:22',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'google_client_secret')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 27,
			'key'         => 'google_client_secret',
			'name'        => 'Google Client Secret',
			'value'       => null,
			'description' => 'Google Client Secret',
			'field'       => '',
			'parent_id'   => 26,
			'lft'         => 53,
			'rgt'         => 54,
			'depth'       => 2,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-14 23:42:29',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'googlemaps_key')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 28,
			'key'         => 'googlemaps_key',
			'name'        => 'Google Maps key',
			'value'       => null,
			'description' => 'Google Maps key',
			'field'       => '',
			'parent_id'   => 26,
			'lft'         => 55,
			'rgt'         => 56,
			'depth'       => 2,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-14 23:42:29',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'twitter_url')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 29,
			'key'         => 'twitter_url',
			'name'        => 'Twitter - URL',
			'value'       => 'https://twitter.com/larapencom',
			'description' => 'Website Twitter URL',
			'field'       => '',
			'parent_id'   => 0,
			'lft'         => 50,
			'rgt'         => 57,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:33:22',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'twitter_username')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 30,
			'key'         => 'twitter_username',
			'name'        => 'Twitter - Username',
			'value'       => 'larapencom',
			'description' => 'Website Twitter username',
			'field'       => '',
			'parent_id'   => 29,
			'lft'         => 51,
			'rgt'         => 52,
			'depth'       => 2,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:29:26',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'twitter_client_id')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 31,
			'key'         => 'twitter_client_id',
			'name'        => 'Twitter Client ID',
			'value'       => null,
			'description' => 'Twitter Client ID',
			'field'       => '',
			'parent_id'   => 29,
			'lft'         => 53,
			'rgt'         => 54,
			'depth'       => 2,
			'active'      => 0,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:29:26',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'twitter_client_secret')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 32,
			'key'         => 'twitter_client_secret',
			'name'        => 'Twitter Client Secret',
			'value'       => null,
			'description' => 'Twitter Client Secret',
			'field'       => '',
			'parent_id'   => 29,
			'lft'         => 55,
			'rgt'         => 56,
			'depth'       => 2,
			'active'      => 0,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:29:26',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'activation_recaptcha')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 33,
			'key'         => 'activation_recaptcha',
			'name'        => 'Recaptcha activation',
			'value'       => '0',
			'description' => 'Recaptcha activation',
			'field'       => '{"name":"value","label":"Activation","type":"checkbox"}',
			'parent_id'   => 0,
			'lft'         => 58,
			'rgt'         => 63,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:33:22',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'recaptcha_public_key')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 34,
			'key'         => 'recaptcha_public_key',
			'name'        => 'reCAPTCHA public key',
			'value'       => null,
			'description' => 'reCAPTCHA public key',
			'field'       => '',
			'parent_id'   => 33,
			'lft'         => 59,
			'rgt'         => 60,
			'depth'       => 2,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:29:26',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'recaptcha_private_key')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 35,
			'key'         => 'recaptcha_private_key',
			'name'        => 'reCAPTCHA private key',
			'value'       => null,
			'description' => 'reCAPTCHA private key',
			'field'       => '',
			'parent_id'   => 33,
			'lft'         => 61,
			'rgt'         => 62,
			'depth'       => 2,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:29:26',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'paypal_mode')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 36,
			'key'         => 'paypal_mode',
			'name'        => 'PayPal mode',
			'value'       => 'sandbox',
			'description' => 'PayPal mode (e.g. sandbox, live)',
			'field'       => '',
			'parent_id'   => 0,
			'lft'         => 88,
			'rgt'         => 95,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:33:22',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'paypal_username')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 37,
			'key'         => 'paypal_username',
			'name'        => 'PayPal username',
			'value'       => null,
			'description' => 'PayPal username',
			'field'       => '',
			'parent_id'   => 36,
			'lft'         => 89,
			'rgt'         => 90,
			'depth'       => 2,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:32:06',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'paypal_password')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 38,
			'key'         => 'paypal_password',
			'name'        => 'PayPal password',
			'value'       => null,
			'description' => 'PayPal password',
			'field'       => '',
			'parent_id'   => 36,
			'lft'         => 91,
			'rgt'         => 92,
			'depth'       => 2,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:32:06',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'paypal_signature')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 39,
			'key'         => 'paypal_signature',
			'name'        => 'PayPal signature',
			'value'       => null,
			'description' => 'PayPal signature',
			'field'       => '',
			'parent_id'   => 36,
			'lft'         => 93,
			'rgt'         => 94,
			'depth'       => 2,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:32:06',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'mail_driver')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 40,
			'key'         => 'mail_driver',
			'name'        => 'Mail driver',
			'value'       => 'smtp',
			'description' => 'e.g. smtp, mail, sendmail, mailgun, mandrill, ses',
			'field'       => '',
			'parent_id'   => 0,
			'lft'         => 64,
			'rgt'         => 75,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:33:22',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'mail_host')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 41,
			'key'         => 'mail_host',
			'name'        => 'Mail host',
			'value'       => null,
			'description' => 'SMTP host',
			'field'       => '',
			'parent_id'   => 40,
			'lft'         => 65,
			'rgt'         => 66,
			'depth'       => 2,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:31:42',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'mail_port')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 42,
			'key'         => 'mail_port',
			'name'        => 'Mail port',
			'value'       => '25',
			'description' => 'SMTP port (e.g. 25, 587, ...)',
			'field'       => '',
			'parent_id'   => 40,
			'lft'         => 67,
			'rgt'         => 68,
			'depth'       => 2,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:31:42',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'mail_encryption')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 43,
			'key'         => 'mail_encryption',
			'name'        => 'Mail encryption',
			'value'       => 'tls',
			'description' => 'SMTP encryption (e.g. tls, ssl, starttls)',
			'field'       => '',
			'parent_id'   => 40,
			'lft'         => 69,
			'rgt'         => 70,
			'depth'       => 2,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:31:42',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'mail_username')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 44,
			'key'         => 'mail_username',
			'name'        => 'Mail username',
			'value'       => null,
			'description' => 'SMTP username',
			'field'       => '',
			'parent_id'   => 40,
			'lft'         => 71,
			'rgt'         => 72,
			'depth'       => 2,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:31:42',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'mail_password')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 45,
			'key'         => 'mail_password',
			'name'        => 'Mail password',
			'value'       => null,
			'description' => 'SMTP password',
			'field'       => '',
			'parent_id'   => 40,
			'lft'         => 73,
			'rgt'         => 74,
			'depth'       => 2,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:31:42',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'mailgun_domain')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 46,
			'key'         => 'mailgun_domain',
			'name'        => 'Mailgun domain',
			'value'       => null,
			'description' => 'Mailgun domain',
			'field'       => '',
			'parent_id'   => 0,
			'lft'         => 76,
			'rgt'         => 79,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:33:22',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'mailgun_secret')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 47,
			'key'         => 'mailgun_secret',
			'name'        => 'Mailgun secret',
			'value'       => null,
			'description' => 'Mailgun secret',
			'field'       => '',
			'parent_id'   => 46,
			'lft'         => 77,
			'rgt'         => 78,
			'depth'       => 2,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:31:42',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'mandrill_secret')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 48,
			'key'         => 'mandrill_secret',
			'name'        => 'Mandrill secret',
			'value'       => null,
			'description' => 'Mandrill secret',
			'field'       => '',
			'parent_id'   => 0,
			'lft'         => 80,
			'rgt'         => 81,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:33:22',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'ses_key')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 49,
			'key'         => 'ses_key',
			'name'        => 'SES key',
			'value'       => null,
			'description' => 'SES key',
			'field'       => '',
			'parent_id'   => 0,
			'lft'         => 82,
			'rgt'         => 87,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:33:22',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'ses_secret')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 50,
			'key'         => 'ses_secret',
			'name'        => 'SES secret',
			'value'       => null,
			'description' => 'SES secret',
			'field'       => '',
			'parent_id'   => 49,
			'lft'         => 83,
			'rgt'         => 84,
			'depth'       => 2,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:32:06',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'ses_region')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 51,
			'key'         => 'ses_region',
			'name'        => 'SES region',
			'value'       => null,
			'description' => 'SES region',
			'field'       => '',
			'parent_id'   => 49,
			'lft'         => 85,
			'rgt'         => 86,
			'depth'       => 2,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:32:06',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'stripe_secret')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 52,
			'key'         => 'stripe_secret',
			'name'        => 'Stripe secret',
			'value'       => null,
			'description' => 'Stripe secret',
			'field'       => '',
			'parent_id'   => 0,
			'lft'         => 97,
			'rgt'         => 98,
			'depth'       => 2,
			'active'      => 0,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:31:42',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'stripe_key')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 53,
			'key'         => 'stripe_key',
			'name'        => 'Stripe key',
			'value'       => null,
			'description' => 'Stripe key',
			'field'       => '',
			'parent_id'   => 0,
			'lft'         => 96,
			'rgt'         => 99,
			'depth'       => 1,
			'active'      => 0,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:33:22',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'sparkpost_secret')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 54,
			'key'         => 'sparkpost_secret',
			'name'        => 'Sparkpost secret',
			'value'       => null,
			'description' => 'Sparkpost secret',
			'field'       => '',
			'parent_id'   => 0,
			'lft'         => 100,
			'rgt'         => 101,
			'depth'       => 1,
			'active'      => 0,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:33:22',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'app_cache_expire')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 55,
			'key'         => 'app_cache_expire',
			'name'        => 'Cache Expire duration',
			'value'       => '60',
			'description' => 'Cache Expire duration (in seconde)',
			'field'       => '',
			'parent_id'   => 0,
			'lft'         => 102,
			'rgt'         => 103,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-15 00:33:22',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'app_cookie_expire')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 56,
			'key'         => 'app_cookie_expire',
			'name'        => 'Cookie Expire duration',
			'value'       => '2592000',
			'description' => 'Cookie Expire duration (in seconde)',
			'field'       => '',
			'parent_id'   => 55,
			'lft'         => 111,
			'rgt'         => 112,
			'depth'       => 2,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-14 23:42:29',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'activation_minify_html')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 57,
			'key'         => 'activation_minify_html',
			'name'        => 'HTML Minify activation',
			'value'       => '0',
			'description' => 'Optimization - HTML Minify activation',
			'field'       => '{"name":"value","label":"Activation","type":"checkbox"}',
			'parent_id'   => 55,
			'lft'         => 113,
			'rgt'         => 114,
			'depth'       => 2,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-14 23:42:29',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'activation_http_cache')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 58,
			'key'         => 'activation_http_cache',
			'name'        => 'HTTP Cache activation',
			'value'       => '0',
			'description' => 'Optimization - HTTP Cache activation',
			'field'       => '{"name":"value","label":"Activation","type":"checkbox"}',
			'parent_id'   => 55,
			'lft'         => 115,
			'rgt'         => 116,
			'depth'       => 2,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2016-06-14 23:42:29',
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'show_country_svgmap')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 59,
			'key'         => 'show_country_svgmap',
			'name'        => 'Show country SVG map',
			'value'       => '1',
			'description' => 'Show country SVG map on the homepage',
			'field'       => '{"name":"value","label":"Show","type":"checkbox"}',
			'parent_id'   => 0,
			'lft'         => 200,
			'rgt'         => null,
			'depth'       => null,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => null,
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'ads_pictures_number')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 60,
			'key'         => 'ads_pictures_number',
			'name'        => 'Ad\'s photos number',
			'value'       => '3',
			'description' => 'Ad\'s photos number',
			'field'       => '',
			'parent_id'   => null,
			'lft'         => 0,
			'rgt'         => null,
			'depth'       => null,
			'active'      => 1,
			'created_at'  => '2016-06-15 17:31:46',
			'updated_at'  => null,
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'show_ad_on_googlemap')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 61,
			'key'         => 'show_ad_on_googlemap',
			'name'        => 'Show Ads on Google Maps',
			'value'       => '1',
			'description' => 'Show Ads on Google Maps (Single page only)',
			'field'       => '{"name":"value","label":"Show","type":"checkbox"}',
			'parent_id'   => null,
			'lft'         => null,
			'rgt'         => null,
			'depth'       => null,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => null,
		];
		DB::table('settings')->insert($data);
	}
	
	$setting = \App\Models\Setting::where('key', 'custom_css')->first();
	if (empty($setting)) {
		$data = [
			'id'          => 62,
			'key'         => 'custom_css',
			'name'        => 'Custom CSS',
			'value'       => null,
			'description' => 'Custom CSS for your site',
			'field'       => '{"name":"value","label":"Value","type":"textarea"}',
			'parent_id'   => null,
			'lft'         => null,
			'rgt'         => null,
			'depth'       => null,
			'active'      => 1,
			'created_at'  => '2016-06-16 15:15:25',
			'updated_at'  => null,
		];
		DB::table('settings')->insert($data);
	}
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
