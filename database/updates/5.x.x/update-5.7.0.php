<?php

use App\Exceptions\Custom\CustomException;
use App\Helpers\DotenvEditor;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

// ===| FILES |===
try {
	
	File::deleteDirectory(app_path('Mail/'));
	File::deleteDirectory(base_path('resources/assets/'));
	File::deleteDirectory(base_path('resources/views/emails/'));
	
	// .ENV
	DotenvEditor::deleteKey('QUEUE_DRIVER');
	DotenvEditor::deleteKey('SESSION_LIFETIME');
	
	DotenvEditor::setKey('QUEUE_CONNECTION', 'sync');
	DotenvEditor::setKey('SESSION_LIFETIME', 10080);
	DotenvEditor::save();
	
} catch (\Throwable $e) {
}

// ===| DATABASE |===
try {
	
	// posts
	if (!Schema::hasColumn('posts', 'archived_at')) {
		Schema::table('posts', function (Blueprint $table) {
			$table->timestamp('archived_at')->nullable()->after('archived');
		});
	}
	if (!Schema::hasColumn('posts', 'deletion_mail_sent_at') && Schema::hasColumn('posts', 'archived_at')) {
		Schema::table('posts', function (Blueprint $table) {
			$table->timestamp('deletion_mail_sent_at')->nullable()->after('archived_at');
		});
	}
	
	// users
	if (!Schema::hasColumn('users', 'photo')) {
		Schema::table('users', function (Blueprint $table) {
			$table->string('photo', 255)->nullable()->after('name');
		});
	}
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}
