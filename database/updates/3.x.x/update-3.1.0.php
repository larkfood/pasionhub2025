<?php

use App\Exceptions\Custom\CustomException;
use App\Helpers\DBTool\DBIndex;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;


// ===| FILES |===
try {
	
	File::deleteDirectory(app_path('Http/Controllers/Ad/'));
	File::deleteDirectory(app_path('Jobs/'));
	File::deleteDirectory(base_path('database/umpirsky/country/icu/'));
	File::deleteDirectory(public_path('assets/css/style/'));
	File::deleteDirectory(public_path('uploads/app/categories/blue/'));
	File::deleteDirectory(public_path('uploads/app/categories/default/'));
	File::deleteDirectory(public_path('uploads/app/categories/green/'));
	File::deleteDirectory(public_path('uploads/app/categories/yellow/'));
	File::deleteDirectory(base_path('resources/views/ad/'));
	File::deleteDirectory(base_path('resources/views/emails/ad/'));
	File::deleteDirectory(base_path('resources/views/layouts/inc/tools/svgmap/'));
	
	File::delete(File::glob(app_path('Mail') . '/Ad*.php'));
	File::delete(File::glob(public_path('assets/css') . '/fileinput*.css'));
	File::delete(File::glob(public_path('assets/js') . '/fileinput*.js'));
	File::delete(File::glob(base_path('resources/views/search/inc') . '/ads*.php'));
	
	File::delete(app_path('Events/AdWasVisited.php'));
	File::delete(app_path('Helpers/Rules.php'));
	File::delete(app_path('Helpers/Validator.php'));
	File::delete(app_path('Http/Controllers/Account/AdsController.php'));
	File::delete(app_path('Http/Controllers/Admin/AdController.php'));
	File::delete(app_path('Http/Controllers/Admin/AdTypeController.php'));
	File::delete(app_path('Http/Controllers/Ajax/AdController.php'));
	File::delete(app_path('Http/Controllers/Ajax/AutocompleteController.php'));
	File::delete(app_path('Http/Controllers/Ajax/JsonController.php'));
	File::delete(app_path('Http/Controllers/Ajax/PlacesController.php'));
	File::delete(app_path('Http/Controllers/Ajax/StateCitiesController.php'));
	File::delete(app_path('Http/Requests/Admin/AdRequest.php'));
	File::delete(app_path('Http/Requests/Admin/AdTypeRequest.php'));
	File::delete(app_path('Listeners/UpdateTheAdCounter.php'));
	File::delete(app_path('Models/Ad.php'));
	File::delete(app_path('Models/AdType.php'));
	File::delete(app_path('Models/SavedAd.php'));
	File::delete(public_path('uploads/app/default/categories/fa-folder-blue.png'));
	File::delete(public_path('uploads/app/default/categories/fa-folder-default.png'));
	File::delete(public_path('uploads/app/default/categories/fa-folder-green.png'));
	File::delete(public_path('uploads/app/default/categories/fa-folder-red.png'));
	File::delete(public_path('uploads/app/default/categories/fa-folder-yellow.png'));
	File::delete(base_path('resources/views/account/ads.blade.php'));
	File::delete(base_path('resources/views/account/inc/sidebar-left.blade.php'));
	File::delete(base_path('resources/views/auth/signup/activation.blade.php'));
	File::delete(base_path('resources/views/auth/signup/success.blade.php'));
	File::delete(base_path('resources/views/home/inc/bottom-info.blade.php'));
	File::delete(base_path('resources/views/layouts/inc/carousel.blade.php'));
	
	if (File::exists(public_path('assets/css/style/custom.css'))) {
		File::delete(public_path('css/custom.css'));
		File::move(public_path('assets/css/style/custom.css'), public_path('css/custom.css'));
	}
	
} catch (\Throwable $e) {
}

