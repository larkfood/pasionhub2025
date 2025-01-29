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
	
	// Cache Drivers
	'cache' => [
		'file'      => 'File (Default)',
		'database'  => 'Database',
		'apc'       => 'APC',
		'memcached' => 'Memcached',
		'redis'     => 'Redis',
		'array'     => 'None',
	],
	
	// Queue Drivers
	'queue' => [
		'sync'       => 'Sync (Default)',
		'database'   => 'Database',
		'redis'      => 'Redis',
		'sqs'        => 'Amazon SQS',
		// 'beanstalkd' => 'Beanstalkd',
	],
	
	// Mail Drivers
	'mail'   => [
		'sendmail'   => 'Sendmail',
		'smtp'       => 'SMTP',
		'mailgun'    => 'Mailgun',
		'postmark'   => 'Postmark',
		'ses'        => 'Amazon SES',
		'sparkpost'  => 'Sparkpost',
		'resend'     => 'Resend',
		'mailersend' => 'MailerSend',
		// 'brevo'   => 'Brevo',
	],
	
	// SMS Drivers
	'sms' => [
		'vonage' => 'Vonage',
		'twilio' => 'Twilio',
	],
	
	// GeoIP Drivers
	'geoip' => [
		'ipinfo'           => 'ipinfo.io',
		'dbip'             => 'db-ip.com',
		'ipbase'           => 'ipbase.com',
		'ip2location'      => 'ip2location.com',
		'ipapi'            => 'ip-api.com', // No API Key
		'ipapico'          => 'ipapi.co',   // No API Key
		'ipgeolocation'    => 'ipgeolocation.io',
		'iplocation'       => 'iplocation.net',
		'ipstack'          => 'ipstack.com',
		'maxmind_api'      => 'maxmind.com (Web Services)',
		'maxmind_database' => 'maxmind.com (Database)', // No API Key (But need to download DB)
	],
	
	// WYSIWYG Editor
	'wysiwyg' => [
		'none'       => 'None',
		'tinymce'    => 'TinyMCE',
		'ckeditor'   => 'CKEditor',
		'summernote' => 'Summernote',
		'simditor'   => 'Simditor',
	],
	
	// Permalinks & Extensions
	'permalink' => [
		'post' => [
			'{slug}-{hashableId}',
			'{slug}/{hashableId}',
			'{slug}_{hashableId}',
			'{hashableId}-{slug}',
			'{hashableId}/{slug}',
			'{hashableId}_{slug}',
			'{hashableId}',
		],
	],
	'permalinkExt' => [
		'',
		'.html',
		'.htm',
		'.php',
		'.asp',
		'.aspx',
		'.jsp',
	],

];
