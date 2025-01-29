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
		Schema::create('sections', function (Blueprint $table) {
			$table->increments('id');
			$table->string('belongs_to', 100)->default('home');
			$table->string('key', 100);
			$table->string('name', 100);
			$table->text('field')->nullable();
			$table->text('value')->nullable();
			$table->string('description', 500)->nullable();
			$table->integer('parent_id')->unsigned()->nullable();
			$table->integer('lft')->unsigned()->nullable();
			$table->integer('rgt')->unsigned()->nullable();
			$table->integer('depth')->unsigned()->nullable();
			$table->boolean('active')->nullable()->default(false);
			$table->timestamps();
			
			$table->unique(['belongs_to', 'key']);
			$table->index(['belongs_to']);
			$table->index(['key']);
			$table->index(['lft']);
			$table->index(['rgt']);
			$table->index(['active']);
		});
	}
	
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down(): void
	{
		Schema::dropIfExists('sections');
	}
};