// ===| DATABASE |===
try {
	
	// advertising
	Schema::table('advertising', function (Blueprint $table) {
		if (!Schema::hasColumn('advertising', 'slug')) {
			$table->string('slug')->after('id');
		} else {
			$table->string('slug')->change();
		}
	});
	
	DBIndex::dropIndexIfExists('advertising', 'slug');
	DBIndex::dropIndexIfExists('advertising', 'slug', 'unique');
	DBIndex::createIndexIfNotExists('advertising', 'slug', 'unique');
	
	DBIndex::dropIndexIfExists('advertising', 'active');
	DBIndex::createIndexIfNotExists('advertising', 'active');
	
	// categories
	DBIndex::dropIndexIfExists('categories', 'slug');
	DBIndex::createIndexIfNotExists('categories', 'slug');
	
	// cities
	DBIndex::dropIndexIfExists('cities', 'subadmin1_code');
	DBIndex::createIndexIfNotExists('cities', 'subadmin1_code');
	
	DBIndex::dropIndexIfExists('cities', 'subadmin2_code');
	DBIndex::createIndexIfNotExists('cities', 'subadmin2_code');
	
	DBIndex::dropIndexIfExists('cities', 'active');
	DBIndex::createIndexIfNotExists('cities', 'active');
	
	$condition1 = "LENGTH(subadmin1_code) > 0";
	$then1 = "CONCAT(country_code, ' . ', subadmin1_code)";
	$else1 = "NULL";
	
	$condition2 = "LENGTH(subadmin2_code) > 0";
	$then2 = "CONCAT(IF(LENGTH(subadmin1_code) > 0, subadmin1_code, country_code), ' . ', subadmin2_code)";
	$else2 = "NULL";
	
	DB::table('cities')
		->update([
			'subadmin1_code' => DB::raw("IF(" . $condition1 . ", " . $then1 . ", " . $else1 . ")"),
			'subadmin2_code' => DB::raw("IF(" . $condition2 . ", " . $then2 . ", " . $else2 . ")"),
		]);
	
	// continents
	DBIndex::dropIndexIfExists('continents', 'active');
	DBIndex::createIndexIfNotExists('continents', 'active');
	
	// countries
	if (!Schema::hasColumn('countries', 'admin_type') && Schema::hasColumn('countries', 'equivalent_fips_code')) {
		Schema::table('countries', function (Blueprint $table) {
			$table->enum('admin_type', ['0', '1', '2'])->default('0')->after('equivalent_fips_code');
		});
	}
	if (!Schema::hasColumn('countries', 'admin_field_active') && Schema::hasColumn('countries', 'admin_type')) {
		Schema::table('countries', function (Blueprint $table) {
			$table->boolean('admin_field_active')->unsigned()->nullable()->default('0')->after('admin_type');
		});
	}
	
	DBIndex::dropIndexIfExists('countries', 'active');
	DBIndex::createIndexIfNotExists('countries', 'active');
	
	// currencies
	if (Schema::hasColumn('currencies', 'in_left')) {
		Schema::table('currencies', function (Blueprint $table) {
			$table->boolean('in_left')->unsigned()->nullable()->default(0)->change();
		});
	}
	
	$currency = \App\Models\Currency::where('code', '=', 'XBT')->first();
	if (empty($currency)) {
		$data = [
			'code'               => 'XBT',
			'name'               => 'Bitcoin',
			'html_entity'        => '฿',
			'font_arial'         => '฿',
			'font_code2000'      => '฿',
			'in_left'            => 1,
			'decimal_places'     => 2,
			'decimal_separator'  => '.',
			'thousand_separator' => ',',
			'created_at'         => '2017-04-08 04:49:08',
			'updated_at'         => null,
		];
		DB::table('currencies')->insert($data);
	}
	
	// languages
	DBIndex::dropIndexIfExists('languages', 'active');
	DBIndex::createIndexIfNotExists('languages', 'active');
	
	DBIndex::dropIndexIfExists('languages', 'default');
	DBIndex::createIndexIfNotExists('languages', 'default');
	
	// messages
	if (!Schema::hasColumn('messages', 'reply_sent') && Schema::hasColumn('messages', 'filename')) {
		Schema::table('messages', function (Blueprint $table) {
			$table->boolean('reply_sent')->unsigned()->default(0)->after('filename');
		});
	}
	if (Schema::hasColumn('messages', 'ad_id') && !Schema::hasColumn('messages', 'post_id')) {
		Schema::table('messages', function (Blueprint $table) {
			$table->renameColumn('ad_id', 'post_id');
		});
	}
	
	DBIndex::dropIndexIfExists('messages', 'post_id');
	DBIndex::createIndexIfNotExists('messages', 'post_id');
	
	// packages (re-do update v2.9.0 part)
	if (Schema::hasTable('packs') && !Schema::hasTable('packages')) {
		Schema::rename('packs', 'packages');
	}
	
	// packages
	if (Schema::hasTable('packages')) {
		if (Schema::hasColumn('packages', 'has_badge')) {
			Schema::table('packages', function (Blueprint $table) {
				$table->boolean('has_badge')->unsigned()->nullable()->default(0)->change();
			});
		}
		if (Schema::hasColumn('packages', 'active')) {
			Schema::table('packages', function (Blueprint $table) {
				$table->boolean('active')->unsigned()->nullable()->default(0)->change();
			});
		}
		DBIndex::dropIndexIfExists('packages', 'active');
		DBIndex::createIndexIfNotExists('packages', 'active');
	}
	
	// pages
	if (Schema::hasColumn('pages', 'excluded_from_footer')) {
		Schema::table('pages', function (Blueprint $table) {
			$table->boolean('excluded_from_footer')->unsigned()->default(0)->change();
		});
	}
	if (Schema::hasColumn('pages', 'active')) {
		Schema::table('pages', function (Blueprint $table) {
			$table->boolean('active')->unsigned()->nullable()->default(1)->change();
		});
	}
	DBIndex::dropIndexIfExists('pages', 'active');
	DBIndex::createIndexIfNotExists('pages', 'active');
	
	// payments
	if (!Schema::hasColumn('payments', 'active') && Schema::hasColumn('payments', 'payment_method_id')) {
		Schema::table('payments', function (Blueprint $table) {
			$table->boolean('active')->unsigned()->default(1)->after('payment_method_id');
		});
	}
	if (Schema::hasColumn('payments', 'ad_id') && !Schema::hasColumn('payments', 'post_id')) {
		Schema::table('payments', function (Blueprint $table) {
			$table->renameColumn('ad_id', 'post_id');
		});
	}
	if (!Schema::hasColumn('payments', 'transaction_id') && Schema::hasColumn('payments', 'payment_method_id')) {
		Schema::table('payments', function (Blueprint $table) {
			$table->string('transaction_id', 191)->nullable()->comment("Transaction's ID at the Provider")->after('payment_method_id');
		});
	}
	DBIndex::dropIndexIfExists('payments', 'ad_id');
	
	DBIndex::dropIndexIfExists('payments', 'post_id');
	DBIndex::createIndexIfNotExists('payments', 'post_id');
	
	DBIndex::dropIndexIfExists('payments', 'active');
	DBIndex::createIndexIfNotExists('payments', 'active');
	
	// payment_methods
	if (!Schema::hasColumn('payment_methods', 'countries') && Schema::hasColumn('payment_methods', 'has_ccbox')) {
		Schema::table('payment_methods', function (Blueprint $table) {
			$table->text('countries')->nullable()->comment('Countries codes separated by comma.')->after('has_ccbox');
		});
	}
	DBIndex::dropIndexIfExists('payment_methods', 'has_ccbox');
	DBIndex::createIndexIfNotExists('payment_methods', 'has_ccbox');
	
	DBIndex::dropIndexIfExists('payment_methods', 'active');
	DBIndex::createIndexIfNotExists('payment_methods', 'active');
	
	// pictures
	if (Schema::hasColumn('pictures', 'ad_id') && !Schema::hasColumn('pictures', 'post_id')) {
		Schema::table('pictures', function (Blueprint $table) {
			$table->renameColumn('ad_id', 'post_id');
		});
	}
	if (Schema::hasColumn('pictures', 'active')) {
		Schema::table('pictures', function (Blueprint $table) {
			$table->boolean('active')->unsigned()->nullable()->default(1)->change();
		});
	}
	DBIndex::dropIndexIfExists('pictures', 'ad_id');
	
	DBIndex::dropIndexIfExists('pictures', 'post_id');
	DBIndex::createIndexIfNotExists('pictures', 'post_id');
	
	DBIndex::dropIndexIfExists('pictures', 'active');
	DBIndex::createIndexIfNotExists('pictures', 'active');
	
	// posts
	if (Schema::hasTable('ads') && !Schema::hasTable('posts')) {
		Schema::rename('ads', 'posts');
	}
	
	if (Schema::hasTable('posts')) {
		if (
			Schema::hasColumn('posts', 'package_id')
			&& Schema::hasColumn('posts', 'resume')
			&& Schema::hasColumn('posts', 'new')
			&& Schema::hasColumn('posts', 'brand')
		) {
			Schema::table('posts', function (Blueprint $table) {
				$table->dropColumn(['package_id', 'resume', 'new', 'brand']);
			});
		}
		if (!Schema::hasColumn('posts', 'tmp_token') && Schema::hasColumn('posts', 'activation_token')) {
			Schema::table('posts', function (Blueprint $table) {
				$table->string('tmp_token', 32)->nullable()->after('activation_token');
			});
		}
		if (Schema::hasColumn('posts', 'ad_type_id') && !Schema::hasColumn('posts', 'post_type_id')) {
			Schema::table('posts', function (Blueprint $table) {
				$table->renameColumn('ad_type_id', 'post_type_id');
			});
		}
		if (Schema::hasColumn('posts', 'activation_token') && !Schema::hasColumn('posts', 'email_token')) {
			Schema::table('posts', function (Blueprint $table) {
				$table->renameColumn('activation_token', 'email_token');
			});
		}
		if (Schema::hasColumn('posts', 'email_token')) {
			Schema::table('posts', function (Blueprint $table) {
				$table->string('email_token', 32)->nullable()->change();
			});
		}
		if (!Schema::hasColumn('posts', 'email_token')) {
			Schema::table('posts', function (Blueprint $table) {
				$table->string('email_token', 32)->nullable()->after('visits');
			});
		}
		if (!Schema::hasColumn('posts', 'phone_token') && Schema::hasColumn('posts', 'email_token')) {
			Schema::table('posts', function (Blueprint $table) {
				$table->string('phone_token', 32)->nullable()->after('email_token');
			});
		}
		if (Schema::hasColumn('posts', 'verified_email')) {
			Schema::table('posts', function (Blueprint $table) {
				$table->boolean('verified_email')->unsigned()->nullable()->default(0)->change();
			});
		}
		if (!Schema::hasColumn('posts', 'verified_email')) {
			Schema::table('posts', function (Blueprint $table) {
				$table->boolean('verified_email')->unsigned()->nullable()->default(0)->after('phone_token');
			});
		}
		if (!Schema::hasColumn('posts', 'verified_phone') && Schema::hasColumn('posts', 'verified_email')) {
			Schema::table('posts', function (Blueprint $table) {
				$table->boolean('verified_phone')->unsigned()->nullable()->default(1)->after('verified_email');
			});
		}
		if (Schema::hasColumn('posts', 'seller_name') && !Schema::hasColumn('posts', 'contact_name')) {
			Schema::table('posts', function (Blueprint $table) {
				$table->renameColumn('seller_name', 'contact_name');
			});
		}
		if (Schema::hasColumn('posts', 'seller_email') && !Schema::hasColumn('posts', 'email')) {
			Schema::table('posts', function (Blueprint $table) {
				$table->renameColumn('seller_email', 'email');
			});
		}
		if (Schema::hasColumn('posts', 'seller_phone') && !Schema::hasColumn('posts', 'phone')) {
			Schema::table('posts', function (Blueprint $table) {
				$table->renameColumn('seller_phone', 'phone');
			});
		}
		if (Schema::hasColumn('posts', 'seller_phone_hidden') && !Schema::hasColumn('posts', 'phone_hidden')) {
			Schema::table('posts', function (Blueprint $table) {
				$table->renameColumn('seller_phone_hidden', 'phone_hidden');
			});
		}
		if (Schema::hasColumn('posts', 'country_code')) {
			Schema::table('posts', function (Blueprint $table) {
				$table->string('country_code', 2)->nullable()->change();
			});
		}
		if (Schema::hasColumn('posts', 'category_id')) {
			Schema::table('posts', function (Blueprint $table) {
				$table->integer('category_id')->unsigned()->default(0)->change();
			});
		}
		if (Schema::hasColumn('posts', 'city_id')) {
			Schema::table('posts', function (Blueprint $table) {
				$table->integer('city_id')->unsigned()->default(0)->change();
			});
		}
		DBIndex::dropIndexIfExists('posts', 'ad_type_id');
		DBIndex::dropIndexIfExists('posts', 'seller_name');
		DBIndex::dropIndexIfExists('posts', 'verified_email');
		
		DBIndex::dropIndexIfExists('posts', 'post_type_id');
		DBIndex::createIndexIfNotExists('posts', 'post_type_id');
		
		DBIndex::dropIndexIfExists('posts', 'contact_name');
		DBIndex::createIndexIfNotExists('posts', 'contact_name');
		
		DBIndex::dropIndexIfExists('posts', 'verified_email');
		DBIndex::createIndexIfNotExists('posts', 'verified_email');
		
		DBIndex::dropIndexIfExists('posts', 'verified_phone');
		DBIndex::createIndexIfNotExists('posts', 'verified_phone');
		
		// Update the existing records
		if (Schema::hasColumn('posts', 'verified_phone')) {
			DB::table('posts')->update(['verified_phone' => 1]);
		}
	}
	
	// post_types
	if (Schema::hasTable('ad_type') && !Schema::hasTable('post_types')) {
		Schema::rename('ad_type', 'post_types');
	}
	
	if (Schema::hasTable('post_types')) {
		if (Schema::hasColumn('post_types', 'active')) {
			Schema::table('post_types', function (Blueprint $table) {
				$table->boolean('active')->unsigned()->nullable()->default(1)->change();
			});
		}
		DBIndex::dropIndexIfExists('post_types', 'active');
		DBIndex::createIndexIfNotExists('post_types', 'active');
	}
	
	// report_type
	if (Schema::hasTable('report_type') && !Schema::hasTable('report_types')) {
		Schema::rename('report_type', 'report_types');
	}
	
	// Drop and recreate roles related tables
	Schema::disableForeignKeyConstraints();
	
	Schema::dropIfExists('permissions');
	Schema::dropIfExists('permission_role');
	Schema::dropIfExists('roles');
	Schema::dropIfExists('role_users');
	
	Schema::enableForeignKeyConstraints();
	
	// saved_posts
	if (Schema::hasTable('saved_ads') && !Schema::hasTable('saved_posts')) {
		Schema::rename('saved_ads', 'saved_posts');
	}
	
	if (Schema::hasTable('saved_posts')) {
		if (Schema::hasColumn('saved_posts', 'ad_id') && !Schema::hasColumn('saved_posts', 'post_id')) {
			Schema::table('saved_posts', function (Blueprint $table) {
				$table->renameColumn('ad_id', 'post_id');
			});
			
			DBIndex::dropIndexIfExists('saved_posts', 'ad_id');
			
			DBIndex::dropIndexIfExists('saved_posts', 'post_id');
			DBIndex::createIndexIfNotExists('saved_posts', 'post_id');
		}
	}
	
	// settings
	DBIndex::dropIndexIfExists('settings', 'active');
	DBIndex::createIndexIfNotExists('settings', 'active');
	
	DB::table('settings')->where('key', 'require_users_activation')->update([
		'key'         => 'email_verification',
		'name'        => 'Email verification required',
		'description' => 'Email verification required',
	]);
	DB::table('settings')->where('key', 'require_ads_activation')->update([
		'key'         => 'phone_verification',
		'name'        => 'Phone verification required',
		'description' => 'Phone verification required',
	]);
	DB::table('settings')->where('key', 'app_cache_expire')->update([
		'key'         => 'app_cache_expiration',
		'name'        => 'Cache Expiration Time',
		'description' => 'Cache Expiration Time (in minutes)',
	]);
	DB::table('settings')->where('key', 'app_cookie_expire')->update([
		'key'         => 'app_cookie_expiration',
		'name'        => 'Cookie Expiration Time',
		'description' => 'Cookie Expiration Time (in seconds)',
	]);
	DB::table('settings')->where('key', 'app_theme')->update([
		'key'   => 'app_skin',
		'name'  => 'Front Skin',
		'value' => DB::raw("IF(LENGTH(`value`) > 0, CONCAT('skin-', `value`), NULL)"),
		'field' => '{"name":"value","label":"Value","type":"select_from_array","options":{"skin-default":"Default","skin-blue":"Blue","skin-yellow":"Yellow","skin-green":"Green","skin-red":"Red"}}',
	]);
	DB::table('settings')->where('key', 'admin_theme')->update([
		'key'         => 'admin_skin',
		'name'        => 'Admin Skin',
		'description' => 'Admin Panel Skin',
	]);
	DB::table('settings')->where('key', 'sparkpost_secret')->update(['active' => 1]);
	DB::table('settings')->where('key', 'mail_driver')->update([
		'description' => 'Before enabling this option you need to download the Maxmind database by following the documentation: https://laraclassifier.com/doc/geo-location/',
		'field'       => '{"name":"value","label":"Value","type":"select_from_array","options":{"smtp":"SMTP","mailgun":"Mailgun","mandrill":"Mandrill","ses":"Amazon SES","sparkpost":"Sparkpost","mail":"PHP Mail","sendmail":"Sendmail"}}',
	]);
	DB::table('settings')->where('key', 'show_ad_on_googlemap')->update(['key' => 'show_post_on_googlemap']);
	DB::table('settings')->where('key', 'unactivated_ads_expiration')->update(['key' => 'unactivated_listings_expiration']);
	DB::table('settings')->where('key', 'activated_ads_expiration')->update(['key' => 'activated_listings_expiration']);
	DB::table('settings')->where('key', 'archived_ads_expiration')->update(['key' => 'archived_listings_expiration']);
	DB::table('settings')->where('key', 'ads_per_page')->update(['key' => 'posts_per_page']);
	DB::table('settings')->where('key', 'ads_pictures_number')->update(['key' => 'posts_pictures_number']);
	DB::table('settings')->where('key', 'ads_review_activation')->update(['key' => 'posts_review_activation']);
	
	// Deleting unnecessary settings
	DB::table('settings')->whereIn('key', [
		'meta_description',
		'activation_home_stats',
		'facebook_page_fans',
		'show_country_svgmap',
	])->delete();
	
	// Inserting new settings
	$allData = [
		[
			'key'         => 'sms_driver',
			'name'        => 'SMS driver',
			'value'       => 'nexmo',
			'description' => 'e.g. nexmo, twilio',
			'field'       => '{"name":"value","label":"Value","type":"select_from_array","options":{"nexmo":"Nexmo","twilio":"Twilio"}}',
			'parent_id'   => 0,
			'lft'         => 86,
			'rgt'         => 86,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => '2017-04-12 13:06:19',
			'updated_at'  => null,
		],
		[
			'key'         => 'sms_message_activation',
			'name'        => 'SMS Message Activation',
			'value'       => '0',
			'description' => 'Users can contact the author by SMS. Note: You need to set the "SMS driver" setting.',
			'field'       => '{"name":"value","label":"Activation","type":"checkbox"}',
			'parent_id'   => 0,
			'lft'         => 87,
			'rgt'         => 87,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => '2017-06-17 13:06:19',
			'updated_at'  => null,
		],
	];
	foreach ($allData as $item) {
		$key = $item['key'] ?? '';
		
		$setting = \App\Models\Setting::where('key', $key)->first();
		if (empty($setting)) {
			DB::table('settings')->insert($item);
		}
	}
	
	// subadmin1
	if (!Schema::hasColumn('subadmin1', 'country_code') && Schema::hasColumn('subadmin1', 'code')) {
		Schema::table('subadmin1', function (Blueprint $table) {
			$table->string('country_code', 2)->nullable()->after('code');
		});
	}
	
	DBIndex::dropIndexIfExists('subadmin1', 'active');
	DBIndex::createIndexIfNotExists('subadmin1', 'active');
	
	DBIndex::dropIndexIfExists('subadmin1', 'country_code');
	DBIndex::createIndexIfNotExists('subadmin1', 'country_code');
	
	DB::table('subadmin1')->update(['country_code' => DB::raw('SUBSTRING(code, 1, 2)')]);
	
	// subadmin2
	if (!Schema::hasColumn('subadmin2', 'country_code') && Schema::hasColumn('subadmin2', 'code')) {
		Schema::table('subadmin2', function (Blueprint $table) {
			$table->string('country_code', 2)->nullable()->after('code');
		});
	}
	if (!Schema::hasColumn('subadmin2', 'subadmin1_code') && Schema::hasColumn('subadmin2', 'country_code')) {
		Schema::table('subadmin2', function (Blueprint $table) {
			$table->string('subadmin1_code', 20)->nullable()->after('country_code');
		});
	}
	
	DBIndex::dropIndexIfExists('subadmin2', 'active');
	DBIndex::createIndexIfNotExists('subadmin2', 'active');
	
	DBIndex::dropIndexIfExists('subadmin2', 'country_code');
	DBIndex::createIndexIfNotExists('subadmin2', 'country_code');
	
	DBIndex::dropIndexIfExists('subadmin2', 'subadmin1_code');
	DBIndex::createIndexIfNotExists('subadmin2', 'subadmin1_code');
	
	DB::table('subadmin2')->update([
		'country_code'   => DB::raw('SUBSTRING(code, 1, 2)'),
		'subadmin1_code' => DB::raw('SUBSTRING_INDEX(code, \'.\', 2)'),
	]);
	
	// users
	if (Schema::hasColumn('users', 'activation_token') && !Schema::hasColumn('users', 'email_token')) {
		Schema::table('users', function (Blueprint $table) {
			$table->renameColumn('activation_token', 'email_token');
		});
	}
	if (!Schema::hasColumn('users', 'phone_token') && Schema::hasColumn('users', 'email_token')) {
		Schema::table('users', function (Blueprint $table) {
			$table->string('phone_token', 32)->nullable()->after('email_token');
		});
	}
	if (Schema::hasColumn('users', 'verified_email')) {
		Schema::table('users', function (Blueprint $table) {
			$table->boolean('verified_email')->unsigned()->nullable()->default(1)->change();
		});
	}
	if (!Schema::hasColumn('users', 'verified_email')) {
		Schema::table('users', function (Blueprint $table) {
			$table->boolean('verified_email')->unsigned()->nullable()->default(1)->after('email_token');
		});
	}
	if (!Schema::hasColumn('users', 'verified_phone') && Schema::hasColumn('users', 'verified_email')) {
		Schema::table('users', function (Blueprint $table) {
			$table->boolean('verified_phone')->unsigned()->nullable()->default(1)->after('verified_email');
		});
	}
	if (!Schema::hasColumn('users', 'username') && Schema::hasColumn('users', 'phone_hidden')) {
		Schema::table('users', function (Blueprint $table) {
			$table->string('username', 100)->nullable()->after('phone_hidden');
		});
	}
	if (Schema::hasColumn('users', 'phone_hidden')) {
		Schema::table('users', function (Blueprint $table) {
			$table->boolean('phone_hidden')->unsigned()->nullable()->default(0)->change();
		});
	}
	if (Schema::hasColumn('users', 'is_admin')) {
		Schema::table('users', function (Blueprint $table) {
			$table->boolean('is_admin')->unsigned()->nullable()->default(0)->change();
		});
	}
	if (Schema::hasColumn('users', 'disable_comments')) {
		Schema::table('users', function (Blueprint $table) {
			$table->boolean('disable_comments')->unsigned()->nullable()->default(0)->change();
		});
	}
	if (Schema::hasColumn('users', 'receive_newsletter')) {
		Schema::table('users', function (Blueprint $table) {
			$table->boolean('receive_newsletter')->unsigned()->nullable()->default(1)->change();
		});
	}
	if (Schema::hasColumn('users', 'receive_advice')) {
		Schema::table('users', function (Blueprint $table) {
			$table->boolean('receive_advice')->unsigned()->nullable()->default(1)->change();
		});
	}
	if (Schema::hasColumn('users', 'blocked')) {
		Schema::table('users', function (Blueprint $table) {
			$table->boolean('blocked')->unsigned()->nullable()->default(0)->change();
		});
	}
	if (Schema::hasColumn('users', 'closed')) {
		Schema::table('users', function (Blueprint $table) {
			$table->boolean('closed')->unsigned()->nullable()->default(0)->change();
		});
	}
	DBIndex::dropIndexIfExists('users', 'username');
	DBIndex::createIndexIfNotExists('users', 'username');
	
	DBIndex::dropIndexIfExists('users', 'phone');
	DBIndex::createIndexIfNotExists('users', 'phone');
	
	DBIndex::dropIndexIfExists('users', 'email');
	DBIndex::createIndexIfNotExists('users', 'email');
	
	if (Schema::hasColumn('users', 'verified_phone')) {
		DB::table('users')->update(['verified_phone' => 1]);
	}
	
	// user_type
	if (Schema::hasTable('user_type') && !Schema::hasTable('user_types')) {
		Schema::rename('user_type', 'user_types');
	}
	if (Schema::hasTable('user_types')) {
		if (Schema::hasColumn('user_types', 'id') && Schema::hasColumn('user_types', 'active')) {
			Schema::table('user_types', function (Blueprint $table) {
				$table->tinyInteger('id')->unsigned()->change();
				$table->boolean('active')->nullable()->unsigned()->default(1)->change();
			});
		}
		DBIndex::dropIndexIfExists('user_types', 'active');
		DBIndex::createIndexIfNotExists('user_types', 'active');
	}
	
	// home_sections
	Schema::dropIfExists('home_sections');
	Schema::create('home_sections', function (Blueprint $table) {
		$table->increments('id')->unsigned();
		$table->string('name', 100);
		$table->string('method', 191)->default('');
		$table->text('options')->nullable();
		$table->string('view', 200);
		$table->integer('parent_id')->unsigned()->nullable();
		$table->integer('lft')->unsigned()->nullable();
		$table->integer('rgt')->unsigned()->nullable();
		$table->integer('depth')->unsigned()->nullable();
		$table->boolean('active')->unsigned()->default(0);
		$table->timestamps();
		
		$table->index('active');
	});
	
	// Inserting data into the table
	$allData = [
		[
			'name'      => 'Locations & SVG Map',
			'method'    => 'getLocations',
			'options'   => '{"max_items":"14","enable_map":"1","map_background_color":null,"map_border":null,"map_hover_border":null,"map_border_width":null,"map_color":null,"map_hover":null,"map_width":"300px","map_height":"300px","cache_expiration":null}',
			'view'      => 'home.inc.locations',
			'parent_id' => 0,
			'lft'       => 2,
			'rgt'       => 3,
			'depth'     => 1,
			'active'    => 1,
		],
		[
			'name'      => 'Premium Listings',
			'method'    => 'getPremiumListings',
			'options'   => '{"max_items":"20","autoplay":"1","autoplay_timeout":null,"cache_expiration":null}',
			'view'      => 'home.inc.featured',
			'parent_id' => 0,
			'lft'       => 4,
			'rgt'       => 5,
			'depth'     => 1,
			'active'    => 1,
		],
		[
			'name'      => 'Latest Listings',
			'method'    => 'getLatestListings',
			'options'   => '{"max_items":"4","show_view_more_btn":"1","cache_expiration":null}',
			'view'      => 'home.inc.latest',
			'parent_id' => 0,
			'lft'       => 8,
			'rgt'       => 9,
			'depth'     => 1,
			'active'    => 1,
		],
		[
			'name'      => 'Categories',
			'method'    => 'getCategories',
			'options'   => '{"cache_expiration":null}',
			'view'      => 'home.inc.categories',
			'parent_id' => 0,
			'lft'       => 6,
			'rgt'       => 7,
			'depth'     => 1,
			'active'    => 1,
		],
		[
			'name'      => 'Mini stats',
			'method'    => 'getStats',
			'options'   => null,
			'view'      => 'home.inc.stats',
			'parent_id' => 0,
			'lft'       => 10,
			'rgt'       => 11,
			'depth'     => 1,
			'active'    => 1,
		],
		[
			'name'      => 'Bottom advertising',
			'method'    => 'getBottomAdvertising',
			'options'   => null,
			'view'      => 'layouts.inc.advertising.bottom',
			'parent_id' => 0,
			'lft'       => 12,
			'rgt'       => 13,
			'depth'     => 1,
			'active'    => 0,
		],
	];
	$tableName = 'home_sections';
	if (Schema::hasTable($tableName)) {
		foreach ($allData as $item) {
			$method = $item['method'] ?? '';
			
			$homeSection = DB::table($tableName)->where('method', $method)->first();
			if (empty($homeSection)) {
				DB::table($tableName)->insert($item);
			}
		}
	}
	
	// meta_tags
	Schema::dropIfExists('meta_tags');
	Schema::create('meta_tags', function (Blueprint $table) {
		$table->increments('id');
		$table->string('translation_lang', 10);
		$table->integer('translation_of')->unsigned();
		$table->string('page', 50)->nullable();
		$table->string('title', 200)->default('');
		$table->string('description', 255)->default('');
		$table->string('keywords', 255)->default('');
		$table->boolean('active')->unsigned()->default(1);
		$table->timestamps();
		
		$table->index('translation_lang');
		$table->index('translation_of');
		$table->index('active');
	});
	
	// Inserting data into the table
	$allData = [
		[
			'id'               => 1,
			'translation_lang' => 'en',
			'translation_of'   => 1,
			'page'             => 'home',
			'title'            => '{app.name} - Hello, World!',
			'description'      => 'Sell and Buy products and services on {app.name} in Minutes {country.name}. Free ads in {country.name}. Looking for a product or service - {country.name}',
			'keywords'         => '{app.name}, {country.name}, free ads, classified, ads, script, app, premium ads',
			'active'           => 1,
		],
		[
			'id'               => 2,
			'translation_lang' => 'en',
			'translation_of'   => 2,
			'page'             => 'register',
			'title'            => 'Sign Up - {app.name}',
			'description'      => 'Sign Up on {app.name}',
			'keywords'         => '{app.name}, {country.name}, free ads, classified, ads, script, app, premium ads',
			'active'           => 1,
		],
		[
			'id'               => 3,
			'translation_lang' => 'en',
			'translation_of'   => 3,
			'page'             => 'login',
			'title'            => 'Login - {app.name}',
			'description'      => 'Log in to {app.name}',
			'keywords'         => '{app.name}, {country.name}, free ads, classified, ads, script, app, premium ads',
			'active'           => 1,
		],
		[
			'id'               => 4,
			'translation_lang' => 'en',
			'translation_of'   => 4,
			'page'             => 'create',
			'title'            => 'Post Free Ads',
			'description'      => 'Post Free Ads - {country.name}.',
			'keywords'         => '{app.name}, {country.name}, free ads, classified, ads, script, app, premium ads',
			'active'           => 1,
		],
		[
			'id'               => 5,
			'translation_lang' => 'en',
			'translation_of'   => 5,
			'page'             => 'countries',
			'title'            => 'Free Local Classified Ads in the World',
			'description'      => 'Welcome to {app.name} : 100% Free Ads Classified. Sell and buy near you. Simple, fast and efficient.',
			'keywords'         => '{app.name}, {country.name}, free ads, classified, ads, script, app, premium ads',
			'active'           => 1,
		],
		[
			'id'               => 6,
			'translation_lang' => 'en',
			'translation_of'   => 6,
			'page'             => 'contact',
			'title'            => 'Contact Us - {app.name}',
			'description'      => 'Contact Us - {app.name}',
			'keywords'         => '{app.name}, {country}, free ads, classified, ads, script, app, premium ads',
			'active'           => 1,
		],
		[
			'id'               => 7,
			'translation_lang' => 'en',
			'translation_of'   => 7,
			'page'             => 'sitemap',
			'title'            => 'Sitemap {app.name} - {country}',
			'description'      => 'Sitemap {app.name} - {country}. 100% Free Ads Classified',
			'keywords'         => '{app.name}, {country}, free ads, classified, ads, script, app, premium ads',
			'active'           => 1,
		],
		[
			'id'               => 8,
			'translation_lang' => 'fr',
			'translation_of'   => 1,
			'page'             => 'home',
			'title'            => '{app.name} - CMS d\'annonces classées et géolocalisées',
			'description'      => 'Vendre et acheter des produits et services en quelques minutes sur {app.name} {country}. Petites annonces - {country}. Recherchez un produit ou un service - {country}',
			'keywords'         => '{app.name}, {country}, annonces, classées, gratuites, script, app, annonces premium',
			'active'           => 1,
		],
		[
			'id'               => 9,
			'translation_lang' => 'es',
			'translation_of'   => 1,
			'page'             => 'home',
			'title'            => '{app.name} - Hello, World!',
			'description'      => 'Sell and Buy products and services on {app.name} in Minutes {country}. Free ads in {country}. Looking for a product or service - {country}',
			'keywords'         => '{app.name}, {country}, free ads, classified, ads, script, app, premium ads',
			'active'           => 1,
		],
		[
			'id'               => 10,
			'translation_lang' => 'fr',
			'translation_of'   => 2,
			'page'             => 'register',
			'title'            => 'S\'inscrire - {app.name}',
			'description'      => 'S\'inscrire sur {app.name}',
			'keywords'         => '{app.name}, {country}, free ads, classified, ads, script, app, premium ads',
			'active'           => 1,
		],
		[
			'id'               => 11,
			'translation_lang' => 'es',
			'translation_of'   => 2,
			'page'             => 'register',
			'title'            => 'Sign Up - {app.name}',
			'description'      => 'Sign Up on {app.name}',
			'keywords'         => '{app.name}, {country}, free ads, classified, ads, script, app, premium ads',
			'active'           => 1,
		],
		[
			'id'               => 12,
			'translation_lang' => 'fr',
			'translation_of'   => 3,
			'page'             => 'login',
			'title'            => 'S\'identifier - {app.name}',
			'description'      => 'S\'identifier sur {app.name}',
			'keywords'         => '{app.name}, {country}, free ads, classified, ads, script, app, premium ads',
			'active'           => 1,
		],
		[
			'id'               => 13,
			'translation_lang' => 'es',
			'translation_of'   => 3,
			'page'             => 'login',
			'title'            => 'Login - {app.name}',
			'description'      => 'Log in to {app.name}',
			'keywords'         => '{app.name}, {country}, free ads, classified, ads, script, app, premium ads',
			'active'           => 1,
		],
		[
			'id'               => 14,
			'translation_lang' => 'fr',
			'translation_of'   => 4,
			'page'             => 'create',
			'title'            => 'Publiez une annonce gratuite',
			'description'      => 'Publiez une annonce gratuite - {country}.',
			'keywords'         => '{app.name}, {country}, free ads, classified, ads, script, app, premium ads',
			'active'           => 1,
		],
		[
			'id'               => 15,
			'translation_lang' => 'es',
			'translation_of'   => 4,
			'page'             => 'create',
			'title'            => 'Post a Free Ads',
			'description'      => 'Post a Free Ads - {country}.',
			'keywords'         => '{app.name}, {country}, free ads, classified, ads, script, app, premium ads',
			'active'           => 1,
		],
		[
			'id'               => 16,
			'translation_lang' => 'fr',
			'translation_of'   => 5,
			'page'             => 'countries',
			'title'            => 'Petites annonces classées dans le monde',
			'description'      => 'Bienvenue sur {app.name} : Site de petites annonces 100% gratuit. Vendez et achetez près de chez vous. Simple, rapide et efficace.',
			'keywords'         => '{app.name}, {country}, free ads, classified, ads, script, app, premium ads',
			'active'           => 1,
		],
		[
			'id'               => 17,
			'translation_lang' => 'es',
			'translation_of'   => 5,
			'page'             => 'countries',
			'title'            => 'Free Local Classified Ads in the World',
			'description'      => 'Welcome to {app.name} : 100% Free Ads Classified. Sell and buy near you. Simple, fast and efficient.',
			'keywords'         => '{app.name}, {country}, free ads, classified, ads, script, app, premium ads',
			'active'           => 1,
		],
		[
			'id'               => 18,
			'translation_lang' => 'fr',
			'translation_of'   => 6,
			'page'             => 'contact',
			'title'            => 'Nous contacter - {app.name}',
			'description'      => 'Nous contacter - {app.name}',
			'keywords'         => '{app.name}, {country}, free ads, classified, ads, script, app, premium ads',
			'active'           => 1,
		],
		[
			'id'               => 19,
			'translation_lang' => 'es',
			'translation_of'   => 6,
			'page'             => 'contact',
			'title'            => 'Contact Us - {app.name}',
			'description'      => 'Contact Us - {app.name}',
			'keywords'         => '{app.name}, {country}, free ads, classified, ads, script, app, premium ads',
			'active'           => 1,
		],
		[
			'id'               => 20,
			'translation_lang' => 'fr',
			'translation_of'   => 7,
			'page'             => 'sitemap',
			'title'            => 'Plan du site {app.name} - {country}',
			'description'      => 'Plan du site {app.name} - {country}. Site de petites annonces 100% gratuit dans le Monde. Vendez et achetez près de chez vous. Simple, rapide et efficace.',
			'keywords'         => '{app.name}, {country}, free ads, classified, ads, script, app, premium ads',
			'active'           => 1,
		],
		[
			'id'               => 21,
			'translation_lang' => 'es',
			'translation_of'   => 7,
			'page'             => 'sitemap',
			'title'            => 'Sitemap {app.name} - {country}',
			'description'      => 'Sitemap {app.name} - {country}. 100% Free Ads Classified',
			'keywords'         => '{app.name}, {country}, free ads, classified, ads, script, app, premium ads',
			'active'           => 1,
		],
		[
			'id'               => 22,
			'translation_lang' => 'en',
			'translation_of'   => 22,
			'page'             => 'password',
			'title'            => 'Lost your password? - {app.name}',
			'description'      => 'Lost your password? - {app.name}',
			'keywords'         => '{app.name}, {country}, free ads, classified, ads, script, app, premium ads',
			'active'           => 1,
		],
		[
			'id'               => 23,
			'translation_lang' => 'fr',
			'translation_of'   => 22,
			'page'             => 'password',
			'title'            => 'Mot de passe oublié? - {app.name}',
			'description'      => 'Mot de passe oublié? - {app.name}',
			'keywords'         => '{app.name}, {country}, free ads, classified, ads, script, app, premium ads',
			'active'           => 1,
		],
		[
			'id'               => 24,
			'translation_lang' => 'es',
			'translation_of'   => 22,
			'page'             => 'password',
			'title'            => '¿Perdiste tu contraseña? - {app.name}',
			'description'      => '¿Perdiste tu contraseña? - {app.name}',
			'keywords'         => '{app.name}, {country}, free ads, classified, ads, script, app, premium ads',
			'active'           => 1,
		],
	];
	
	foreach ($allData as $item) {
		$page = $item['page'] ?? '';
		$translationLang = $item['translation_lang'] ?? '';
		
		$metaTag = \App\Models\MetaTag::where('page', $page)->where('translation_lang', $translationLang)->first();
		if (empty($metaTag)) {
			DB::table('meta_tags')->insert($item);
		}
	}
	
	// fields
	if (!Schema::hasTable('fields')) {
		Schema::create('fields', function (Blueprint $table) {
			$table->increments('id');
			$table->enum('belongs_to', ['post', 'user']);
			$table->string('translation_lang', 10)->nullable();
			$table->integer('translation_of')->unsigned()->nullable();
			$table->string('name', 100)->nullable();
			$table->enum('type', ['text', 'textarea', 'checkbox', 'checkbox_multiple', 'select', 'radio', 'file'])->default('text');
			$table->integer('max')->unsigned()->default(255);
			$table->string('default', 255)->nullable();
			$table->boolean('required')->nullable();
			$table->string('help', 255)->nullable();
			$table->boolean('active')->nullable();
			$table->timestamps();
			
			$table->index('belongs_to');
			$table->index('translation_lang');
			$table->index('translation_of');
			$table->index('active');
		});
	}
	
	// fields_options
	if (!Schema::hasTable('fields_options')) {
		Schema::create('fields_options', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('field_id')->unsigned()->nullable();
			$table->string('translation_lang', 10)->nullable();
			$table->integer('translation_of')->unsigned()->nullable();
			$table->text('value');
			$table->integer('parent_id')->unsigned()->nullable();
			$table->integer('lft')->unsigned()->nullable();
			$table->integer('rgt')->unsigned()->nullable();
			$table->integer('depth')->unsigned()->nullable();
			$table->timestamps();
			
			$table->index('field_id');
			$table->index('translation_lang');
			$table->index('translation_of');
		});
	}
	
	// category_field
	if (!Schema::hasTable('category_field')) {
		Schema::create('category_field', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('category_id')->unsigned()->nullable();
			$table->integer('field_id')->unsigned()->nullable();
			$table->integer('parent_id')->unsigned()->nullable();
			$table->integer('lft')->unsigned()->nullable();
			$table->integer('rgt')->unsigned()->nullable();
			$table->integer('depth')->unsigned()->nullable();
			$table->timestamps();
			
			$table->unique(['category_id', 'field_id']);
		});
	}
	
	// post_values
	if (!Schema::hasTable('post_values')) {
		Schema::create('post_values', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('post_id')->unsigned()->nullable();
			$table->integer('field_id')->unsigned()->nullable();
			$table->integer('option_id')->unsigned()->nullable();
			$table->text('value');
			$table->timestamps();
			
			$table->index('post_id');
			$table->index('field_id');
			$table->index('option_id');
		});
	}
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
