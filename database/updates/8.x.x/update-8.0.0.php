<?php

use App\Exceptions\Custom\CustomException;
use App\Helpers\DBTool;
use App\Helpers\DBTool\DBIndex;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

// ===| FILES |===
try {
	
	File::delete(app_path('Helpers/Lang/Traits/LangTablesTrait.php'));
	File::delete(app_path('Models/Traits/TranslatedTrait.php'));
	File::delete(app_path('Observers/TranslatedModelObserver.php'));
	File::delete(base_path('packages/larapen/admin/src/app/Http/Controllers/Features/TranslateItem.php'));
	File::delete(base_path('packages/larapen/admin/src/app/Models/Crud.php'));
	File::delete(base_path('packages/larapen/admin/src/app/Models/LanguageFeatures.php'));
	File::delete(base_path('packages/larapen/admin/src/app/Models/Translated.php'));
	
} catch (\Throwable $e) {
}

// ===| DATABASE |===
try {
	
	// languages
	$languages = DB::table('languages')->get();
	$mainLang = DB::table('languages')->where('default', 1)->first();
	$mainLangCode = $mainLang->abbr ?? $mainLang->code ?? null;
	
	// Set Db Fallback Locale
	if (!empty($mainLangCode)) {
		setDbFallbackLocale($mainLangCode);
	}
	
	// ========================================================================================
	// categories
	$tableName = 'categories';
	$columns = ['name', 'description'];
	$indexes = [];
	migrateTransSchema($languages, $mainLang, $tableName, $columns, $indexes);
	
	// ========================================================================================
	// fields
	if (Schema::hasColumn('fields', 'default') && !Schema::hasColumn('fields', 'default_value')) {
		Schema::table('fields', function (Blueprint $table) {
			$table->renameColumn('default', 'default_value');
		});
	}
	$tableName = 'fields';
	$columns = ['name', 'default_value', 'help'];
	$indexes = [];
	migrateTransSchema($languages, $mainLang, $tableName, $columns, $indexes);
	
	// ========================================================================================
	// fields_options
	$tableName = 'fields_options';
	$columns = ['value'];
	$indexes = [];
	migrateTransSchema($languages, $mainLang, $tableName, $columns, $indexes);
	
	// ========================================================================================
	// gender
	$tableName = 'gender';
	$columns = ['name'];
	$indexes = [];
	migrateTransSchema($languages, $mainLang, $tableName, $columns, $indexes);
	
	// ========================================================================================
	// meta_tags
	$tableName = 'meta_tags';
	$columns = ['title', 'description', 'keywords'];
	$indexes = [];
	migrateTransSchema($languages, $mainLang, $tableName, $columns, $indexes);
	
	if (config('plugins.domainmapping.installed')) {
		// domain_meta_tags
		$tableName = 'domain_meta_tags';
		$columns = ['title', 'description', 'keywords'];
		$indexes = [];
		migrateTransSchema($languages, $mainLang, $tableName, $columns, $indexes);
	}
	
	// ========================================================================================
	// packages
	$tableName = 'packages';
	$columns = ['name', 'short_name', 'description'];
	$indexes = [];
	migrateTransSchema($languages, $mainLang, $tableName, $columns, $indexes);
	
	// ========================================================================================
	// pages
	$tableName = 'pages';
	$columns = ['name', 'title', 'content'];
	$indexes = [];
	migrateTransSchema($languages, $mainLang, $tableName, $columns, $indexes);
	
	// ========================================================================================
	// post_types
	$tableName = 'post_types';
	$columns = ['name'];
	$indexes = [];
	migrateTransSchema($languages, $mainLang, $tableName, $columns, $indexes);
	
	// ========================================================================================
	// report_types
	$tableName = 'report_types';
	$columns = ['name'];
	$indexes = [];
	migrateTransSchema($languages, $mainLang, $tableName, $columns, $indexes);
	
	// ========================================================================================
	// cities
	$tableName = 'cities';
	$columns = ['name'];
	$indexes = ['name'];
	migrateTransSchema($languages, $mainLang, $tableName, $columns, $indexes);
	
	// ========================================================================================
	// countries
	$tableName = 'countries';
	$columns = ['name'];
	$indexes = [];
	migrateTransSchema($languages, $mainLang, $tableName, $columns, $indexes);
	
	// ========================================================================================
	// subadmin1
	$tableName = 'subadmin1';
	$columns = ['name'];
	$indexes = ['name'];
	migrateTransSchema($languages, $mainLang, $tableName, $columns, $indexes);
	
	// ========================================================================================
	// subadmin2
	$tableName = 'subadmin2';
	$columns = ['name'];
	$indexes = ['name'];
	migrateTransSchema($languages, $mainLang, $tableName, $columns, $indexes);
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}

