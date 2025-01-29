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

namespace App\Http\Requests\Front;

use App\Enums\PostType;
use App\Helpers\Num;
use App\Http\Requests\Front\PostRequest\CustomFieldRequest;
use App\Http\Requests\Front\PostRequest\LimitationCompliance;
use App\Http\Requests\Request;
use App\Http\Requests\Traits\HasCaptchaInput;
use App\Http\Requests\Traits\HasEmailInput;
use App\Http\Requests\Traits\HasPhoneInput;
use App\Models\Category;
use App\Models\City;
use App\Models\Package;
use App\Models\Picture;
use App\Rules\BetweenRule;
use App\Rules\BlacklistTitleRule;
use App\Rules\BlacklistWordRule;
use App\Rules\MbAlphanumericRule;
use App\Rules\SluggableRule;
use App\Rules\UniquenessOfPostRule;
use App\Services\RemoveFromString;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Number;
use Illuminate\Validation\Rule;

class PostRequest extends Request
{
	use HasEmailInput, HasPhoneInput, HasCaptchaInput;
	
	public static Collection $packages;
	public static Collection $paymentMethods;
	
	protected array $customFieldMessages = [];
	protected array $limitationComplianceMessages = [];
	
	/**
	 * Prepare the data for validation.
	 *
	 * @return void
	 */
	protected function prepareForValidation(): void
	{
		// Don't apply this to the Admin Panel
		if (isAdminPanel()) {
			return;
		}
		
		$input = $this->all();
		
		// title
		if ($this->filled('title')) {
			$input['title'] = $this->input('title');
			$input['title'] = singleLineStringCleaner($input['title']);
			$input['title'] = preventStringContainingOnlyNumericChars($input['title']);
			$input['title'] = RemoveFromString::contactInfo($input['title'], true);
		}
		
		// description
		if ($this->filled('description')) {
			$input['description'] = $this->input('description');
			$input['description'] = preventStringContainingOnlyNumericChars($input['description']);
			$input['description'] = htmlPurifierCleaner($input['description']);
			$input['description'] = RemoveFromString::contactInfo($input['description'], true);
		}
		
		// price
		if ($this->has('price')) {
			if ($this->filled('price')) {
				$input['price'] = $this->input('price');
				// If field's value contains only numbers and dot,
				// Then decimal separator is set as dot.
				if (preg_match('/^[\d.]*$/', $input['price'])) {
					$input['price'] = Num::formatForDb($input['price'], '.');
				} else {
					if ($this->filled('currency_decimal_separator')) {
						$input['price'] = Num::formatForDb($input['price'], $this->input('currency_decimal_separator'));
					} else {
						$input['price'] = Num::formatForDb($input['price'], config('currency.decimal_separator', '.'));
					}
				}
			} else {
				$input['price'] = null;
			}
		}
		
		// currency_code
		if ($this->filled('currency_code')) {
			$input['currency_code'] = $this->input('currency_code');
		} else {
			$input['currency_code'] = config('currency.code', 'USD');
		}
		
		// contact_name
		if ($this->filled('contact_name')) {
			$input['contact_name'] = singleLineStringCleaner($this->input('contact_name'));
			$input['contact_name'] = preventStringContainingOnlyNumericChars($input['contact_name']);
		}
		
		// auth_field
		$input['auth_field'] = getAuthField();
		
		// phone
		$input = $this->preparePhoneForValidation($this, $input);
		
		// city_name (needed to retrieve the city name for modal location)
		if ($this->filled('city_id')) {
			$cityId = $this->input('city_id', 0);
			$city = City::find($cityId);
			if (!empty($city)) {
				$cityName = data_get($city, 'name');
				
				$adminType = config('country.admin_type', 0);
				$relAdminType = (in_array($adminType, ['1', '2'])) ? $adminType : 1;
				$adminCode = data_get($city, 'subadmin' . $relAdminType . '_code', 0);
				$adminCode = data_get($city, 'subAdmin' . $relAdminType . '.code', $adminCode);
				$adminName = data_get($city, 'subAdmin' . $relAdminType . '.name');
				
				$input['admin_type'] = $adminType;
				$input['admin_code'] = $adminCode;
				$input['city_name'] = !empty($adminName) ? $cityName . ', ' . $adminName : $cityName;
			}
		}
		
		// tags
		if ($this->filled('tags')) {
			$input['tags'] = tagCleaner($this->input('tags'));
		}
		
		// is_permanent
		if ($this->filled('is_permanent')) {
			$input['is_permanent'] = $this->input('is_permanent');
			// For security purpose
			if (config('settings.listing_form.permanent_listings_enabled') == '0') {
				$input['is_permanent'] = 0;
			} else {
				if (config('settings.listing_form.permanent_listings_enabled') == '1' && $this->input('post_type_id') != 1) {
					$input['is_permanent'] = 0;
				}
				if (config('settings.listing_form.permanent_listings_enabled') == '2' && $this->input('post_type_id') != 2) {
					$input['is_permanent'] = 0;
				}
				if (config('settings.listing_form.permanent_listings_enabled') == '3' && $this->input('post_type_id') == 2) {
					$input['is_permanent'] = 1;
				}
			}
		} else {
			$input['is_permanent'] = 0;
		}
		
		request()->merge($input); // Required!
		$this->merge($input);
	}
	
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		$createMethods = ['POST', 'CREATE'];
		$updateMethods = ['PUT', 'PATCH', 'UPDATE'];
		
