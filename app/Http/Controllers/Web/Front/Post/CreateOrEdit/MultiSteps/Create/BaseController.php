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

namespace App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Create;

use App\Enums\PostType;
use App\Http\Controllers\Api\Payment\HasPaymentTrigger;
use App\Http\Controllers\Api\Payment\Promotion\SingleStepPayment;
use App\Http\Controllers\Web\Front\Auth\Traits\VerificationTrait;
use App\Http\Controllers\Web\Front\Payment\HasPaymentRedirection;
use App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\BaseController as MultiStepsBaseController;
use App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Create\Traits\ClearTmpInputTrait;
use App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Create\Traits\SubmitTrait;
use App\Http\Controllers\Web\Front\Post\CreateOrEdit\Traits\PricingPageUrlTrait;
use App\Observers\Traits\PictureTrait;
use Larapen\LaravelMetaTags\Facades\MetaTag;

class BaseController extends MultiStepsBaseController
{
	use VerificationTrait;
	use PricingPageUrlTrait;
	use PictureTrait, ClearTmpInputTrait;
	use SubmitTrait;
	use HasPaymentTrigger, SingleStepPayment, HasPaymentRedirection;
	
	protected string $baseUrl = '/posts/create';
	protected string $cfTmpUploadDir = 'temporary';
	protected string $tmpUploadDir = 'temporary';
	protected array $allowedQueries = ['packageId'];
	
	/**
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->commonQueries();
		
		$this->baseUrl = url($this->baseUrl);
		
		if (isPostCreationRequest()) {
			$this->shareNavItems();
		}
	}
	
	/**
	 * Get the middleware that should be assigned to the controller.
	 */
	public static function middleware(): array
	{
		$array = [];
		
		// Check if guests can post listings
		if (!doesGuestHaveAbilityToCreateListings()) {
			$array[] = 'auth';
		}
		
		return array_merge(parent::middleware(), $array);
	}
	
	/**
	 * @return void
	 */
	protected function commonQueries(): void
	{
		// Set payment settings for promotion packages (Single-Step Form)
		$this->setPaymentSettingsForPromotion();
		
		if (config('settings.listing_form.show_listing_type') == '1') {
			$postTypes = PostType::all();
			view()->share('postTypes', $postTypes);
		}
		
		if (request()->query('error') == 'paymentCancelled') {
			if (session()->has('postId')) {
				session()->forget('postId');
			}
		}
		
		// Meta Tags
		[$title, $description, $keywords] = getMetaTag('create');
		MetaTag::set('title', $title);
		MetaTag::set('description', strip_tags($description));
		MetaTag::set('keywords', $keywords);
	}
	
	/**
	 * @return string[]
	 */
	protected function unwantedFields(): array
	{
		return ['_token', 'entity_field', 'valid_field'];
	}
}
