<?php

return [
	
	'paypal' => [
		'mode'         => env('PAYPAL_MODE', 'sandbox'),
		'clientId'     => env('PAYPAL_CLIENT_ID', ''),
		'clientSecret' => env('PAYPAL_CLIENT_SECRET', ''),
		
		/*
		 * Referrers' hosts
		 * Used to allow HTTP POST requests from this gateway to the core app when the CSRF protection is activated
		 */
		'referrersHosts' => ['paypal.com'],
	],
	
];