		$guard = getAuthGuard();
		$authFields = array_keys(getAuthFields());
		
		$rules = [];
		
		$rules['category_id'] = ['required', 'not_in:0', 'exists:categories,id'];
		if (config('settings.listing_form.show_listing_type')) {
			$rules['post_type_id'] = ['required', Rule::in(PostType::values())];
		}
		$rules['title'] = [
			'required',
			new BetweenRule(
				(int)config('settings.listing_form.title_min_length', 2),
				(int)config('settings.listing_form.title_max_length', 150)
			),
			new MbAlphanumericRule(),
			new SluggableRule(),
			new BlacklistTitleRule(),
		];
		if (config('settings.listing_form.enable_post_uniqueness')) {
			$rules['title'][] = new UniquenessOfPostRule();
		}
		$rules['description'] = [
			'required',
			new BetweenRule(
				(int)config('settings.listing_form.description_min_length', 5),
				(int)config('settings.listing_form.description_max_length', 6000)
			),
			new MbAlphanumericRule(),
			new BlacklistWordRule(),
		];
		if (config('settings.listing_form.price_mandatory') == '1') {
			if ($this->filled('category_id')) {
				$category = Category::find($this->input('category_id'));
				if (!empty($category)) {
					if ($category->type != 'not-salable') {
						$rules['price'] = ['required', 'numeric', 'gt:0'];
					}
				}
			}
		}
		$rules['contact_name'] = ['required', new BetweenRule(2, 200)];
		$rules['auth_field'] = ['required', Rule::in($authFields)];
		$rules['phone'] = ['max:30'];
		$rules['phone_country'] = ['required_with:phone'];
		$rules['city_id'] = ['required', 'not_in:0', 'exists:cities,id'];
		
		
		if (!auth($guard)->check()) {
			$rules['accept_terms'] = ['accepted'];
		}
		
		$isSingleStepFormEnabled = isSingleStepFormEnabled();
		
		// CREATE
		if (in_array($this->method(), $createMethods)) {
			// Apply this rules for the 'Single-Step Form' (Web & API requests)
			// Or for API requests whatever the form type (i.e.: Single or Multi Steps)
			if ($isSingleStepFormEnabled || isFromApi()) {
				
				// Pictures
				if ($this->file('pictures')) {
					$serverAllowedImageFormats = collect(getServerAllowedImageFormats())->join(',');
					
					$files = $this->file('pictures');
					foreach ($files as $key => $file) {
						if (!empty($file)) {
							$rules['pictures.' . $key] = [
								'file',
								'mimes:' . $serverAllowedImageFormats,
								'min:' . (int)config('settings.upload.min_image_size', 0),
								'max:' . (int)config('settings.upload.max_image_size', 1000),
							];
						}
					}
				} else {
					if (config('settings.listing_form.picture_mandatory')) {
						$rules['pictures'] = ['required'];
					}
				}
				
				if ($isSingleStepFormEnabled) {
					// Require 'package_id' if Packages are available
					$isPackageSelectionRequired = (
						isset(self::$packages, self::$paymentMethods)
						&& self::$packages->count() > 0
						&& self::$paymentMethods->count() > 0
					);
					if ($isPackageSelectionRequired) {
						$rules['package_id'] = ['required'];
						
						if ($this->has('package_id')) {
							$package = Package::find($this->input('package_id'));
							
							// Require 'payment_method_id' if the selected package's price > 0
							$isPaymentMethodSelectionRequired = (!empty($package) && $package->price > 0);
							if ($isPaymentMethodSelectionRequired) {
								$rules['payment_method_id'] = ['required', 'not_in:0'];
							}
						}
					}
				}
				
			}
			
			$rules = $this->captchaRules($rules);
		}
		
		// UPDATE
		if (in_array($this->method(), $updateMethods)) {
			if ($isSingleStepFormEnabled) {
				// Pictures
				if ($this->file('pictures')) {
					$serverAllowedImageFormats = collect(getServerAllowedImageFormats())->join(',');
					
					$files = $this->file('pictures');
					foreach ($files as $key => $file) {
						if (!empty($file)) {
							$rules['pictures.' . $key] = [
								'file',
								'mimes:' . $serverAllowedImageFormats,
								'min:' . (int)config('settings.upload.min_image_size', 0),
								'max:' . (int)config('settings.upload.max_image_size', 1000),
							];
						}
					}
				} else {
					if (config('settings.listing_form.picture_mandatory')) {
						$countPictures = Picture::where('post_id', $this->input('post_id'))->count();
						if ($countPictures <= 0) {
							$rules['pictures'] = ['required'];
						}
					}
				}
			}
		}
		
		// COMMON
		
