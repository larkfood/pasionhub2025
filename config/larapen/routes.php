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

$routes = [
	
	// Post
	'post' => '{slug}/{hashableId}',
	
	// Search
	'search' => 'search',
	'searchPostsByUserId' => 'users/{id}/ads',
	'searchPostsByUsername' => 'profile/{username}',
	'searchPostsByTag' => 'tag/{tag}',
	'searchPostsByCity' => 'location/{city}/{id}',
	'searchPostsBySubCat' => 'category/{catSlug}/{subCatSlug}',
	'searchPostsByCat' => 'category/{catSlug}',
	'searchPostsByCompanyId' => 'companies/{id}/ads',
	
	// Auth
	'login' => 'login',
	'logout' => 'logout',
	'register' => 'register',
	
	// Other Pages
	'companies' => 'companies',
	'pageBySlug' => 'page/{slug}',
	'sitemap' => 'sitemap',
	'countries' => 'countries',
	'contact' => 'contact',
	'pricing' => 'pricing',
	
];

if (config('settings.seo.multi_country_urls')) {
	
	$routes['search'] = '{countryCode}/search';
	$routes['searchPostsByUserId'] = '{countryCode}/users/{id}/ads';
	$routes['searchPostsByUsername'] = '{countryCode}/profile/{username}';
	$routes['searchPostsByTag'] = '{countryCode}/tag/{tag}';
	$routes['searchPostsByCity'] = '{countryCode}/location/{city}/{id}';
	$routes['searchPostsBySubCat'] = '{countryCode}/category/{catSlug}/{subCatSlug}';
	$routes['searchPostsByCat'] = '{countryCode}/category/{catSlug}';
	$routes['searchPostsByCompanyId'] = '{countryCode}/companies/{id}/ads';
	$routes['companies'] = '{countryCode}/companies';
	$routes['sitemap'] = '{countryCode}/sitemap';
	
}

// Post
$postPermalinks = (array)config('larapen.options.permalink.post');
if (in_array(config('settings.seo.listing_permalink', '{slug}/{hashableId}'), $postPermalinks)) {
	$routes['post'] = config('settings.seo.listing_permalink', '{slug}/{hashableId}') . config('settings.seo.listing_permalink_ext', '');
}

return $routes;
