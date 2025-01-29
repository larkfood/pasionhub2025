<?php

use App\Exceptions\Custom\CustomException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// ===| DATABASE |===
try {
	
	// packs
	if (Schema::hasTable('packs')) {
		if (!Schema::hasColumn('packs', 'short_name')) {
			Schema::table('packs', function (Blueprint $table) {
				$table->string('short_name', 100)->nullable()->comment('In country language')->after('name');
			});
		}
		if (!Schema::hasColumn('packs', 'ribbon')) {
			Schema::table('packs', function (Blueprint $table) {
				$table->enum('ribbon', ['red', 'orange', 'green'])->nullable()->after('short_name');
			});
		}
		if (!Schema::hasColumn('packs', 'has_badge')) {
			Schema::table('packs', function (Blueprint $table) {
				$table->boolean('has_badge')->unsigned()->nullable()->default(0)->after('ribbon');
			});
		}
		
		if (Schema::hasColumn('packs', 'description')) {
			Schema::table('packs', function (Blueprint $table) {
				$table->string('description', 255)->nullable()->comment('In country language')->after('has_badge')->change();
			});
		}
		
		if (
			Schema::hasColumn('packs', 'translation_of')
			&& Schema::hasColumn('packs', 'short_name')
			&& Schema::hasColumn('packs', 'ribbon')
			&& Schema::hasColumn('packs', 'has_badge')
		) {
			$affected = DB::table('packs')->where('translation_of', 1)->update([
				'short_name' => 'FREE',
				'ribbon'     => null,
				'has_badge'  => 0,
			]);
			$affected = DB::table('packs')->where('translation_of', 2)->update([
				'short_name' => 'Urgent',
				'ribbon'     => 'red',
				'has_badge'  => 0,
			]);
			$affected = DB::table('packs')->where('translation_of', 3)->update([
				'short_name' => 'Premium',
				'ribbon'     => 'orange',
				'has_badge'  => 1,
			]);
			$affected = DB::table('packs')->where('translation_of', 4)->update([
				'short_name' => 'Premium+',
				'ribbon'     => 'green',
				'has_badge'  => 1,
			]);
		}
	}
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