		// Location
		if (config('settings.listing_form.city_selection') == 'select') {
			$adminType = config('country.admin_type', 0);
			if (in_array($adminType, ['1', '2'])) {
				$cityId = $this->integer('city_id');
				$isCityIdFilled = (!empty($cityId) && $cityId > 0);
				if (!$isCityIdFilled) {
					$rules['admin_code'] = ['required', 'not_in:0'];
				}
			}
		}
		
		$phoneIsEnabledAsAuthField = (config('settings.sms.enable_phone_as_auth_field') == '1');
		$phoneNumberIsRequired = ($phoneIsEnabledAsAuthField && $this->input('auth_field') == 'phone');
		
		// email
		$emailIsRequired = (!$phoneNumberIsRequired);
		if ($emailIsRequired) {
			$rules['email'][] = 'required';
		}
		$rules = $this->emailRules($rules);
		
		// phone
		if ($phoneNumberIsRequired) {
			$rules['phone'][] = 'required';
		}
		$rules = $this->phoneRules($rules);
		
		// Tags
		if ($this->filled('tags')) {
			$rules['tags.*'] = ['regex:' . tagRegexPattern(), new BlacklistWordRule()];
		}
		
		// Custom Fields
		if (!isFromApi()) {
			$customFieldRequest = new CustomFieldRequest();
			$rules = $rules + $customFieldRequest->rules();
			$this->customFieldMessages = $customFieldRequest->messages();
		}
		
		// Posts Limitation Compliance
		if (in_array($this->method(), $createMethods)) {
			$limitationComplianceRequest = new LimitationCompliance();
			$rules = $rules + $limitationComplianceRequest->rules();
			$this->limitationComplianceMessages = $limitationComplianceRequest->messages();
		}
		
		return $rules;
	}
	
	/**
	 * Get custom attributes for validator errors.
	 *
	 * @return array
	 */
	public function attributes(): array
	{
		$attributes = [];
		
		if ($this->file('pictures')) {
			$files = $this->file('pictures');
			foreach ($files as $key => $file) {
				$fileIndex = ($key + 1);
				$filename = $file->getClientOriginalName() ?? $fileIndex;
				$attributes['pictures.' . $key] = t('picture_x', ['name' => $filename]);
			}
		}
		
		if ($this->filled('tags')) {
			$tags = $this->input('tags');
			if (is_array($tags) && !empty($tags)) {
				foreach ($tags as $key => $tag) {
					$attributes['tags.' . $key] = t('tag_x', ['key' => ($key + 1)]);
				}
			}
		}
		
		return $attributes;
	}
	
	/**
	 * @return array
	 */
	public function messages(): array
	{
		$messages = [];
		
		// Category & Sub-Category
		if ($this->filled('parent_id') && !empty($this->input('parent_id'))) {
			$messages['category_id.required'] = t('The field is required', ['field' => mb_strtolower(t('sub_category'))]);
			$messages['category_id.not_in'] = t('The field is required', ['field' => mb_strtolower(t('sub_category'))]);
		}
		
		if (isSingleStepFormEnabled()) {
			// Picture
			if ($this->file('pictures')) {
				$files = $this->file('pictures');
				foreach ($files as $key => $file) {
					// uploaded
					$fileIndex = ($key + 1);
					$filename = $file->getClientOriginalName() ?? $fileIndex;
					$maxSize = (int)config('settings.upload.max_image_size', 1000); // In KB
					$maxSize = $maxSize * 1024;                                     // Convert KB to Bytes
					$msg = t('large_file_uploaded_error', [
						'field'   => t('picture_x', ['name' => $filename]),
						'maxSize' => Number::fileSize($maxSize),
					]);
					
					$uploadMaxFilesizeStr = @ini_get('upload_max_filesize');
					$postMaxSizeStr = @ini_get('post_max_size');
					if (!empty($uploadMaxFilesizeStr) && !empty($postMaxSizeStr)) {
						$uploadMaxFilesize = forceToInt($uploadMaxFilesizeStr);
						$postMaxSize = forceToInt($postMaxSizeStr);
						
						$serverMaxSize = min($uploadMaxFilesize, $postMaxSize);
						$serverMaxSize = $serverMaxSize * 1024 * 1024; // Convert MB to KB to Bytes
						if ($serverMaxSize < $maxSize) {
							$msg = t('large_file_uploaded_error_system', [
								'field'   => t('picture_x', ['name' => $filename]),
								'maxSize' => Number::fileSize($serverMaxSize),
							]);
						}
					}
					
					$messages['pictures.' . $key . '.uploaded'] = $msg;
				}
			}
			
			// Package & PaymentMethod
			$messages['package_id.required'] = trans('validation.required_package_id');
			$messages['payment_method_id.required'] = t('validation.required_payment_method_id');
			$messages['payment_method_id.not_in'] = t('validation.required_payment_method_id');
		}
		
		// Custom Fields
		if (!isFromApi()) {
			$messages = $messages + $this->customFieldMessages;
		}
		
		// Posts Limitation Compliance
		$messages = $messages + $this->limitationComplianceMessages;
		
		return $messages;
	}
}
