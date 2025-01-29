<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up(): void
	{
		Schema::create('users', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('country_code', 2)->nullable();
			$table->string('language_code', 10)->nullable();
			$table->tinyInteger('user_type_id')->unsigned()->nullable();
			$table->integer('gender_id')->unsigned()->nullable();
			$table->string('name', 100);
			$table->string('photo_path', 255)->nullable();
			$table->string('about', 255)->nullable();
			$table->enum('auth_field', ['email', 'phone'])->nullable()->default('email');
			$table->string('email', 191)->nullable();
			$table->string('phone', 60)->nullable();
			$table->string('phone_national', 30)->nullable();
			$table->string('phone_country', 2)->nullable();
			$table->boolean('phone_hidden')->nullable()->default('0');
			$table->string('username', 100)->nullable();
			$table->string('password', 60)->nullable();
			$table->string('remember_token', 191)->nullable();
			$table->boolean('is_admin')->nullable()->default('0');
			$table->boolean('can_be_impersonated')->nullable()->default('1');
			$table->boolean('disable_comments')->nullable()->default('0');
			$table->string('create_from_ip', 50)->nullable()->comment('IP address of creation');
			$table->string('latest_update_ip', 50)->nullable()->comment('Latest update IP address');
			$table->string('provider', 100)->nullable()->comment('facebook, google, twitter, linkedin, ...');
			$table->string('provider_id', 191)->nullable()->comment('Provider User ID');
			$table->string('email_token', 191)->nullable();
			$table->string('phone_token', 191)->nullable();
			$table->timestamp('email_verified_at')->nullable();
			$table->timestamp('phone_verified_at')->nullable();
			$table->boolean('accept_terms')->nullable()->default('0');
			$table->boolean('accept_marketing_offers')->nullable()->default('0');
			$table->boolean('dark_mode')->nullable()->default('0');
			$table->string('time_zone', 50)->nullable();
			$table->boolean('featured')->nullable()->default('0')
				->comment('Need to be cleared form a cron tab command');
			$table->boolean('blocked')->nullable()->default('0');
			$table->boolean('closed')->nullable()->default('0');
			$table->datetime('last_activity')->nullable();
			$table->datetime('last_login_at')->nullable();
			$table->timestamp('deleted_at')->nullable();
			$table->timestamps();
			
			$table->index(['country_code']);
			$table->index(['user_type_id']);
			$table->index(['gender_id']);
			$table->index(['auth_field']);
			$table->index(['email']);
			$table->index(['phone']);
			$table->index(['phone_country']);
			$table->index(['username']);
			$table->index(['email_verified_at']);
			$table->index(['phone_verified_at']);
			$table->index(['is_admin']);
			$table->index(['can_be_impersonated']);
		});
		
		Schema::create('password_reset_tokens', function (Blueprint $table) {
			$table->string('email', 191)->nullable();
			$table->string('phone', 191)->nullable();
			$table->string('phone_country', 2)->nullable();
			$table->string('token', 191)->nullable();
			$table->timestamp('created_at')->nullable();
			
			$table->index(['email']);
			$table->index(['phone']);
			$table->index(['token']);
		});
		
		Schema::create('sessions', function (Blueprint $table) {
			$table->string('id')->primary();
			$table->foreignId('user_id')->nullable()->index();
			$table->string('ip_address', 45)->nullable();
			$table->text('user_agent')->nullable();
			$table->text('payload');
			$table->integer('last_activity')->index();
		});
	}
	
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down(): void
	{
		Schema::dropIfExists('users');
		Schema::dropIfExists('password_reset_tokens');
		Schema::dropIfExists('sessions');
	}
};
