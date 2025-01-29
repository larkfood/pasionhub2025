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

namespace App\Http\Controllers\Api;

// Increase the server resources
$iniConfigFile = __DIR__ . '/../../../Helpers/Functions/ini.php';
if (file_exists($iniConfigFile)) {
	$configForUpload = true;
	include_once $iniConfigFile;
}

use App\Helpers\Arr;
use App\Http\Controllers\Api\Auth\Traits\VerificationTrait;
use App\Http\Controllers\Api\Payment\HasPaymentTrigger;
use App\Http\Controllers\Api\Payment\Promotion\SingleStepPayment;
use App\Http\Controllers\Api\Picture\SingleStepPictures;
use App\Http\Controllers\Api\Post\UpdateTrait;
use App\Http\Controllers\Api\Post\StoreTrait;
use App\Http\Controllers\Api\Post\ListTrait;
use App\Http\Controllers\Api\Post\ShowTrait;
use App\Http\Requests\Front\PostRequest;
use App\Models\Post;
use App\Models\Scopes\ReviewedScope;
use App\Models\Scopes\VerifiedScope;
use App\Notifications\PostDeleted;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Notification;
use NotificationChannels\Twilio\TwilioChannel;

/**
 * @group Listings
 */
class PostController extends BaseController
{
	use VerificationTrait;
	
	use ListTrait;
	use ShowTrait;
	use StoreTrait;
	use UpdateTrait;
	
	use SingleStepPictures;
	use SingleStepPayment, HasPaymentTrigger;
	
	/**
	 * Get the middleware that should be assigned to the controller.
	 */
	public static function middleware(): array
	{
		$array = [];
		
		// Check if guests can post listings
		if (!doesGuestHaveAbilityToCreateListings()) {
			$array[] = new Middleware('auth', only: ['store']);
		}
		
		return array_merge(parent::middleware(), $array);
	}
	
