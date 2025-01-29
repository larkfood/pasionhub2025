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
    |--------------------------------------------------------------------------
    | Default URIs
    |--------------------------------------------------------------------------
    |
    | 'default_uri' => Homepage
    | 'countries_list_uri' => Page that show the countries list
    */
    
    'default_uri' => '/',
    'countries_list_uri' => 'countries',
    
    
    /*
    |--------------------------------------------------------------------------
    | Cache and Cookies Expiration (Unused)
    |--------------------------------------------------------------------------
    | Value in seconds
    |
    | InMinute = 60; InHour = 3600; InDay = 86400; InWeek = 604800; InMonth = 2592000;
    */
    
    'cache_expire' => 3600,
    'cookie_expire' => 2592000,
    
    
    /*
    |--------------------------------------------------------------------------
    | Default Country (Unused)
    |--------------------------------------------------------------------------
    |
    | Use the countries ISO Code
    | E.g. Use 'BJ' for Benin.
    | Let this value empty to allow user to select a country if her IP not found or if her IP belong a banned country.
    */
    
    'default_country' => '',
    'show_country_flag' => true,

];
