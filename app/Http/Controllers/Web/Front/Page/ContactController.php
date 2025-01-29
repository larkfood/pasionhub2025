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

namespace App\Http\Controllers\Web\Front\Page;

use App\Http\Controllers\Web\Front\FrontController;
use App\Http\Requests\Front\ContactRequest;
use Larapen\LaravelMetaTags\Facades\MetaTag;

class ContactController extends FrontController
{
	/**
	 * @return \Illuminate\Contracts\View\View
	 */
	public function getForm()
	{
		$city = null;
		if (config('services.googlemaps.key')) {
			// Get the Country's largest city for Google Maps
			// Call API endpoint
			$endpoint = '/countries/' . config('country.code') . '/cities';
			$queryParams = ['firstOrderByPopulation' => 'desc'];
			$data = makeApiRequest(method: 'get', uri: $endpoint, data: $queryParams);
			
			$message = $this->handleHttpError($data);
			$city = data_get($data, 'result');
		}
		
		// Meta Tags
		[$title, $description, $keywords] = getMetaTag('contact');
		MetaTag::set('title', $title);
		MetaTag::set('description', strip_tags($description));
		MetaTag::set('keywords', $keywords);
		
		return view('front.pages.contact', compact('city'));
	}
	
	/**
	 * @param \App\Http\Requests\Front\ContactRequest $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function postForm(ContactRequest $request)
	{
		// Add required data in the request for API
		$request->merge([
			'country_code' => config('country.code'),
			'country_name' => config('country.name'),
		]);
		
		// Call API endpoint
		$endpoint = '/contact';
		$data = makeApiRequest(method: 'post', uri: $endpoint, data: $request->all());
		
		// Parsing the API response
		$message = data_get($data, 'message', t('unknown_error'));
		
		// HTTP Error Found
		if (!data_get($data, 'isSuccessful')) {
			return back()->withErrors(['error' => $message])->withInput();
		}
		
		// Notification Message
		if (data_get($data, 'success')) {
			flash($message)->success();
		} else {
			flash($message)->error();
		}
		
		return redirect()->to(urlGen()->contact());
	}
}
