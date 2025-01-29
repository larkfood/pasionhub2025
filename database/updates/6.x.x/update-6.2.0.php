<?php

use App\Helpers\DotenvEditor;
use Illuminate\Support\Facades\File;

// ===| FILES |===
try {
	
	File::deleteDirectory(base_path('packages/mcamara/laravel-localization/src/Exceptions/'));
	File::deleteDirectory(base_path('packages/mcamara/laravel-localization/src/Facades/'));
	File::deleteDirectory(base_path('packages/mcamara/laravel-localization/src/Middleware/'));
	File::deleteDirectory(base_path('packages/mcamara/laravel-localization/src/Traits/'));
	File::delete(base_path('packages/mcamara/laravel-localization/src/LanguageNegotiator.php'));
	File::delete(base_path('packages/mcamara/laravel-localization/src/LaravelLocalization.php'));
	File::delete(base_path('packages/mcamara/laravel-localization/src/LaravelLocalizationServiceProvider.php'));
	
	// .ENV
	$needToBeSaved = false;
	if (DotenvEditor::keyExists('RECAPTCHA_PUBLIC_KEY')) {
		$recaptchaPublicKey = DotenvEditor::getValue('RECAPTCHA_PUBLIC_KEY');
		DotenvEditor::setKey('RECAPTCHA_SITE_KEY', $recaptchaPublicKey);
		DotenvEditor::deleteKey('RECAPTCHA_PUBLIC_KEY');
		$needToBeSaved = true;
	}
	if (DotenvEditor::keyExists('RECAPTCHA_PRIVATE_KEY')) {
		$recaptchaPrivateKey = DotenvEditor::getValue('RECAPTCHA_PRIVATE_KEY');
		DotenvEditor::setKey('RECAPTCHA_SECRET_KEY', $recaptchaPrivateKey);
		DotenvEditor::deleteKey('RECAPTCHA_PRIVATE_KEY');
		$needToBeSaved = true;
	}
	if ($needToBeSaved) {
		DotenvEditor::save();
	}
	
} catch (\Throwable $e) {
}
