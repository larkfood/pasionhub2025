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

return [
	
	// MEDIA PATH
	// Default Logos
	'logo-factory' => '/images/logo.png',
	'logo'         => 'app/default/logo.png',
	'logo-dark'    => 'app/default/logo-dark.png',
	'logo-light'   => 'app/default/logo-light.png',
	
	// Default Icons
	'favicon'      => 'app/default/ico/favicon.png',
	
	// Default Pictures
	'picture'      => 'app/default/picture.jpg',
	'avatar'       => 'app/default/user.png',
	'company-logo' => 'app/default/picture.jpg',
	
	// MEDIA RESIZE
	/*
	 * Media Resize Default Parameters
	 *
	 * Note:
	 * The system types of resize below are not available in the 'Upload' options in the Admin Panel
	 * - logo-max,
	 * - cat,
	 * - bg-header, bg-body,
	 * - avatar, company-logo,
	 */
	'resize'       => [
		'methods'      => [
			'resize',
			'fit',
			'resizeCanvas',
		],
		'positions'    => ['top-left', 'top', 'top-right', 'left', 'center', 'right', 'bottom-left', 'bottom', 'bottom-right'],
		'namedOptions' => [
			'default'      => [
				'method'   => 'resize',
				'width'    => 1500,
				'height'   => 1500,
				'ratio'    => '1',
				'upsize'   => '0',
				'position' => 'center',
				'relative' => false,
				'bgColor'  => 'ffffff',
			],
			
			// logo
			'logo'         => [
				'method'   => 'resize',
				'width'    => 485, // 216|485,
				'height'   => 90,  // 40|90,
				'ratio'    => '1',
				'upsize'   => '0',
				'position' => 'center',
				'relative' => false,
				'bgColor'  => 'rgba(0, 0, 0, 0)',
			],
			'logo-max'     => [ // Used in CSS styles
				'method'   => 'resize',
				'width'    => 430,
				'height'   => 80,
				'ratio'    => '1',
				'upsize'   => '0',
				'position' => 'center',
				'relative' => false,
				'bgColor'  => 'rgba(0, 0, 0, 0)',
			],
			
			// icon
			'favicon'      => [
				'method'   => 'resize',
				'width'    => 32,
				'height'   => 32,
				'ratio'    => '1',
				'upsize'   => '0',
				'position' => 'center',
				'relative' => false,
				'bgColor'  => 'rgba(0, 0, 0, 0)',
			],
			
			// asset
			'cat'          => [
				'method'   => 'resize',
				'width'    => 70,
				'height'   => 70,
				'ratio'    => '1',
				'upsize'   => '0',
				'position' => 'center',
				'relative' => false,
				'bgColor'  => 'rgba(0, 0, 0, 0)',
			],
			'bg-header'    => [
				'method'   => 'resize',
				'width'    => 2000,
				'height'   => 1000,
				'ratio'    => '1',
				'upsize'   => '0',
				'position' => 'center',
				'relative' => false,
				'bgColor'  => 'ffffff',
			],
			'bg-body'      => [
				'method'   => 'resize',
				'width'    => 2500,
				'height'   => 2500,
				'ratio'    => '1',
				'upsize'   => '0',
				'position' => 'center',
				'relative' => false,
				'bgColor'  => 'ffffff',
			],
			
			// picture
			'picture-sm'   => [
				'label'    => 'small', // Local key or label
				'method'   => 'resizeCanvas',
				'width'    => 120,
				'height'   => 90,
				'ratio'    => '1',
				'upsize'   => '0',
				'position' => 'center',
				'relative' => false,
				'bgColor'  => 'ffffff',
			],
			'picture-md'   => [
				'label'    => 'medium',
				'method'   => 'fit', // 'fit' for LaraClassifier | 'resizeCanvas' for JobClass
				'width'    => 320,
				'height'   => 240,
				'ratio'    => '1',
				'upsize'   => '0',
				'position' => 'center',
				'relative' => false,
				'bgColor'  => 'ffffff',
			],
			'picture-lg'   => [
				'label'    => 'large',
				'method'   => 'resize',
				'width'    => 816,
				'height'   => 460,
				'ratio'    => '1',
				'upsize'   => '0',
				'position' => 'center',
				'relative' => false,
				'bgColor'  => 'ffffff',
			],
			
			// avatar
			'avatar'       => [
				'method'   => 'resize',
				'width'    => 800,
				'height'   => 800,
				'ratio'    => '1',
				'upsize'   => '0',
				'position' => 'center',
				'relative' => false,
				'bgColor'  => 'ffffff',
			],
			
			// company
			'company-logo' => [
				'method'   => 'resize',
				'width'    => 800,
				'height'   => 800,
				'ratio'    => '1',
				'upsize'   => '0',
				'position' => 'center',
				'relative' => false,
				'bgColor'  => 'rgba(0, 0, 0, 0)',
			],
		],
	],
	
	'versioned' => env('PICTURE_VERSIONED', false),
	'version'   => env('PICTURE_VERSION', 1),

];