// ===| FUNCTIONS |===
function migrateTransSchema($languages, $mainLang, string $tableName, array $columns, array $indexes = []): void
{
	$mainLangCode = $mainLang->abbr ?? $mainLang->code ?? null;
	
	// Check & Drop Indexes
	if (is_array($indexes) && !empty($indexes)) {
		foreach ($indexes as $indexName) {
			DBIndex::dropIndexIfExists($tableName, $indexName);
		}
	}
	
	$columnsTypes = [];
	if (!empty($columns)) {
		foreach ($columns as $columnName) {
			if (Schema::hasColumn($tableName, $columnName)) {
				Schema::table($tableName, function (Blueprint $table) use ($columnName) {
					$table->text($columnName)->nullable()->change();
				});
			}
			$columnsTypes[$columnName] = Schema::getColumnType($tableName, $columnName);
		}
	}
	
	$allColumnsTypesAreTheSame = (is_array($columnsTypes) && count(array_unique($columnsTypes)) === 1 && end($columnsTypes) == 'text');
	
	if ($allColumnsTypesAreTheSame) {
		migrateTransData($languages, $mainLang, $tableName, $columns);
	}
	
	if (in_array($tableName, ['countries', 'cities', 'subadmin1', 'subadmin2'])) {
		if ($tableName == 'countries') {
			\App\Models\Country::autoTranslation(true);
		}
		
		if (!empty($mainLangCode)) {
			DBTool::convertTranslatableDataToJson($tableName, $columns, $mainLangCode);
		}
		
		if (Schema::hasColumn($tableName, 'asciiname')) {
			Schema::table($tableName, function (Blueprint $table) {
				$table->dropColumn('asciiname');
			});
		}
		
		if (in_array($tableName, ['subadmin1', 'subadmin2'])) {
			if (Schema::hasColumn($tableName, 'created_at')) {
				Schema::table($tableName, function (Blueprint $table) {
					$table->dropColumn('created_at');
				});
			}
			if (Schema::hasColumn($tableName, 'updated_at')) {
				Schema::table($tableName, function (Blueprint $table) {
					$table->dropColumn('updated_at');
				});
			}
		}
		
		return;
	}
	
	// Fix (2024)
	if (
		in_array($tableName, ['categories', 'packages'])
		&& (
			!Schema::hasColumn($tableName, 'translation_lang')
			|| !Schema::hasColumn($tableName, 'translation_of')
		)
	) {
		if (!empty($mainLangCode)) {
			DBTool::convertTranslatableDataToJson($tableName, $columns, $mainLangCode);
		}
	}
}

function migrateTransData($languages, $mainLang, string $tableName, array $columns): void
{
	$mainLangCode = $mainLang->abbr ?? $mainLang->code ?? null;
	
	if (empty($mainLangCode)) {
		return;
	}
	
	$newEntries = [];
	if (Schema::hasColumn($tableName, 'translation_lang') && Schema::hasColumn($tableName, 'translation_of')) {
		
		if ($languages->count() > 0) {
			foreach ($languages as $language) {
				$langCode = $language->abbr ?? $language->code ?? null;
				$oldEntries = DB::table($tableName)->where('translation_lang', $langCode)->get();
				if ($oldEntries->count() > 0) {
					foreach ($oldEntries as $oldEntry) {
						if (!empty($oldEntry)) {
							
							if (is_array($columns) && !empty($columns)) {
								foreach ($columns as $columnName) {
									if (!empty($oldEntry->{$columnName})) {
										$newEntries[$oldEntry->translation_of][$columnName][$oldEntry->translation_lang] = $oldEntry->{$columnName};
									} else {
										if (!isset($newEntries[$oldEntry->translation_of][$columnName]) && empty($newEntries[$oldEntry->translation_of][$columnName])) {
											$newEntries[$oldEntry->translation_of][$columnName] = null;
										}
									}
								}
							}
							
						}
					}
				}
			}
		}
		
		if (!empty($mainLang) && !empty($newEntries)) {
			$mainLangEntries = collect();
			if (Schema::hasColumn($tableName, 'translation_lang')) {
				$mainLangEntries = DB::table($tableName)->where('translation_lang', $mainLangCode)->get();
			}
			if ($mainLangEntries->count() > 0) {
				foreach ($mainLangEntries as $mainLangEntry) {
					$newEntry = $newEntries[$mainLangEntry->id] ?? null;
					if (!empty($newEntry)) {
						
						if (!is_array($columns) && !empty($columns)) {
							foreach ($columns as $columnName) {
								$newEntry[$columnName] = (isset($newEntry[$columnName]) && !empty($newEntry[$columnName]))
									? json_encode($newEntry[$columnName], JSON_UNESCAPED_UNICODE)
									: null;
							}
						}
						
						$affected = DB::table($tableName)->where('id', $mainLangEntry->id)->update($newEntry);
					}
				}
				
				if (Schema::hasColumn($tableName, 'translation_lang')) {
					$affected = DB::table($tableName)->where('translation_lang', '!=', $mainLangCode)->delete();
				}
				
				if (Schema::hasColumn($tableName, 'translation_lang')) {
					Schema::table($tableName, function (Blueprint $table) {
						$table->dropColumn('translation_lang');
					});
				}
				if (Schema::hasColumn($tableName, 'translation_of')) {
					Schema::table($tableName, function (Blueprint $table) {
						$table->dropColumn('translation_of');
					});
				}
			}
		}
		
	}
}
