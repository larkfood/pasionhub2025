<?php

use App\Exceptions\Custom\CustomException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

// ===| FILES |===
try {
	
	File::delete(app_path('Larapen/Helpers/functions.php'));
	File::delete(app_path('Larapen/Helpers/wordpress.php'));
	File::delete(app_path('Larapen/Models/Pack.php'));
	
	File::moveDirectory(public_path('vendor/adminlte/plugins/jquery/'), public_path('vendor/adminlte/plugins/jqueryNew/'));
	File::moveDirectory(public_path('vendor/adminlte/plugins/jqueryNew/'), public_path('vendor/adminlte/plugins/jQuery/'));
	
} catch (\Throwable $e) {
}

// ===| DATABASE |===
try {
	
	// languages
	if (
		!Schema::hasColumn('languages', 'russian_pluralization')
		&& Schema::hasColumn('languages', 'script')
	) {
		Schema::table('languages', function (Blueprint $table) {
			$table->boolean('russian_pluralization')->unsigned()->nullable()->default(0)->after('script');
		});
	}
	
	// currencies
	if (
		!Schema::hasColumn('currencies', 'decimal_places')
		&& Schema::hasColumn('currencies', 'in_left')
	) {
		Schema::table('currencies', function (Blueprint $table) {
			$table->integer('decimal_places')->unsigned()->nullable()->default(2)->comment('Currency Decimal Places - ISO 4217')->after('in_left');
		});
	}
	if (
		!Schema::hasColumn('currencies', 'decimal_separator')
		&& Schema::hasColumn('currencies', 'decimal_places')
	) {
		Schema::table('currencies', function (Blueprint $table) {
			$table->string('decimal_separator', 10)->nullable()->default('.')->after('decimal_places');
		});
	}
	if (
		!Schema::hasColumn('currencies', 'thousand_separator')
		&& Schema::hasColumn('currencies', 'decimal_separator')
	) {
		Schema::table('currencies', function (Blueprint $table) {
			$table->string('thousand_separator', 10)->nullable()->default(',')->after('decimal_separator');
		});
	}
	
	DB::table('currencies')->update([
		'decimal_places'     => 2,
		'decimal_separator'  => '.',
		'thousand_separator' => ',',
	]);
	
	// ads
	if (Schema::hasTable('ads')) {
		if (Schema::hasColumn('ads', 'price')) {
			Schema::table('ads', function (Blueprint $table) {
				$table->decimal('price', 10, 2)->nullable()->change();
			});
		}
	}
	
	// packs
	if (Schema::hasTable('packs')) {
		if (Schema::hasColumn('packs', 'price')) {
			Schema::table('packs', function (Blueprint $table) {
				$table->decimal('price', 10, 2)->nullable()->change();
			});
		}
	}
	
	// countries
	if (Schema::hasColumn('countries', 'phone')) {
		Schema::table('countries', function (Blueprint $table) {
			$table->string('phone', 20)->nullable()->change();
		});
	}
	
	// settings
	$setting = \App\Models\Setting::where('key', 'ads_per_page')->first();
	if (empty($setting)) {
		$data = [
			'key'         => 'ads_per_page',
			'name'        => 'Ads per page',
			'value'       => '12',
			'description' => 'Number of ads per page (> 4 and < 40)',
			'field'       => '{"name":"value","label":"Value","type":"text"}',
			'parent_id'   => 0,
			'lft'         => 18,
			'rgt'         => 19,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2017-02-08 13:51:10',
		];
		DB::table('settings')->insert($data);
	}
	$setting = \App\Models\Setting::where('key', 'decimals_superscript')->first();
	if (empty($setting)) {
		$data = [
			'key'         => 'decimals_superscript',
			'name'        => 'Decimals Superscript',
			'value'       => '0',
			'description' => 'Decimals Superscript (For Price, Salary, etc.)',
			'field'       => '{"name":"value","label":"Activation","type":"checkbox"}',
			'parent_id'   => 0,
			'lft'         => 19,
			'rgt'         => 19,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2017-02-08 13:51:10',
		];
		DB::table('settings')->insert($data);
	}
	$setting = \App\Models\Setting::where('key', 'simditor_wysiwyg')->first();
	if (empty($setting)) {
		$data = [
			'key'         => 'simditor_wysiwyg',
			'name'        => 'Simditor WYSIWYG Editor',
			'value'       => '0',
			'description' => 'Simditor WYSIWYG Editor',
			'field'       => '{"name":"value","label":"Activation","type":"checkbox"}',
			'parent_id'   => 0,
			'lft'         => 19,
			'rgt'         => 19,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2017-02-08 13:51:10',
		];
		DB::table('settings')->insert($data);
	}
	$setting = \App\Models\Setting::where('key', 'ckeditor_wysiwyg')->first();
	if (empty($setting)) {
		$data = [
			'key'         => 'ckeditor_wysiwyg',
			'name'        => 'CKEditor WYSIWYG Editor',
			'value'       => '0',
			'description' => 'CKEditor WYSIWYG Editor (For commercial use: http://ckeditor.com/pricing) - You need to disable the "Simditor WYSIWYG Editor"',
			'field'       => '{"name":"value","label":"Activation","type":"checkbox"}',
			'parent_id'   => 0,
			'lft'         => 19,
			'rgt'         => 19,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2017-02-08 13:51:10',
		];
		DB::table('settings')->insert($data);
	}
	$setting = \App\Models\Setting::where('key', 'admin_theme')->first();
	if (empty($setting)) {
		$data = [
			'key'         => 'admin_theme',
			'name'        => 'Admin Theme',
			'value'       => 'skin-blue',
			'description' => 'Admin Panel Theme',
			'field'       => '{"name":"value","label":"Value","type":"select_from_array","options":{"skin-black":"Black","skin-blue":"Blue","skin-purple":"Purple","skin-red":"Red","skin-yellow":"Yellow","skin-green":"Green","skin-blue-light":"Blue light","skin-black-light":"Black light","skin-purple-light":"Purple light","skin-green-light":"Green light","skin-red-light":"Red light","skin-yellow-light":"Yellow light"}}',
			'parent_id'   => 0,
			'lft'         => 13,
			'rgt'         => 13,
			'depth'       => 1,
			'active'      => 1,
			'created_at'  => null,
			'updated_at'  => '2017-02-12 03:53:11',
		];
		DB::table('settings')->insert($data);
	}
	
	// packs
	if (Schema::hasTable('packs') && !Schema::hasTable('packages')) {
		Schema::rename('packs', 'packages');
	}
	
	// payments
	if (Schema::hasColumn('payments', 'pack_id') && !Schema::hasColumn('payments', 'package_id')) {
		Schema::table('payments', function (Blueprint $table) {
			$table->renameColumn('pack_id', 'package_id');
		});
	}
	
	// ads
	if (Schema::hasTable('ads')) {
		if (Schema::hasColumn('ads', 'pack_id') && !Schema::hasColumn('ads', 'package_id')) {
			Schema::table('ads', function (Blueprint $table) {
				$table->renameColumn('pack_id', 'package_id');
			});
		}
	}
	
	// pages
	if (!Schema::hasTable('pages')) {
		Schema::create('pages', function (Blueprint $table) {
			$table->increments('id')->unsigned();
			$table->string('translation_lang', 10)->nullable();
			$table->integer('translation_of')->unsigned()->nullable();
			$table->integer('parent_id')->unsigned()->nullable();
			$table->enum('type', ['standard', 'terms', 'privacy']);
			$table->string('name', 100)->nullable();
			$table->string('slug', 100)->nullable();
			$table->string('title', 200)->nullable();
			$table->string('picture', 255)->nullable();
			$table->text('content')->nullable();
			$table->integer('lft')->unsigned()->nullable();
			$table->integer('rgt')->unsigned()->nullable();
			$table->integer('depth')->unsigned()->nullable();
			$table->string('name_color', 10)->nullable();
			$table->string('title_color', 10)->nullable();
			$table->boolean('active')->unsigned()->default(1);
			$table->timestamps();
			
			$table->index('translation_lang');
			$table->index('translation_of');
			$table->index('parent_id');
		});
	}
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