	/**
	 * List listings
	 *
	 * Note: The main picture of the listings is fetched via a 'picture' attribute (added as fake column),
	 * that provide default picture as image placeholder when the listing has no pictures.
	 * In addition, for performance reasons, default picture is also provided when the 'pictures' table is not embed in the endpoint.
	 * So you need to embed the picture table like: /api/posts?embed=pictures to retrieve right main picture data.
	 *
	 * @queryParam op string Type of listings list (optional) - Possible value: search,premium,latest,free,premiumFirst,similar. Example: null
	 * @queryParam postId int Base Listing's ID to get similar listings (optional) - Mandatory to get similar listings (when op=similar). Example: null
	 * @queryParam distance int Distance to get similar listings (optional) - Also optional when the type of similar listings is based on the current listing's category. Mandatory when the type of similar listings is based on the current listing's location. So, its usage is limited to get similar listings (when op=similar) based on the current listing's location. Example: null
	 * @queryParam belongLoggedUser boolean Force users to be logged to get data that belongs to him. Authentication token needs to be sent in the header, and the "op" parameter needs to be null or unset - Possible value: 0 or 1.
	 * @queryParam pendingApproval boolean To list a user's listings in pending approval. Authentication token needs to be sent in the header, and the "op" parameter needs to be null or unset - Possible value: 0 or 1.
	 * @queryParam archived boolean To list a user's archived listings. Authentication token needs to be sent in the header, and the "op" parameter need be null or unset - Possible value: 0 or 1.
	 * @queryParam embed string Comma-separated list of the post relationships for Eager Loading - Possible values: user,category,parent,postType,city,currency,savedByLoggedUser,pictures,payment,package. Example: null
	 * @queryParam sort string The sorting parameter (Order by DESC with the given column. Use "-" as prefix to order by ASC). Possible values: created_at. Example: created_at
	 * @queryParam perPage int Items per page. Can be defined globally from the admin settings. Cannot be exceeded 100. Example: 2
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index(): \Illuminate\Http\JsonResponse
	{
		// Advanced Query (Query with the 'op' parameter)
		$searchOptions = ['search', 'premium', 'latest', 'free', 'premiumFirst'];
		
		$op = request()->input('op');
		$op = is_string($op) ? $op : null;
		
		if (in_array($op, $searchOptions)) {
			return $this->getPostsBySearch($op);
		}
		if ($op == 'similar') {
			return $this->getSimilarPosts();
		}
		
		return $this->getPostsList();
	}
	
	/**
	 * Get listing
	 *
	 * Note: The main picture of the listing is fetched via a 'picture' attribute (added as fake column),
	 * that provide default picture as image placeholder when the listing has no pictures.
	 * In addition, for performance reasons, default picture is also provided when the 'pictures' table is not embed in the endpoint.
	 * So you need to embed the picture table like: /api/posts/1?embed=pictures to retrieve right main picture data.
	 *
	 * @queryParam unactivatedIncluded boolean Include or not unactivated entries - Possible value: 0 or 1. Example: 1
	 * @queryParam belongLoggedUser boolean Force users to be logged to get data that belongs to him - Possible value: 0 or 1. Example: 0
	 * @queryParam noCache boolean Disable the cache for this request - Possible value: 0 or 1. Example: 0
	 * @queryParam embed string Comma-separated list of the post relationships for Eager Loading - Possible values: user,category,parent,postType,city,currency,savedByLoggedUser,pictures,payment,package,fieldsValues.Example: user,postType
	 * @queryParam detailed boolean Allow getting the listing's details with all its relationships (No need to set the 'embed' parameter). Example: false
	 *
	 * @urlParam id int required The post/listing's ID. Example: 2
	 *
	 * @param $id
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show($id): \Illuminate\Http\JsonResponse
	{
		$isDetailed = (request()->filled('detailed') && request()->integer('detailed') == 1);
		if ($isDetailed) {
			$defaultEmbed = [
				'user',
				'category',
				'parent',
				'postType',
				'city',
				'subAdmin1',
				'currency',
				'savedByLoggedUser',
				'picture',
				'pictures',
				'payment',
				'package',
			];
			if (request()->has('embed')) {
				$embed = explode(',', request()->input('embed', ''));
				$embed = array_merge($defaultEmbed, $embed);
				request()->query->set('embed', implode(',', $embed));
			} else {
				request()->query->add(['embed' => implode(',', $defaultEmbed)]);
			}
			
			return $this->showDetailedPost($id);
		}
		
		return $this->showPost($id);
	}
	
	/**
	 * Store listing
	 *
	 * For both types of listing's creation (Single step or Multi steps).
	 * Note: The field 'admin_code' is only available when the listing's country's 'admin_type' column is set to 1 or 2.
	 *
	 * @authenticated
	 * @header Authorization Bearer {YOUR_AUTH_TOKEN}
	 *
	 * @bodyParam country_code string required The code of the user's country. Example: US
	 * @bodyParam category_id int required The category's ID. Example: 1
	 * @bodyParam post_type_id int The listing type's ID. Example: 1
	 * @bodyParam title string required The listing's title. Example: John Doe
	 * @bodyParam description string required The listing's description. Example: Beatae placeat atque tempore consequatur animi magni omnis.
	 * @bodyParam contact_name string required The listing's author name. Example: John Doe
	 * @bodyParam auth_field string required The user's auth field ('email' or 'phone'). Example: email
	 * @bodyParam email string The listing's author email address (Required when 'auth_field' value is 'email'). Example: john.doe@domain.tld
	 * @bodyParam phone string The listing's author mobile number (Required when 'auth_field' value is 'phone'). Example: +17656766467
	 * @bodyParam phone_country string required The user's phone number's country code (Required when the 'phone' field is filled). Example: null
	 * @bodyParam admin_code string The administrative division's code. Example: 0
	 * @bodyParam city_id int required The city's ID.
	 * @bodyParam price int required The price. Example: 5000
	 * @bodyParam negotiable boolean Negotiable price or no. Example: 0
	 * @bodyParam phone_hidden boolean Mobile phone number will be hidden in public or no. Example: 0
	 * @bodyParam create_from_ip string The listing's author IP address. Example: 127.0.0.1
	 * @bodyParam accept_marketing_offers boolean Accept to receive marketing offers or no.
	 * @bodyParam is_permanent boolean Is it permanent post or no.
	 * @bodyParam tags string Comma-separated tags list. Example: car,automotive,tesla,cyber,truck
	 * @bodyParam accept_terms boolean required Accept the website terms and conditions. Example: 0
	 * @bodyParam pictures file[] required The listing's pictures.
	 * @bodyParam package_id int required The package's ID. Example: 2
	 * @bodyParam payment_method_id int The payment method's ID (required when the selected package's price is > 0). Example: 5
	 * @bodyParam captcha_key string Key generated by the CAPTCHA endpoint calling (Required when the CAPTCHA verification is enabled from the Admin panel).
	 *
	 * @param \App\Http\Requests\Front\PostRequest $request
	 * @return array|\Illuminate\Http\JsonResponse|mixed
	 */
	public function store(PostRequest $request)
	{
		$this->setPaymentSettingsForPromotion();
		
		return $this->storePost($request);
	}
	
