<?php

use App\Exceptions\Custom\CustomException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

// ===| FILES |===
try {
	
	File::delete(app_path('Http/Controllers/SelectLangController.php'));
	
} catch (\Throwable $e) {
}

// ===| DATABASE |===
try {
	
	// pages
	// Make Pages Slug Unique
	$slugsCollection = DB::table('pages')->selectRaw('slug, COUNT(*) countSlugs')->groupBy('slug')->having('countSlugs', '>', 1)->get();
	if ($slugsCollection->count() > 0) {
		foreach ($slugsCollection as $obj) {
			$pages = DB::table('pages')->where('slug', $obj->slug)->get();
			if ($pages->count() > 0) {
				foreach ($pages as $page) {
					if (!isset($page->translation_of) || !isset($page->translation_lang)) {
						continue;
					}
					if ($page->id != $page->translation_of) {
						$pageSlug = preg_replace('/\-' . strtolower($page->translation_lang) . '$/', '', $page->slug);
						DB::table('pages')->where('id', $page->id)->update([
							'slug' => $pageSlug . '-' . strtolower($page->translation_lang),
						]);
					}
				}
			}
		}
	}
	
} catch (\Throwable $e) {
	
	$message = $e->getMessage() . "\n" . 'in ' . str_replace(base_path(), '', __FILE__);
	throw new CustomException($message);
	
}




