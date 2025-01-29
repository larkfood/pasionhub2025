<?php

namespace Database\Seeders;

use App\Enums\Gender;
use App\Enums\UserType;
use App\Helpers\DBTool;
use App\Models\Country;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class SiteInfoSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @param array $data
	 * @return void
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	public function run(array $data = []): void
	{
		if (empty($data)) return;
		
		// Disable foreign key constraints (Temporarily)
		Schema::disableForeignKeyConstraints();
		
		// USERS: Create the default super admin user
		$userData = (array)data_get($data, 'user', []);
		$this->createSuperAdminUser($userData);
		
		// GEONAMES DATA - Insert the default country data & activate it
		$countryCode = data_get($data, 'settings.localization.default_country_code');
		$countryCode = getAsStringOrNull($countryCode);
		$this->insertDefaultCountryData($countryCode);
		
		// SETTINGS - Set up the default settings
		$settings = (array)data_get($data, 'settings', []);
		$this->setupDefaultSettings($settings);
		
		// Re-Enable back foreign key constraints
		Schema::enableForeignKeyConstraints();
	}
	
	/**
	 * Create the default super admin user
	 *
	 * @param array $userData
	 * @return void
	 */
	private function createSuperAdminUser(array $userData): void
	{
		// Make sure table is empty
		$usersTable = (new User())->getTable();
		DB::table($usersTable)->truncate();
		
		// Create new admin user
		$userData = array_merge($userData, [
			'language_code'     => config('app.locale', 'en'),
			'user_type_id'      => UserType::PROFESSIONAL,
			'gender_id'         => Gender::MALE,
			'about'             => 'Administrator',
			'username'          => 'admin',
			'is_admin'          => 1,
			'create_from_ip'    => request()->ip(),
			'email_verified_at' => now(),
			'phone_verified_at' => now(),
			'accept_terms'      => 1,
			'created_at'        => now(),
			'updated_at'        => now(),
		]);
		$userData['password'] = Hash::make($userData['password']);
		
		// Save the first user (Know as Super-Admin)
		// $userId = DB::table($usersTable)->insertGetId($userData);
		$user = new User();
		foreach ($userData as $column => $value) {
			$user->{$column} = $value;
		}
		$user->saveQuietly();
		
		if (empty($user)) return;
		
		// Setup ACL system
		// Create the default role & permission in the database
		if (!Permission::checkDefaultPermissions()) {
			Permission::resetDefaultPermissions();
		}
		
		// Assign the super-admin role to the user
		$role = Role::getSuperAdminRoleFromDb();
		if (!empty($role) && isset($role->name)) {
			$user->removeRole($role->name);
			$user->assignRole($role->name);
		}
	}
	
	/**
	 * Insert the default country data & activate it
	 *
	 * @param string|null $countryCode
	 * @return void
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	private function insertDefaultCountryData(?string $countryCode): void
	{
		if (empty($countryCode)) return;
		
		// Make sure table is empty
		$countriesTable = (new Country())->getTable();
		
		// Get the database PDO resource & tables prefix
		$pdo = DB::connection()->getPdo();
		$tablesPrefix = DB::connection()->getTablePrefix();
		
		// Insert the default country data
		$this->importGeonamesSql($pdo, $tablesPrefix, $countryCode);
		
		// Activate the default country
		DB::table($countriesTable)->where('code', '=', $countryCode)->update(['active' => 1]);
	}
	
	/**
	 * Set up the default settings
	 *
	 * @param array $settings
	 * @return void
	 */
	private function setupDefaultSettings(array $settings): void
	{
		$settingsTable = (new Setting())->getTable();
		
		// settings.app
		$app = $settings['app'] ?? [];
		DB::table($settingsTable)->where('key', '=', 'app')->update(['value' => json_encode($app)]);
		
		// settings.localization
		$localization = $settings['localization'] ?? [];
		DB::table($settingsTable)->where('key', '=', 'localization')->update(['value' => json_encode($localization)]);
		
		// settings.mail
		$mail = $settings['mail'] ?? [];
		DB::table($settingsTable)->where('key', '=', 'mail')->update(['value' => json_encode($mail)]);
	}
	
	/**
	 * Import the Default Country Data from the Geonames SQL Files
	 *
	 * @param \PDO $pdo
	 * @param $tablesPrefix
	 * @param $countryCode
	 * @return void
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	private function importGeonamesSql(\PDO $pdo, $tablesPrefix, $countryCode): void
	{
		// Default Country SQL file
		$filename = 'database/geonames/countries/' . strtolower($countryCode) . '.sql';
		$filePath = storage_path($filename);
		
		// Import the SQL file
		DBTool::importSqlFile($pdo, $filePath, $tablesPrefix);
	}
}
