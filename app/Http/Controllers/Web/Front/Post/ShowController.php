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

namespace App\Http\Controllers\Web\Front\Post;

use App\Http\Controllers\Web\Front\Post\Traits\CatBreadcrumbTrait;
use App\Http\Controllers\Web\Front\Post\Traits\ReviewsPlugin;
use App\Models\Package;
use App\Http\Controllers\Web\Front\FrontController;
use Larapen\LaravelMetaTags\Facades\MetaTag;

class ShowController extends FrontController
{
	use CatBreadcrumbTrait, ReviewsPlugin;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->commonQueries();
	}
	
	/**
	 * @return void
	 */
	public function commonQueries(): void
	{
		// Count Packages
		$countPackages = Package::applyCurrency()->count();
		view()->share('countPackages', $countPackages);
		
		// Count Payment Methods
		view()->share('countPaymentMethods', $this->countPaymentMethods);
	}
	
	/**
	 * Show the Post's Details.
	 *
	 * @param $postId
	 * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
	 * @throws \Exception
	 */
	public function index($postId)
	{
		// Get and Check the Controller's Method Parameters
		$parameters = request()->route()->parameters();
		
		// Check if the Listing's ID key exists
		$idKey = array_key_exists('hashableId', $parameters) ? 'hashableId' : 'id';
		$idKeyDoesNotExist = (
			empty($parameters[$idKey])
			|| (!isHashedId($parameters[$idKey]) && !is_numeric($parameters[$idKey]))
		);
		
		// Show 404 error if the Listing's ID key cannot be found
		abort_if($idKeyDoesNotExist, 404);
		
		// Set the Parameters
		$postId = $parameters[$idKey];
		$slug = $parameters['slug'] ?? null;
		
		// Forcing redirection 301 for hashed (or non-hashed) ID to update links in search engine indexes
		if (config('settings.seo.listing_hashed_id_seo_redirection')) {
			if (config('settings.seo.listing_hashed_id_enabled') && !isHashedId($postId) && is_numeric($postId)) {
				// Don't lose important notification, so we need to persist your flash data for the request (the redirect request)
				request()->session()->reflash();
				
				$uri = urlGen()->postPathBasic(hashId($postId), $slug);
				
				return redirect()->to($uri, 301)->withHeaders(config('larapen.core.noCacheHeaders'));
			}
			if (!config('settings.seo.listing_hashed_id_enabled') && isHashedId($postId) && !is_numeric($postId)) {
				// Don't lose important notification, so we need to persist your flash data for the request (the redirect request)
				request()->session()->reflash();
				
				$uri = urlGen()->postPathBasic(hashId($postId, true), $slug);
				
				return redirect()->to($uri, 301)->withHeaders(config('larapen.core.noCacheHeaders'));
			}
		}
		
		// Decode Hashed ID
		$postId = hashId($postId, true) ?? $postId;
		
		// Call API endpoint
		$endpoint = '/posts/' . $postId;
		$queryParams = [
			'detailed' => 1,
		];
		if (config('plugins.reviews.installed')) {
			$queryParams['embed'] = 'userRating,countUserRatings';
		}
		$queryParams = array_merge(request()->all(), $queryParams);
		$headers = session()->has('isPostVisited') ? ['X-VISITED-BY-SAME-SESSION' => $postId] : [];
		$data = makeApiRequest(method: 'get', uri: $endpoint, data: $queryParams, headers: $headers);
		
		$message = $this->handleHttpError($data);
		$post = data_get($data, 'result');
		$customFields = data_get($data, 'extra.fieldsValues');
		
		// Listing isn't found
		abort_if(empty($post), 404, $message ?? t('post_not_found'));
		
		session()->put('isPostVisited', $postId);
		
		// Get post's pictures
		$pictures = (array)data_get($post, 'pictures');
		
		// Get possible post's registered Author (User)
		$user = data_get($post, 'user');
		
		// Get post's user decision about comments activation
		$commentsAreDisabledByUser = (data_get($user, 'disable_comments') == 1);
		
		// Category Breadcrumb
		$catBreadcrumb = $this->getCatBreadcrumb(data_get($post, 'category'), 1);
		
		// GET SIMILAR POSTS
		$widgetSimilarPosts = $this->similarPosts(data_get($post, 'id'));
		
		$isFromPostDetails = currentRouteActionContains('Post\ShowController');
		
		// Meta Tags
		[$title, $description, $keywords] = getMetaTag('listingDetails');
		$title = str_replace('{ad.title}', data_get($post, 'title'), $title);
		$title = str_replace('{location.name}', data_get($post, 'city.name'), $title);
		$description = str_replace('{ad.description}', str(normalizeWhitespace(strip_tags(data_get($post, 'description'))))->limit(200), $description);
		$keywords = str_replace('{ad.tags}', str_replace(',', ', ', @implode(',', data_get($post, 'tags'))), $keywords);
		
		$title = removeUnmatchedPatterns($title);
		$description = removeUnmatchedPatterns($description);
		$keywords = removeUnmatchedPatterns($keywords);
		
		// Fallback
		if (empty($title)) {
			$title = data_get($post, 'title') . ', ' . data_get($post, 'city.name');
		}
		if (empty($description)) {
			$description = str(normalizeWhitespace(strip_tags(data_get($post, 'description'))))->limit(200);
		}
		
		MetaTag::set('title', $title);
		MetaTag::set('description', $description);
		MetaTag::set('keywords', $keywords);
		
		// Open Graph
		$this->og->title($title)->description($description)->type('article');
		if (!empty($pictures)) {
			if ($this->og->has('image')) {
				$this->og->forget('image')->forget('image:width')->forget('image:height');
			}
			
			foreach ($pictures as $picture) {
				$pictureUrl = data_get($picture, 'url.large');
				$this->og->image($pictureUrl, [
					'width'  => (int)config('settings.social_share.og_image_width', 1200),
					'height' => (int)config('settings.social_share.og_image_height', 630),
				]);
			}
		}
		view()->share('og', $this->og);
		
		// Reviews Plugin Data
		if (config('plugins.reviews.installed')) {
			$reviewsApiResult = $this->getReviews(data_get($post, 'id'));
			view()->share('reviewsApiResult', $reviewsApiResult);
		}
		
		return view(
			'front.post.show.index',
			compact(
				'post',
				'pictures',
				'user',
				'catBreadcrumb',
				'customFields',
				'commentsAreDisabledByUser',
				'widgetSimilarPosts',
				'isFromPostDetails'
			)
		);
	}
	
	/**
	 * @param $postId
	 * @return array|null
	 */
	public function similarPosts($postId): ?array
	{
		$post = null;
		$posts = [];
		$totalPosts = 0;
		$widgetSimilarPosts = null;
		$message = null;
		
		// GET SIMILAR POSTS
		if (in_array(config('settings.listing_page.similar_listings'), ['1', '2'])) {
			// Call API endpoint
			$endpoint = '/posts';
			$queryParams = [
				'op'       => 'similar',
				'postId'   => $postId,
				'distance' => 50, // km OR miles
			];
			$queryParams = array_merge(request()->all(), $queryParams);
			$headers = [
				'X-WEB-CONTROLLER' => class_basename(get_class($this)),
			];
			$data = makeApiRequest(method: 'get', uri: $endpoint, data: $queryParams, headers: $headers);
			
			$message = data_get($data, 'message');
			$posts = data_get($data, 'result.data');
			$totalPosts = data_get($data, 'extra.count.0');
			$post = data_get($data, 'extra.preSearch.post');
		}
		
		if (config('settings.listing_page.similar_listings') == '1') {
			// Featured Area Data
			$widgetSimilarPosts = [
				'title'      => t('Similar Listings'),
				'link'       => urlGen()->category(data_get($post, 'category')),
				'posts'      => $posts,
				'totalPosts' => $totalPosts,
				'message'    => $message,
			];
			$widgetSimilarPosts = ($totalPosts > 0) ? $widgetSimilarPosts : null;
		} else if (config('settings.listing_page.similar_listings') == '2') {
			$distance = 50; // km OR miles
			
			// Featured Area Data
			$widgetSimilarPosts = [
				'title'      => t('more_listings_at_x_distance_around_city', [
					'distance' => $distance,
					'unit'     => getDistanceUnit(config('country.code')),
					'city'     => data_get($post, 'city.name'),
				]),
				'link'       => urlGen()->city(data_get($post, 'city')),
				'posts'      => $posts,
				'totalPosts' => $totalPosts,
				'message'    => $message,
			];
			$widgetSimilarPosts = ($totalPosts > 0) ? $widgetSimilarPosts : null;
		}
		
		return $widgetSimilarPosts;
	}
}
