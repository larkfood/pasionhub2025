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

namespace App\Http\Controllers\Web\Front;

use App\Helpers\Files\Response\FileContentResponseCreator;
use App\Helpers\Files\Storage\StorageDisk;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Front\Traits\HasIntlTelInput;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Routing\Controllers\Middleware;

class FileController extends Controller
{
	use HasIntlTelInput;
	
	protected Filesystem $disk;
	private static ?string $diskName = null;
	
	/**
	 * FileController constructor.
	 */
	public function __construct()
	{
		$tmpDiskName = request()->input('disk');
		if (!empty($tmpDiskName) && is_string($tmpDiskName)) {
			$allowedNames = ['private', 'public'];
			if (config('filesystems.disks.' . $tmpDiskName) && in_array($tmpDiskName, $allowedNames)) {
				self::$diskName = $tmpDiskName;
			}
		}
		
		$this->disk = StorageDisk::getDisk(self::$diskName);
	}
	
	/**
	 * Get the middleware that should be assigned to the controller.
	 */
	public static function middleware(): array
	{
		$array = [];
		
		if (self::$diskName == 'private') {
			$array[] = new Middleware('auth', only: ['show']);
		}
		
		return array_merge(parent::middleware(), $array);
	}
	
	/**
	 * Get & watch media file (image, audio & video) content
	 *
	 * @param \App\Helpers\Files\Response\FileContentResponseCreator $response
	 * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\StreamedResponse|null
	 * @throws \League\Flysystem\FilesystemException
	 */
	public function watchMediaContent(FileContentResponseCreator $response)
	{
		$filePath = request()->input('path');
		$filePath = preg_replace('|\?.*|ui', '', $filePath);
		
		$out = $response::create($this->disk, $filePath);
		
		if (ob_get_length()) {
			ob_end_clean(); // HERE IS THE MAGIC
		}
		
		return $out;
	}
	
	/**
	 * Translation of the bootstrap-fileinput plugin
	 *
	 * @param string $code
	 * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|void
	 */
	public function bootstrapFileinputLocales(string $code = 'en')
	{
		$fileInputArray = trans('fileinput', [], $code);
		if (is_array($fileInputArray) && !empty($fileInputArray)) {
			if (config('settings.optimization.minify_html_activation') == 1) {
				$fileInputJson = json_encode($fileInputArray, JSON_UNESCAPED_UNICODE);
			} else {
				$fileInputJson = json_encode($fileInputArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
			}
			
			if (!empty($fileInputJson)) {
				// $fileInputJson = str_replace('<\/', '</', $fileInputJson);
				
				$out = "(function (factory) {" . "\n";
				$out .= "   'use strict';" . "\n";
				$out .= "if (typeof define === 'function' && define.amd) {" . "\n";
				$out .= "   define(['jquery'], factory);" . "\n";
				$out .= "} else if (typeof module === 'object' && typeof module.exports === 'object') {" . "\n";
				$out .= "   factory(require('jquery'));" . "\n";
				$out .= "} else {" . "\n";
				$out .= "   factory(window.jQuery);" . "\n";
				$out .= "}" . "\n";
				$out .= '}(function ($) {' . "\n";
				$out .= '"use strict";' . "\n\n";
				
				$out .= "$.fn.fileinputLocales['$code'] = ";
				$out .= $fileInputJson . ';' . "\n";
				$out .= '}));' . "\n";
				
				return response($out, 200)->header('Content-Type', 'application/javascript');
			}
		}
		
		$filePath = public_path('assets/plugins/bootstrap-fileinput/js/locales/' . getLangTag(config('app.locale')) . '.js');
		if (file_exists($filePath)) {
			$out = file_get_contents($filePath);
			
			return response($out, 200)->header('Content-Type', 'application/javascript');
		}
		
		abort(404, 'File not found!');
	}
	
	/**
	 * Generate Skin & Custom CSS
	 *
	 * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
	 */
	public function cssStyle()
	{
		$displayKey = request()->input('display');
		$displayKey = getAsStringOrNull($displayKey);
		
		$out = '';
		
		$hOut = '/* === CSS Version === */' . "\n";
		$hOut .= '/* === v' . config('version.app') . ' === */' . "\n";
		
		try {
			$out .= view('front.common.css.style', ['disk' => $this->disk])->render();
			$out .= view('front.common.css.ribbons', ['disk' => $this->disk, 'displayKey' => $displayKey])->render();
			$out = preg_replace('|</?style[^>]*>|i', '', $out);
		} catch (\Throwable $e) {
			$out .= '/* === CSS Error Found === */' . "\n";
		}
		
		$out = cssMinify($out);
		
		$out = $hOut . $out;
		
		return response($out, 200)->header('Content-Type', 'text/css');
	}
}
