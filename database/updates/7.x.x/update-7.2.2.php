<?php

use App\Exceptions\Custom\CustomException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ===| DATABASE |===
try {
	
	// packages
	if (Schema::hasColumn('packages', 'facebook_ads') && !Schema::hasColumn('packages', 'facebook_ads_duration')) {
		Schema::table('packages', function (Blueprint $table) {
			$table->renameColumn('facebook_ads', 'facebook_ads_duration');
		});
	}
	if (Schema::hasColumn('packages', 'google_ads') && !Schema::hasColumn('packages', 'google_ads_duration')) {
		Schema::table('packages', function (Blueprint $table) {
			$table->renameColumn('google_ads', 'google_ads_duration');
		});
	}
	if (Schema::hasColumn('packages', 'twitter_ads') && !Schema::hasColumn('packages', 'twitter_ads_duration')) {
		Schema::table('packages', function (Blueprint $table) {
			$table->renameColumn('twitter_ads', 'twitter_ads_duration');
		});
	}
	
	if (Schema::hasColumn('packages', 'facebook_ads_duration')) {
		Schema::table('packages', function (Blueprint $table) {
			$table->integer('facebook_ads_duration')->unsigned()->nullable()->default(0)->change();
		});
	}
	if (Schema::hasColumn('packages', 'google_ads_duration')) {
		Schema::table('packages', function (Blueprint $table) {
			$table->integer('google_ads_duration')->unsigned()->nullable()->default(0)->change();
		});
	}
	if (Schema::hasColumn('packages', 'twitter_ads_duration')) {
		Schema::table('packages', function (Blueprint $table) {
			$table->integer('twitter_ads_duration')->unsigned()->nullable()->default(0)->change();
		});
		if (!Schema::hasColumn('packages', 'linkedin_ads_duration')) {
			Schema::table('packages', function (Blueprint $table) {
				$table->integer('linkedin_ads_duration')->unsigned()->nullable()->default(0)->after('twitter_ads_duration');
			});
		}
	}
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
