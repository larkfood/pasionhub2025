<?php

use App\Exceptions\Custom\CustomException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

// ===| FILES |===
try {
	
	File::delete(app_path('Http/Controllers/Admin/SubCategoryController.php'));
	File::delete(app_path('Http/Controllers/Post/CreateOrEdit/Traits/PaymentTrait.php'));
	File::delete(resource_path('views/post/createOrEdit/inc/form-plugins.blade.php'));
	File::delete(resource_path('views/search/serp.blade.php'));
	File::delete(resource_path('views/search/inc/fields.blade.php'));
	
} catch (\Throwable $e) {
}

// ===| DATABASE |===
try {
	
	// settings
	DB::table('settings')->where('key', '=', 'single')->update([
		'name'        => 'Ads (Form & Single Page)',
		'description' => 'Ads (Form & Single Page) Options',
	]);
	
	// post_types
	DB::table('post_types')->where('name', 'LIKE', 'Private')->update(['name' => 'Private individual']);
	
	// posts
	if (!Schema::hasColumn('posts', 'is_permanent') && Schema::hasColumn('posts', 'verified_phone')) {
		Schema::table('posts', function (Blueprint $table) {
			$table->boolean('is_permanent')->nullable()->default(false)->after('verified_phone');
			$table->index('is_permanent');
		});
	}
	
	// categories
	if (!Schema::hasColumn('categories', 'is_for_permanent') && Schema::hasColumn('categories', 'type')) {
		Schema::table('categories', function (Blueprint $table) {
			$table->boolean('is_for_permanent')->nullable()->default(false)->after('type');
		});
	}
	
	// payments
	if (!Schema::hasColumn('payments', 'amount') && Schema::hasColumn('payments', 'transaction_id')) {
		Schema::table('payments', function (Blueprint $table) {
			$table->decimal('amount', 10, 2)->default(0.00)->after('transaction_id');
		});
	}
	if (Schema::hasColumn('payments', 'amount')) {
		$payments = \App\Models\Payment::with(['package']);
		if ($payments->count() > 0) {
			foreach ($payments->cursor() as $payment) {
				if (isset($payment->package) && !empty($payment->package)) {
					$payment->amount = $payment->package->price;
				} else {
					$payment->amount = 0;
				}
				$payment->save();
			}
		}
	}
	
	// packages
	Schema::table('packages', function (Blueprint $table) {
		$table->text('description')->nullable()->change();
	});
	if (!Schema::hasColumn('packages', 'promotion_time') && Schema::hasColumn('packages', 'currency_code')) {
		Schema::table('packages', function (Blueprint $table) {
			$table->integer('promotion_time')->nullable()->default(30)->comment('In days')->after('currency_code');
		});
	}
	if (!Schema::hasColumn('packages', 'pictures_limit') && Schema::hasColumn('packages', 'duration')) {
		Schema::table('packages', function (Blueprint $table) {
			$table->integer('pictures_limit')->nullable()->default(0)->after('duration');
		});
	}
	if (!Schema::hasColumn('packages', 'facebook_ads') && Schema::hasColumn('packages', 'description')) {
		Schema::table('packages', function (Blueprint $table) {
			$table->boolean('facebook_ads')->nullable()->default(false)->after('description');
		});
	}
	if (!Schema::hasColumn('packages', 'google_ads') && Schema::hasColumn('packages', 'facebook_ads')) {
		Schema::table('packages', function (Blueprint $table) {
			$table->boolean('google_ads')->nullable()->default(false)->after('facebook_ads');
		});
	}
	if (!Schema::hasColumn('packages', 'twitter_ads') && Schema::hasColumn('packages', 'google_ads')) {
		Schema::table('packages', function (Blueprint $table) {
			$table->boolean('twitter_ads')->nullable()->default(false)->after('google_ads');
		});
	}
	if (!Schema::hasColumn('packages', 'recommended') && Schema::hasColumn('packages', 'twitter_ads')) {
		Schema::table('packages', function (Blueprint $table) {
			$table->boolean('recommended')->nullable()->default(false)->after('twitter_ads');
		});
	}
	
	// meta_tags
	if (
		Schema::hasColumn('languages', 'abbr')
		&& Schema::hasColumn('meta_tags', 'translation_lang')
		&& Schema::hasColumn('meta_tags', 'translation_of')
	) {
		$languages = \App\Models\Language::query()->get();
		if ($languages->count() > 0) {
			DB::table('meta_tags')->where('page', 'pricing')->delete();
			
			$translationOf = null;
			foreach ($languages as $lang) {
				$metaTag = [
					'page'        => 'pricing',
					'title'       => 'Pricing - {app.name}',
					'description' => 'Pricing - {app.name}',
					'keywords'    => '{app.name}, {country}, pricing, free ads, classified, ads, script, app, premium ads',
					'active'      => 1,
				];
				if (Schema::hasColumn('meta_tags', 'translation_lang') && Schema::hasColumn('meta_tags', 'translation_of')) {
					$metaTag = [
						'translation_lang' => $lang->abbr,
						'translation_of'   => 0,
						'page'             => 'pricing',
						'title'            => 'Pricing - {app.name}',
						'description'      => 'Pricing - {app.name}',
						'keywords'         => '{app.name}, {country}, pricing, free ads, classified, ads, script, app, premium ads',
						'active'           => 1,
					];
				}
				$metaTagId = DB::table('meta_tags')->insertGetId($metaTag);
				if ($lang->abbr == config('appLang.abbr')) {
					$translationOf = $metaTagId;
				}
			}
			
			if (!empty($translationOf)) {
				if (Schema::hasColumn('meta_tags', 'translation_lang') && Schema::hasColumn('meta_tags', 'translation_of')) {
					$affected = DB::table('meta_tags')
						->where('page', 'pricing')
						->update(['translation_of' => $translationOf]);
				}
			}
		}
	}
	
	// categories
	$params = [
		'adjacentTable' => 'categories',
		'nestedTable'   => 'categories',
	];
	$transformer = new \App\Helpers\Categories\AdjacentToNested($params);
	$transformer->getAndSetAdjacentItemsIds();
	$transformer->convertChildrenRecursively(0);
	$transformer->setNodesDepth();
	
	// Create the Nested Set indexes ('lft', 'rgt' & 'depth')
	$transformer->createNestedSetIndexes();
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
