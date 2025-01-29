<?php

use App\Exceptions\Custom\CustomException;
use App\Helpers\DBTool\DBIndex;
use App\Helpers\DotenvEditor;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

// ===| FILES |===
try {
	
	File::delete(app_path('Helpers/Payment.php'));
	File::delete(app_path('Helpers/Referrer.php'));
	File::delete(app_path('Helpers/RemoveFromString.php'));
	File::delete(app_path('Helpers/UrlGen.php'));
	File::deleteDirectory(app_path('Helpers/Lang/'));
	File::deleteDirectory(app_path('Helpers/Localization/'));
	File::deleteDirectory(app_path('Helpers/Payment/'));
	File::deleteDirectory(app_path('Helpers/Search/'));
	File::deleteDirectory(app_path('Helpers/UrlGen/'));
	
	File::delete(database_path('migrations/07_00_create_home_sections_table.php'));
	File::delete(database_path('seeders/HomeSectionSeeder.php'));
	File::delete(app_path('Console/Commands/SiteInfoCommand.php'));
	File::delete(app_path('Models/HomeSection.php'));
	File::delete(app_path('Models/Traits/HomeSectionTrait.php'));
	File::delete(app_path('Http/Controllers/Web/Admin/HomeSectionController.php'));
	File::delete(app_path('Http/Resources/HomeSectionResource.php'));
	File::delete(app_path('Observers/HomeSectionObserver.php'));
	File::delete(app_path('Http/Controllers/Web/Admin/Traits/InlineRequest/HomeSectionTrait.php'));
	File::deleteDirectory(app_path('Http/Controllers/Api/HomeSection/'));
	File::deleteDirectory(app_path('Models/HomeSection/'));
	File::deleteDirectory(resource_path('views/home/'));
	File::deleteDirectory(resource_path('views/admin/js/home-section/'));
	
	File::delete(base_path('packages/larapen/texttoimage/src/Libraries/font/LICENSE'));
	File::deleteDirectory(base_path('packages/larapen/texttoimage/src/Libraries/Intervention/'));
	File::delete(app_path('Http/Controllers/Web/Install/Traits/Install/PhpTrait.php'));
	
	$domainMappingDir = base_path('extras/plugins/domainmapping/');
	if (File::exists($domainMappingDir) && File::isDirectory($domainMappingDir)) {
		File::delete(base_path('extras/plugins/domainmapping/app/Models/DomainHomeSection.php'));
		File::delete(base_path('extras/plugins/domainmapping/app/Http/Controllers/Web/Admin/DomainHomeSectionController.php'));
		File::delete(base_path('extras/plugins/domainmapping/app/Observers/DomainHomeSectionObserver.php'));
		File::deleteDirectory(base_path('extras/plugins/domainmapping/app/Models/HomeSection/'));
	}
	
	// .ENV
	if (DotenvEditor::keyExists('QUEUE_CONNECTION')) {
		DotenvEditor::setKey('QUEUE_CONNECTION', 'sync');
		DotenvEditor::save();
	}
	
} catch (\Exception $e) {
}

