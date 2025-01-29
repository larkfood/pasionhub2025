<?php
/*
 * LaraClassifier - Classified Ads Web Application
 * Copyright (c) BeDigit. All Rights Reserved
 *
 * Website: https://laraclassifier.com
 * Author: Mayeul Akpovi (BeDigit - https://bedigit.com)
 *
 * LICENSE
 * -------
 * This software is provided under a license agreement and may only be used or copied
 * in accordance with its terms, including the inclusion of the above copyright notice.
 * As this software is sold exclusively on CodeCanyon,
 * please review the full license details here: https://codecanyon.net/licenses/standard
 */

namespace App\Services\Lang;

use App\Services\Lang\Traits\LangFilesTrait;
use App\Services\Lang\Traits\LangLinesTrait;

class LangManager
{
	use LangFilesTrait, LangLinesTrait;
	
	/**
	 * The path to the language files.
	 *
	 * @var string
	 */
	protected string $path;
	
	/**
	 * The master language code
	 *
	 * @var string
	 */
	protected string $masterLangCode = 'en';
	
	/**
	 * Included languages files
	 *
	 * @var array
	 */
	protected array $includedLanguagesFiles = [
		'en', // English (en_US)
		'fr', // French (fr_FR) - Français
		'es', // Spanish (es_ES) - Español
		'ar', // Arabic (ar_SA) - ‫العربية
		'pt', // Portuguese (pt_PT) - Português
		'de', // German (de_DE) - Deutsch
		'it', // Italian (it_IT) - Italiano
		'tr', // Turkish (tr_TR) - Türkçe
		'ru', // Russian (ru_RU) - Русский
		'hi', // Hindi (hi_IN) - हिन्दी
		'bn', // Bengali (bn_BD) - বাংলা
		'zh', // Simplified Chinese (zh_CN) - 简体中文
		'ja', // Japanese (ja_JP) - 日本語
		'th', // Thai (th_TH) - ไทย
		'ro', // Romanian (ro_RO) - Română
		'ka', // Georgian (ka_GE) - ქართული
		'he', // Hebrew (he_IL) - עִברִית
	];
	
	/**
	 * LangManager constructor.
	 */
	public function __construct()
	{
		$this->path = base_path('lang/');
	}
	
	/**
	 * Get all codes of the included languages
	 *
	 * @return array
	 */
	public function getIncludedLanguages(): array
	{
		return $this->includedLanguagesFiles;
	}
	
	/**
	 * Get all the codes of included and existing languages
	 *
	 * @return array
	 */
	public function getTranslatedLanguages(): array
	{
		$languages = [];
		
		if (!empty($this->includedLanguagesFiles)) {
			foreach($this->includedLanguagesFiles as $code) {
				$path = $this->path . $code;
				if (file_exists($path) && is_dir($path)) {
					$languages[] = $code;
				}
			}
		}
		
		return $languages;
	}
}
