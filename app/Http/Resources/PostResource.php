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

namespace App\Http\Resources;

use App\Enums\PostType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return array
	 */
	public function toArray(Request $request): array
	{
		if (!isset($this->id)) return [];
		
		$entity = [
			'id' => $this->id,
		];
		
		$columns = $this->getFillable();
		foreach ($columns as $column) {
			$entity[$column] = $this->{$column} ?? null;
			if ($column == 'title') {
				$entity['excerpt'] = $this->excerpt ?? null;
			}
		}
		
		$entity['reference'] = $this->reference ?? null;
		$entity['slug'] = $this->slug ?? null;
		$entity['url'] = $this->url ?? null;
		$entity['phone_intl'] = $this->phone_intl ?? null;
		$entity['created_at_formatted'] = $this->created_at_formatted ?? null;
		$entity['updated_at_formatted'] = $this->updated_at_formatted ?? null;
		$entity['archived_at_formatted'] = $this->archived_at_formatted ?? null;
		$entity['archived_manually_at_formatted'] = $this->archived_manually_at_formatted ?? null;
		$entity['user_photo_url'] = $this->user_photo_url ?? null;
		$entity['country_flag_url'] = $this->country_flag_url ?? null;
		$entity['price_label'] = $this->price_label ?? t('price');
		$entity['price_formatted'] = $this->price_formatted ?? null;
		$entity['visits_formatted'] = $this->visits_formatted ?? null;
		$entity['distance_info'] = $this->distance_info ?? null;
		$entity['count_pictures'] = $this->count_pictures ?? 0;
		
		// Main Picture
		$picture = $this->whenLoaded('picture');
		if (!empty($picture)) {
			$entity['picture'] = new PictureResource($picture);
		} else {
			$defaultPicture = config('larapen.media.picture');
			$defaultPictureUrl = thumbParam($defaultPicture)->url();
			$entity['picture'] = [
				'file_path' => $defaultPicture,
				'url'      => [
					'full'   => $defaultPictureUrl,
					'small'  => $defaultPictureUrl,
					'medium' => $defaultPictureUrl,
					'large'  => $defaultPictureUrl,
				],
			];
		}
		
		$embed = request()->filled('embed') ? explode(',', request()->input('embed')) : [];
		
		if (in_array('pictures', $embed)) {
			$entity['pictures'] = PictureResource::collection($this->whenLoaded('pictures'));
		}
		if (in_array('country', $embed)) {
			$entity['country'] = new CountryResource($this->whenLoaded('country'));
		}
		if (in_array('user', $embed)) {
			$entity['user'] = new UserResource($this->whenLoaded('user'));
		}
		if (in_array('category', $embed)) {
			$entity['category'] = new CategoryResource($this->whenLoaded('category'));
		}
		if (in_array('postType', $embed)) {
			if (!empty($this->post_type_id)) {
				$entity['postType'] = PostType::find($this->post_type_id);
			}
		}
		if (in_array('city', $embed)) {
			$entity['city'] = new CityResource($this->whenLoaded('city'));
		}
		if (in_array('currency', $embed)) {
			$entity['currency'] = new CurrencyResource($this->whenLoaded('currency'));
		}
		if (in_array('payment', $embed)) {
			$entity['payment'] = new PaymentResource($this->whenLoaded('payment'));
		}
		if (in_array('possiblePayment', $embed)) {
			$entity['possiblePayment'] = new PaymentResource($this->whenLoaded('possiblePayment'));
		}
		if (in_array('savedByLoggedUser', $embed)) {
			$entity['savedByLoggedUser'] = UserResource::collection($this->whenLoaded('savedByLoggedUser'));
		}
		
		// Reviews Plugin
		if (config('plugins.reviews.installed')) {
			$entity['rating_cache'] = $this->rating_cache ?? 0;
			$entity['rating_count'] = $this->rating_count ?? 0;
			// Warning: To prevent SQL queries in loop,
			// Never embed 'userRating' and 'countUserRatings' when collection will be returned
			if (in_array('userRating', $embed)) {
				$entity['p_user_rating'] = $this->userRating();
			}
			if (in_array('countUserRatings', $embed)) {
				$entity['p_count_user_ratings'] = $this->countUserRatings();
			}
		}
		
		if (isset($this->distance)) {
			$entity['distance'] = $this->distance;
		}
		
		// From SavedPostResource
		if (isset($this->saved_at_formatted)) {
			$entity['saved_at_formatted'] = $this->saved_at_formatted;
		}
		
		return $entity;
	}
}