// ===| DATABASE |===
try {
	
	$sectionsKeyValues = [
		'getSearchForm'        => 'search_form',
		'getPremiumListings'   => 'premium_listings',
		'getLatestListings'    => 'latest_listings',
		'getCategories'        => 'categories',
		'getLocations'         => 'locations',
		'getCompanies'         => 'companies',
		'getStats'             => 'stats',
		'getTextArea'          => 'text_area',
		'getTopAdvertising'    => 'top_ad',
		'getBottomAdvertising' => 'bottom_ad',
	];
	
	// Check if indexes exist, and drop them
	if (Schema::hasTable('home_sections')) {
		DBIndex::dropIndexIfExists('home_sections', 'method', 'unique');
		DBIndex::dropIndexIfExists('home_sections', 'lft');
		DBIndex::dropIndexIfExists('home_sections', 'rgt');
		DBIndex::dropIndexIfExists('home_sections', 'active');
	}
	
	// sections
	// Rename the 'home_sections' table to 'sections'
	if (
		Schema::hasTable('home_sections')
		&& !Schema::hasTable('sections')
	) {
		Schema::rename('home_sections', 'sections');
	}
	
	// sections
	if (Schema::hasTable('sections')) {
		// belongs_to
		if (!Schema::hasColumn('sections', 'belongs_to')) {
			Schema::table('sections', function (Blueprint $table) {
				$table->string('belongs_to', 100)->default('home')->after('id');
			});
		}
		
		// description
		if (!Schema::hasColumn('sections', 'description')) {
			Schema::table('sections', function (Blueprint $table) {
				$table->string('description', 500)->nullable()->after('value');
			});
		}
		
		// key
		if (
			Schema::hasColumn('sections', 'method')
			&& !Schema::hasColumn('sections', 'key')
		) {
			Schema::table('sections', function (Blueprint $table) {
				$table->renameColumn('method', 'key');
			});
		}
		if (Schema::hasColumn('sections', 'key')) {
			Schema::table('sections', function (Blueprint $table) {
				$table->string('key', 100)->change();
			});
		}
		
		// created_at, updated_at
		if (
			!Schema::hasColumn('sections', 'created_at')
			&& !Schema::hasColumn('sections', 'updated_at')
		) {
			Schema::table('sections', function (Blueprint $table) {
				$table->timestamps();
			});
		}
		
		// view
		if (Schema::hasColumn('sections', 'view')) {
			Schema::table('sections', function (Blueprint $table) {
				$table->dropColumn('view');
			});
		}
		
		// Update the 'sections' table entries
		if (Schema::hasColumn('sections', 'key')) {
			if (!empty($sectionsKeyValues)) {
				foreach ($sectionsKeyValues as $oldValue => $newValue) {
					DB::table('sections')->where('key', $oldValue)->update(['key' => $newValue]);
				}
			}
		}
		
		// Create the new indexes
		DBIndex::createIndexIfNotExists('sections', ['belongs_to', 'key'], 'unique');
		DBIndex::createIndexIfNotExists('sections', 'belongs_to');
		DBIndex::createIndexIfNotExists('sections', 'key');
		DBIndex::createIndexIfNotExists('sections', 'lft');
		DBIndex::createIndexIfNotExists('sections', 'rgt');
		DBIndex::createIndexIfNotExists('sections', 'active');
	}
	
	// migrations
	$updatedMigrations = [
		'create_home_sections_table' => 'create_sections_table',
	];
	if (!empty($updatedMigrations)) {
		foreach ($updatedMigrations as $oldValue => $newValue) {
			DB::table('migrations')
				->where('migration', 'LIKE', '%' . $oldValue . '%')
				->update([
					'migration' => DB::raw("REPLACE(`migration`, '$oldValue', '$newValue')"),
				]);
		}
	}
	
	// Domain Mapping
	// Check if indexes exist, and drop them
	if (Schema::hasTable('domain_home_sections')) {
		DBIndex::dropIndexIfExists('domain_home_sections', 'method');
		DBIndex::dropIndexIfExists('domain_home_sections', 'lft');
		DBIndex::dropIndexIfExists('domain_home_sections', 'rgt');
		DBIndex::dropIndexIfExists('domain_home_sections', 'active');
	}
	
	// domain_sections
	// Rename the 'domain_home_sections' table to 'domain_sections'
	if (
		Schema::hasTable('domain_home_sections')
		&& !Schema::hasTable('domain_sections')
	) {
		Schema::rename('domain_home_sections', 'domain_sections');
	}
	
	// domain_sections
	if (Schema::hasTable('domain_sections')) {
		// belongs_to
		if (!Schema::hasColumn('domain_sections', 'belongs_to')) {
			Schema::table('domain_sections', function (Blueprint $table) {
				$table->string('belongs_to', 100)->default('home')->after('id');
			});
		}
		
		// description
		if (!Schema::hasColumn('domain_sections', 'description')) {
			Schema::table('domain_sections', function (Blueprint $table) {
				$table->string('description', 500)->nullable()->after('value');
			});
		}
		
		// key
		if (
			Schema::hasColumn('domain_sections', 'method')
			&& !Schema::hasColumn('domain_sections', 'key')
		) {
			Schema::table('domain_sections', function (Blueprint $table) {
				$table->renameColumn('method', 'key');
			});
		}
		
		// created_at, updated_at
		if (
			!Schema::hasColumn('domain_sections', 'created_at')
			&& !Schema::hasColumn('domain_sections', 'updated_at')
		) {
			Schema::table('domain_sections', function (Blueprint $table) {
				$table->timestamps();
			});
		}
		
		// view
		if (Schema::hasColumn('domain_sections', 'view')) {
			Schema::table('domain_sections', function (Blueprint $table) {
				$table->dropColumn('view');
			});
		}
		
		// Update the 'domain_sections' table entries
		if (Schema::hasColumn('domain_sections', 'key')) {
			if (!empty($sectionsKeyValues)) {
				foreach ($sectionsKeyValues as $oldValue => $newValue) {
					DB::table('domain_sections')
						->where('key', 'LIKE', '%' . $oldValue . '%')
						->update([
							'key' => DB::raw("REPLACE(`key`, '$oldValue', '$newValue')"),
						]);
				}
			}
		}
		
		// Create the new indexes
		DBIndex::createIndexIfNotExists('domain_sections', ['country_code', 'belongs_to', 'key'], 'unique');
		DBIndex::createIndexIfNotExists('domain_sections', 'country_code');
		DBIndex::createIndexIfNotExists('domain_sections', 'belongs_to');
		DBIndex::createIndexIfNotExists('domain_sections', 'key');
		DBIndex::createIndexIfNotExists('domain_sections', 'lft');
		DBIndex::createIndexIfNotExists('domain_sections', 'rgt');
		DBIndex::createIndexIfNotExists('domain_sections', 'active');
	}
	
	// Some columns updated
	if (Schema::hasTable('pages')) {
		$tableName = 'pages';
		Schema::table($tableName, function (Blueprint $table) use ($tableName) {
			if (Schema::hasColumn($tableName, 'slug')) {
				$table->string('slug', 191)->nullable()->change();
			}
		});
	}
	if (Schema::hasTable('posts')) {
		$tableName = 'posts';
		Schema::table($tableName, function (Blueprint $table) use ($tableName) {
			if (Schema::hasColumn($tableName, 'email')) {
				$table->string('email', 191)->nullable()->change();
			}
			if (Schema::hasColumn($tableName, 'email_token')) {
				$table->string('email_token', 191)->nullable()->change();
			}
			if (Schema::hasColumn($tableName, 'phone_token')) {
				$table->string('phone_token', 191)->nullable()->change();
			}
			if (Schema::hasColumn($tableName, 'tmp_token')) {
				$table->string('tmp_token', 191)->nullable()->change();
			}
			if (Schema::hasColumn($tableName, 'partner')) {
				$table->string('partner', 191)->nullable()->change();
			}
		});
	}
	if (Schema::hasTable('users')) {
		$tableName = 'users';
		Schema::table($tableName, function (Blueprint $table) use ($tableName) {
			if (Schema::hasColumn($tableName, 'email')) {
				$table->string('email', 191)->nullable()->change();
			}
			if (Schema::hasColumn($tableName, 'remember_token')) {
				$table->string('remember_token', 191)->nullable()->change();
			}
			if (Schema::hasColumn($tableName, 'provider')) {
				$table->string('provider', 100)->nullable()->comment('facebook, google, twitter, linkedin, ...')->change();
			}
			if (Schema::hasColumn($tableName, 'provider_id')) {
				$table->string('provider_id', 191)->nullable()->comment('Provider User ID')->change();
			}
			if (Schema::hasColumn($tableName, 'email_token')) {
				$table->string('email_token', 191)->nullable()->change();
			}
			if (Schema::hasColumn($tableName, 'phone_token')) {
				$table->string('phone_token', 191)->nullable()->change();
			}
		});
	}
	if (Schema::hasTable('saved_search')) {
		$tableName = 'saved_search';
		Schema::table($tableName, function (Blueprint $table) use ($tableName) {
			if (Schema::hasColumn($tableName, 'keyword')) {
				$table->string('keyword', 191)->nullable()->comment('To show')->change();
			}
		});
	}
	if (Schema::hasTable('threads')) {
		$tableName = 'threads';
		Schema::table($tableName, function (Blueprint $table) use ($tableName) {
			if (Schema::hasColumn($tableName, 'subject')) {
				$table->string('subject', 191)->nullable()->change();
			}
		});
	}
	if (Schema::hasTable('threads_messages')) {
		$tableName = 'threads_messages';
		Schema::table($tableName, function (Blueprint $table) use ($tableName) {
			if (Schema::hasColumn($tableName, 'filename')) {
				$table->string('filename', 255)->nullable()->change();
			}
		});
	}
	if (Schema::hasTable('payments')) {
		$tableName = 'payments';
		Schema::table($tableName, function (Blueprint $table) use ($tableName) {
			if (Schema::hasColumn($tableName, 'payable_type')) {
				$table->string('payable_type', 191)->nullable()->comment('Post|User class name')->change();
			}
			if (Schema::hasColumn($tableName, 'transaction_id')) {
				$table->string('transaction_id', 191)->nullable()->comment('Transaction\'s ID from the Provider')->change();
			}
		});
	}
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
