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

namespace App\Providers;

use App\Exceptions\Custom\CustomException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

/*
 * IMPORTANT:
 * - The 'app/Macros/' directory needs to be created first to save the first macro file
 * - Macro files need to be organized following the Laravel macoable class's namespace
 * in the 'Macros/' directory, by removing the '\Illuminate\' from the macros files path
 */

class MacroServiceProvider extends ServiceProvider
{
	/**
	 * Bootstrap services.
	 *
	 * @return void
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	public function boot(): void
	{
		// Get the macros directory's path
		$macrosDir = 'Macros/';
		$macrosDirPath = __DIR__ . '/../' . $macrosDir;
		
		// Check if the macros directory exists
		if (!File::exists($macrosDirPath) || !File::isDirectory($macrosDirPath)) {
			return;
		}
		
		// Get all the files available in the 'app/Macro/' directory
		// $files = File::allFiles($macrosDirPath);
		$files = recursiveGlob($macrosDirPath . '*.php');
		
		// No files found
		if (count($files) <= 0) {
			return;
		}
		
		// Get the macros classes
		$files = collect($files)
			->reject(fn ($item) => !str_ends_with(strtolower($item), '.php'))
			->mapWithKeys(function ($path, $key) use ($macrosDir) {
				$className = File::name($path);
				$macro = str($className)->camel()->toString();
				
				$class = str($path)->dirname()->after($macrosDir)->replace('/', '\\');
				$macroClass = $class->prepend('\App\Macros\\')->append('\\' . $className)->toString();
				$laravelClass = $class->prepend('\Illuminate\\')->toString();
				
				return [
					$key => [
						'laravelClass' => $laravelClass,
						'macroClass'   => $macroClass,
						'macro'        => $macro,
					],
				];
			});
		
		// Reject all helpers and traits
		$files = $files->reject(function ($item) {
			return (
				str_ends_with($item['laravelClass'], 'Helpers')
				|| str_ends_with($item['laravelClass'], 'Traits')
			);
		});
		
		// dd($files->toArray()); // debug!
		
		// Check if no macro is available
		if ($files->count() <= 0) {
			return;
		}
		
		// Register all macros
		$files->each(function ($item) {
			$laravelClass = $item['laravelClass'];
			$macroClass = $item['macroClass'];
			$macro = $item['macro'];
			
			// The macro class needs to be existed
			$laravelClassExists = class_exists($laravelClass);
			if (!$laravelClassExists) {
				// throw new CustomException("$laravelClass does not exist to extend it with macros.");
				return false;
			}
			
			$macroClassExists = class_exists($macroClass);
			if (!$macroClassExists) {
				// throw new CustomException("$macroClass does not exist. It cannot be used as macro.");
				return false;
			}
			
			// Is the Laravel is macoable?
			// Note: The Laravel class must have 'macro' static method
			$isMacroable = isMacroable($laravelClass);
			if (!$isMacroable) {
				// throw new CustomException("$laravelClass is not macroable.");
				return false;
			}
			
			// Register the macro
			return $laravelClass::macro($macro, app($macroClass)());
		});
	}
}