	/**
	 * Update listing
	 *
	 * Note: The fields 'pictures', 'package_id' and 'payment_method_id' are only available with the single step listing edition.
	 * The field 'admin_code' is only available when the listing's country's 'admin_type' column is set to 1 or 2.
	 *
	 * @authenticated
	 * @header Authorization Bearer {YOUR_AUTH_TOKEN}
	 *
	 * @bodyParam country_code string required The code of the user's country. Example: US
	 * @bodyParam category_id int required The category's ID. Example: 1
	 * @bodyParam post_type_id int The listing type's ID. Example: 1
	 * @bodyParam title string required The listing's title. Example: John Doe
	 * @bodyParam description string required The listing's description. Example: Beatae placeat atque tempore consequatur animi magni omnis.
	 * @bodyParam contact_name string required The listing's author name. Example: John Doe
	 * @bodyParam auth_field string required The user's auth field ('email' or 'phone'). Example: email
	 * @bodyParam email string The listing's author email address (Required when 'auth_field' value is 'email'). Example: john.doe@domain.tld
	 * @bodyParam phone string The listing's author mobile number (Required when 'auth_field' value is 'phone'). Example: +17656766467
	 * @bodyParam phone_country string required The user's phone number's country code (Required when the 'phone' field is filled). Example: null
	 * @bodyParam admin_code string The administrative division's code. Example: 0
	 * @bodyParam city_id int required The city's ID.
	 * @bodyParam price int required The price. Example: 5000
	 * @bodyParam negotiable boolean Negotiable price or no. Example: 0
	 * @bodyParam phone_hidden boolean Mobile phone number will be hidden in public or no. Example: 0
	 * @bodyParam latest_update_ip string The listing's author IP address. Example: 127.0.0.1
	 * @bodyParam accept_marketing_offers boolean Accept to receive marketing offers or no.
	 * @bodyParam is_permanent boolean Is it permanent post or no.
	 * @bodyParam tags string Comma-separated tags list. Example: car,automotive,tesla,cyber,truck
	 * @bodyParam accept_terms boolean required Accept the website terms and conditions. Example: 0
	 *
	 * @bodyParam pictures file[] required The listing's pictures.
	 * @bodyParam package_id int required The package's ID. Example: 2
	 * @bodyParam payment_method_id int The payment method's ID (Required when the selected package's price is > 0). Example: 5
	 *
	 * @urlParam id int required The post/listing's ID.
	 *
	 * @param $id
	 * @param \App\Http\Requests\Front\PostRequest $request
	 * @return array|\Illuminate\Http\JsonResponse|mixed
	 */
	public function update($id, PostRequest $request)
	{
		// Single-Step Form
		if (isSingleStepFormEnabled()) {
			$this->setPaymentSettingsForPromotion();
			
			return $this->singleStepFormUpdate($id, $request);
		}
		
		return $this->multiStepsFormUpdate($id, $request);
	}
	
	/**
	 * Delete listing(s)
	 *
	 * @authenticated
	 * @header Authorization Bearer {YOUR_AUTH_TOKEN}
	 *
	 * @urlParam ids string required The ID or comma-separated IDs list of listing(s).
	 *
	 * @param string $ids
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function destroy(string $ids): \Illuminate\Http\JsonResponse
	{
		$authUser = auth('sanctum')->user();
		if (empty($authUser)) {
			return apiResponse()->unauthorized();
		}
		
		$data = [
			'success' => false,
			'message' => t('no_deletion_is_done'),
			'result'  => null,
		];
		
		$extra = [];
		
		// Get Entries ID (IDs separated by comma accepted)
		$ids = explode(',', $ids);
		
		// Delete
		$res = false;
		foreach ($ids as $postId) {
			$post = Post::withoutGlobalScopes([VerifiedScope::class, ReviewedScope::class])
				->where('user_id', $authUser->getAuthIdentifier())
				->where('id', $postId)
				->first();
			
			if (!empty($post)) {
				$tmpPost = Arr::toObject($post->toArray());
				
				// Delete Entry
				$res = $post->delete();
				
				// Send an Email or SMS confirmation
				$emailNotificationCanBeSent = (config('settings.mail.confirmation') == '1' && !empty($tmpPost->email));
				$smsNotificationCanBeSent = (
					config('settings.sms.enable_phone_as_auth_field') == '1'
					&& config('settings.sms.confirmation') == '1'
					&& $tmpPost->auth_field == 'phone'
					&& !empty($tmpPost->phone)
					&& !isDemoDomain()
				);
				try {
					if ($emailNotificationCanBeSent) {
						Notification::route('mail', $tmpPost->email)->notify(new PostDeleted($tmpPost));
					}
					if ($smsNotificationCanBeSent) {
						$smsChannel = (config('settings.sms.driver') == 'twilio')
							? TwilioChannel::class
							: 'vonage';
						Notification::route($smsChannel, $tmpPost->phone)->notify(new PostDeleted($tmpPost));
					}
				} catch (\Throwable $e) {
					$extra['mail']['success'] = false;
					$extra['mail']['message'] = getExceptionMessage($e);
				}
			}
		}
		
		// Confirmation
		if ($res) {
			$data['success'] = true;
			
			$count = count($ids);
			if ($count > 1) {
				$data['message'] = t('x entities have been deleted successfully', ['entities' => t('listings'), 'count' => $count]);
			} else {
				$data['message'] = t('1 entity has been deleted successfully', ['entity' => t('listing')]);
			}
		}
		
		$data['extra'] = $extra;
		
		return apiResponse()->json($data);
	}
}
