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
	
    /*
     |-----------------------------------------------------------------------------------------------
     | The item's info on CodeCanyon
     |-----------------------------------------------------------------------------------------------
     |
     */
	'item' => [
		'id'    => '16458425',
		'name'  => 'LaraClassifier',
		'title' => 'Classified Ads Web Application',
		'slug'  => 'laraclassifier',
		'url'   => 'https://laraclassifier.com/',
	],
	
    /*
     |-----------------------------------------------------------------------------------------------
     | Purchase code checker URL
     |-----------------------------------------------------------------------------------------------
     |
     */
    'purchaseCodeCheckerUrl' => 'https://api.bedigit.com/envato.php?purchase_code=',
    'purchaseCodeFindingUrl' => 'https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code',
	
	/*
     |-----------------------------------------------------------------------------------------------
     | Purchase Code
     |-----------------------------------------------------------------------------------------------
     |
     */
	'purchaseCode' => env('PURCHASE_CODE', ''),
	
    /*
     |-----------------------------------------------------------------------------------------------
     | Demo Website Info
     |-----------------------------------------------------------------------------------------------
     |
     */
    'demo' => [
    	'domain' => 'laraclassifier.com',
		'hosts'  => [
			'laraclassified.bedigit.com',
			'demo.laraclassifier.com',
		],
	],
	
	/*
     |-----------------------------------------------------------------------------------------------
     | App's Charset
     |-----------------------------------------------------------------------------------------------
	 | It is very important to not change this value
	 | because the UTF-8 charset is more universal and easier to use.
	 | Unless you know what you're doing.
	 |
     */
	'charset' => env('CHARSET', 'utf-8'),
	
	/*
     |-----------------------------------------------------------------------------------------------
     | Database Charset & Collation
     |-----------------------------------------------------------------------------------------------
	 | utf8mb4 & utf8mb4_general_ci => MySQL v5.5.3 or greater
	 | utf8mb4 & utf8mb4_0900_ai_ci => MySQL v8.0 or greater
	 |
	 | utf8mb3: Alias for utf8 (Deprecated in favor of utf8mb4)
	 | utf8mb4_0900_ai_ci: Default collation for utf8mb4 introduced in MySQL 8.0
	 | utf8mb4_general_ci: Default collation for utf8mb4 in MySQL before version 8.0
	 | utf8mb4_unicode_ci: Provides more accurate Unicode sorting and comparison than utf8mb4_general_ci, but less accurate than utf8mb4_0900_ai_ci
	 |
     */
	'database' => [
		'encoding'   => [
			'default'     => [
				'charset'   => 'utf8mb4',
				'collation' => 'utf8mb4_unicode_ci',
			],
			'recommended' => [
				'utf8mb4' => ['utf8mb4_0900_ai_ci', 'utf8mb4_unicode_ci', 'utf8mb4_general_ci'],
				'utf8mb3' => ['utf8mb3_unicode_ci', 'utf8mb3_general_ci'],
				'utf8'    => ['utf8_unicode_ci', 'utf8_general_ci'],
			],
		],
	],
	
	/*
     |-----------------------------------------------------------------------------------------------
     | Bootstrap-FileInput
     |-----------------------------------------------------------------------------------------------
     |
     */
	'fileinput' => ['theme' => 'bs5'],
	
	/*
     |-----------------------------------------------------------------------------------------------
     | TextToImage settings (Used to convert phone numbers to image)
     |-----------------------------------------------------------------------------------------------
     |
	 | format                    : More info at: app/Helpers/Functions/referrer/supported-images.php
	 | color                     : RGB (Example RGB: #FFFFFF = White)
	 | backgroundColor           : RGBA or RGB (Examples RGBA: rgba(0,0,0,0.0) = Transparent)
	 | fontFamily (Required)     : Path: /packages/larapen/texttoimage/src/Libraries/font/Foo.ttf
	 | boldFontFamily (Optional) : Path: /packages/larapen/texttoimage/src/Libraries/font/FooBold.ttf
	 |
	 | NOTE: Transparent value is only available for PNG format.
	 |
     */
	'textToImage' => [
		'format'          => 'png', // PNG format is required for transparency
		'color'           => '#FFFFFF',
		'backgroundColor' => 'transparent', // 'transparent' or rgba(0,0,0,0.0)
		'fontFamily'      => 'FiraSans-Regular.ttf',  // Required
		'boldFontFamily'  => 'FiraSans-SemiBold.ttf', // Optional - Use it if available.
		'fontSize'        => 13,
		'padding'         => 0,
		'shadowEnabled'   => false,
		'shadowColor'     => '#666666',
		'shadowOffsetX'   => 1,
		'shadowOffsetY'   => 1,
		'quality'         => 100,
		'retinaEnabled'   => true,
	],
	
    /*
     |-----------------------------------------------------------------------------------------------
     | Countries SVG maps folder & URL base
     |-----------------------------------------------------------------------------------------------
     |
     */
    'maps' => [
        'path'    => public_path('images/maps') . DIRECTORY_SEPARATOR,
        'urlBase' => 'images/maps/',
    ],
	
	/*
     |-----------------------------------------------------------------------------------------------
     | Icon set of the current version of the main icons fonts
     |-----------------------------------------------------------------------------------------------
	 |
	 | - Bootstrap Icons
	 | - Font Awesome Free
	 |
	 | Related to the "bootstrap-iconpicker" plugin features
	 |
     */
    'defaultFontIconSet' => 'bootstrapfontawesome',
    'fontIconSet' => [
	    'bootstrap' => [
		    'version' => '1.11.3',
		    'key'     => 'bootstrapicons',
		    'path'    => public_path('assets/plugins/bootstrap-iconpicker/js/iconset/iconset-bootstrapicons-all.js'),
	    ],
	    'fontawesome' => [
		    'version' => '6.5.2',
		    'key'     => 'fontawesome6',
		    'path'    => public_path('assets/plugins/bootstrap-iconpicker/js/iconset/iconset-fontawesome6-all.js'),
	    ],
	    'bootstrapfontawesome' => [
		    'version' => 'current',
		    'key'     => 'bootstrapfontawesome',
		    'path'    => public_path('assets/plugins/bootstrap-iconpicker/js/iconset/iconset-bootstrapfontawesome-all.js'),
	    ],
    ],
	
    /*
     |-----------------------------------------------------------------------------------------------
     | Optimize your URLs for SEO (for International website)
     |-----------------------------------------------------------------------------------------------
     |
     | You have to set the variables below in the /.env file:
     |
     | MULTI_COUNTRY_URLS=true (to enable the multi-country URLs optimization)
     | HIDE_DEFAULT_LOCALE_IN_URL=false (to show the default language code in the URLs)
     |
     */
    'multiCountryUrls' => env('MULTI_COUNTRY_URLS', false),
	
    /*
     |-----------------------------------------------------------------------------------------------
     | Force links to use the HTTPS protocol
     |-----------------------------------------------------------------------------------------------
     |
     */
    'forceHttps' => env('FORCE_HTTPS', false),
	
	/*
     |-----------------------------------------------------------------------------------------------
     | Headers - No Cache during redirect (Prevent Browser cache)
     |-----------------------------------------------------------------------------------------------
     | 'Cache-Control' => 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0', // IE.
	 |
     */
	'noCacheHeaders' => [
		'Cache-Control' => 'no-store, no-cache, must-revalidate', // HTTP 1.1.
		'Pragma'        => 'no-cache', // HTTP 1.0.
		'Expires'       => 'Sun, 02 Jan 1990 05:00:00 GMT', // Proxies. (Date in the past)
		'Last-Modified' => gmdate('D, d M Y H:i:s') . ' GMT',
	],
	
	/*
     |-----------------------------------------------------------------------------------------------
     | Performance (preventLazyLoading & httpRequestTimeout) & Debug Bar
     |-----------------------------------------------------------------------------------------------
	 | preventLazyLoading:
	 | Disable lazy loading (completely).
	 | Errors will be occurred if the Eloquent queries are not optimized.
	 | NOTE: Don't apply that on production to prevent exception errors.
	 |
	 | httpRequestTimeout: in seconds (1 recommended)
	 | Fire action when HTTP request running duration is more than specified value
	 |
	 | debugBar:
	 | In addition to this option, the Debug Bar will be enabled when APP_DEBUG is true
	 |
     */
	'performance' => [
		'preventLazyLoading' => env('PREVENT_LAZY_LOADING', false),
		'httpRequestTimeout' => env('HTTP_REQUEST_TIMEOUT', 1),
	],
	'debugBar' => env('DEBUG_BAR', false),
	
	/*
     |-----------------------------------------------------------------------------------------------
     | Storing user's selected|preferred language in: cookie or session
     |-----------------------------------------------------------------------------------------------
     | Possible value: cookie, session
	 |
     */
	'storingUserSelectedLang' => 'cookie',
	
    /*
     |-----------------------------------------------------------------------------------------------
     | Plugins Path & Namespace
     |-----------------------------------------------------------------------------------------------
     |
     */
    'plugin' => [
        'path'      => base_path('extras/plugins') . DIRECTORY_SEPARATOR,
        'namespace' => '\\extras\plugins\\',
    ],
	
	// Available only when the Multi-Domain plugin is installed
	'dmCountriesListAsHomepage' => env('DM_COUNTRIES_LIST_AS_HOMEPAGE'),
	
    /*
     |-----------------------------------------------------------------------------------------------
     | Managing User's Fields
     |-----------------------------------------------------------------------------------------------
     | Disable (or not) these fields on the user's creation form
     | and on the listing's creation form when users are not logged in.
     |
     */
    'disable' => [
		'username' => env('DISABLE_USERNAME', true),
    ],

	/*
     |-----------------------------------------------------------------------------------------------
     | Display both auth fields
     |-----------------------------------------------------------------------------------------------
     | IMPORTANT:
	 | - The both auth fields (email and phone) cannot be displayed when both these fields need
	 |   to be verified. So to make work this option, you have to disable the email verification
	 |   or the phone verification option from the Admin panel.
	 | - By setting the option bellow to 'false', and since it's not possible to disable email field
	 |   to be auth field, it will be always possible to fill the email field in the related forms.
	 |   It's not the case for the phone field that can be disabled as auth field from the Admin panel.
     |
     */
	'displayBothAuthFields' => env('DISPLAY_BOTH_AUTH_FIELDS', true),
    
    /*
     |-----------------------------------------------------------------------------------------------
     | Custom Prefix for the new locations (Administrative Divisions) Codes
     |-----------------------------------------------------------------------------------------------
     |
     */
    'locationCodePrefix' => 'Z',
	
	/*
     |-----------------------------------------------------------------------------------------------
     | Send Notifications On Error Exceptions
     |-----------------------------------------------------------------------------------------------
	 | This option will allow mail sending when error exceptions occurred.
	 | Note: The notifications will be sent in the email set in the:
	 |       "Settings -> General -> Application -> Email"
     |
     */
	'sendNotificationOnError' => env('SEND_NOTIFICATION_ON_ERROR', false),
	
	/*
     |-----------------------------------------------------------------------------------------------
     | Date & Datetime Format
	 |-----------------------------------------------------------------------------------------------
     | Accepted formats:
     | - ISO Format: https://carbon.nesbot.com/docs/#api-localization
     | - PHP-specific dates formats
     |     |- DateTimeInterface::format():https://www.php.net/manual/en/datetime.format.php
     |     |- strftime(): https://www.php.net/manual/en/function.strftime.php
	 |
	 | Worldwide Date and Time Formats: https://www.timeandunits.com/time-and-date-format.html
	 |
     */
	'dateFormat' => [
		'default' => 'YYYY-MM-DD',
		'php'     => 'Y-m-d',
	],
	'datetimeFormat' => [
		'default' => 'YYYY-MM-DD HH:mm',
		'php'     => 'Y-m-d H:i',
	],
	
	/*
     |-----------------------------------------------------------------------------------------------
     | Hashable ID Prefix
     |-----------------------------------------------------------------------------------------------
     |
     */
	'hashableIdPrefix' => env('HASHABLE_ID_PREFIX', ''),
	
	/*
     |-----------------------------------------------------------------------------------------------
     | Maintenance Mode IP Whitelist
     |-----------------------------------------------------------------------------------------------
	 |
	 | Add the MAINTENANCE_IP_ADDRESSES="" variable in the /.env file,
	 | with IP addresses separated by commas
	 |
	 | example: MAINTENANCE_IP_ADDRESSES="127.0.0.1, ::1, 175.12.103.14"
     |
     */
	'maintenanceIpAddresses' => array_map('trim', explode(',', env('MAINTENANCE_IP_ADDRESSES') ?? '')),
	
	/*
     |-----------------------------------------------------------------------------------------------
     | IP Address Link Creation Base
     |-----------------------------------------------------------------------------------------------
	 |
	 | example: https://whatismyipaddress.com/ip/127.0.0.1
     |
     */
	'ipLinkBase' => env('IP_LINK_BASE', 'https://whatismyipaddress.com/ip/'),
	
	/*
     |-----------------------------------------------------------------------------------------------
     | API Parameters
     |-----------------------------------------------------------------------------------------------
	 |
	 | api.token: Token to authenticate each HTTP request
	 | api.client: Type of HTTP requests (can be: none, curl)
	 | - none: To consume the API by making internal HTTP requests (using Laravel sub requests)
	 | - curl: To consume the API by making external HTTP requests (using CURL)
	 | api.timeout: HTTP timeout during API calls (in seconds)
	 | api.retry.sleep: HTTP retry times during API calls
	 | api.retry.sleep: HTTP retry sleep during API calls (in milliseconds)
	 |
     */
	'api' => [
		'token'   => env('APP_API_TOKEN'),
		'client'  => env('APP_HTTP_CLIENT', 'none'),
		'timeout' => env('APP_API_TIMEOUT', 60),
		'retry' => [
			'times' => env('APP_API_RETRY_TIMES', 3),
			'sleep' => env('APP_API_RETRY_SLEEP', 2000),
		],
		// Optional
		// Pattern: host:port (or) ip:port - Example: proxy.example.tld:3128
		'proxy' => env('APP_API_PROXY'),
	],
	
	'web' => [
		// More info in: config/auth.php
		'guard' => env('AUTH_GUARD', 'web'),
	],
	
	/*
     |-----------------------------------------------------------------------------------------------
     | Packages Options
     |-----------------------------------------------------------------------------------------------
	 |
	 | package.type.promotion: The promotion packages are about Post
	 | package.type.promotion: The subscription packages are about User
	 |
     */
    'package' => [
	    'type' => [
		    'promotion'    => 'App\Models\Post',
		    'subscription' => 'App\Models\User',
	    ],
    ],
	
];
