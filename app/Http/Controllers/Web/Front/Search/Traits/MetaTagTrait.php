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

namespace App\Http\Controllers\Web\Front\Search\Traits;

trait MetaTagTrait
{
	/**
	 * Get Search Meta Tags
	 *
	 * @param array|null $preSearch
	 * @return array
	 */
	public function getMetaTag(?array $preSearch = []): array
	{
		// Get the Location's right arguments
		$cityId = request()->input('l');
		$stateName = request()->input('r');
		$isStateRequested = (!empty($stateName) && empty($cityId));
		
		// ...
		
		$metaTag = [];
		
		[$title, $description, $keywords] = getMetaTag('search');
		
		// Get pre-search data
		$parentCat = data_get($preSearch, 'cat.parent');
		$category = data_get($preSearch, 'cat');
		
		$state = data_get($preSearch, 'admin'); // Admin. Division
		$city = data_get($preSearch, 'city');
		$location = $isStateRequested ? $state : $city;
		
		$user = data_get($preSearch, 'user');
		$tag = data_get($preSearch, 'tag');
		
		// Init.
		$fallbackTitle = '';
		$fallbackTitle .= t('classified_ads');
		
		// Keyword
		$keyword = request()->input('q');
		$keyword = is_string($keyword) ? $keyword : null;
		if (!empty($keyword)) {
			$fallbackTitle .= ' ' . t('for') . ' ';
			$fallbackTitle .= '"' . rawurldecode($keyword) . '"';
		}
		
		// Category
		if (!empty($category)) {
			[$title, $description, $keywords] = getMetaTag('searchCategory');
			$this->applyCategoryValue($category, $title, $description, $keywords, $fallbackTitle, $fallbackDescription);
			
			if (!empty($location) || !empty($user) || !empty($tag)) {
				[$title, $description, $keywords] = getMetaTag('search');
				$this->applyCategoryValue($category, $title, $description, $keywords, $fallbackTitle, $fallbackDescription);
				if (!empty($location)) {
					$this->applyLocationValue($location, $title, $description, $keywords, $fallbackTitle, $fallbackDescription);
				}
				if (!empty($user)) {
					$this->applyUserValue($user, $title, $description, $keywords, $fallbackTitle, $fallbackDescription);
				}
				if (!empty($tag)) {
					$this->applyTagValue($tag, $title, $description, $keywords, $fallbackTitle, $fallbackDescription);
				}
			}
		}
		
		// Location
		if (!empty($location)) {
			[$title, $description, $keywords] = getMetaTag('searchLocation');
			$this->applyLocationValue($location, $title, $description, $keywords, $fallbackTitle, $fallbackDescription);
			
			if (!empty($category) || !empty($user) || !empty($tag)) {
				[$title, $description, $keywords] = getMetaTag('search');
				$this->applyLocationValue($location, $title, $description, $keywords, $fallbackTitle, $fallbackDescription);
				if (!empty($category)) {
					$this->applyCategoryValue($category, $title, $description, $keywords, $fallbackTitle, $fallbackDescription);
				}
				if (!empty($user)) {
					$this->applyUserValue($user, $title, $description, $keywords, $fallbackTitle, $fallbackDescription);
				}
				if (!empty($tag)) {
					$this->applyTagValue($tag, $title, $description, $keywords, $fallbackTitle, $fallbackDescription);
				}
			}
		}
		
		// User
		if (!empty($user)) {
			[$title, $description, $keywords] = getMetaTag('searchProfile');
			$this->applyUserValue($user, $title, $description, $keywords, $fallbackTitle, $fallbackDescription);
			
			if (!empty($category) || !empty($location) || !empty($tag)) {
				[$title, $description, $keywords] = getMetaTag('search');
				$this->applyUserValue($user, $title, $description, $keywords, $fallbackTitle, $fallbackDescription);
				if (!empty($category)) {
					$this->applyCategoryValue($category, $title, $description, $keywords, $fallbackTitle, $fallbackDescription);
				}
				if (!empty($location)) {
					$this->applyLocationValue($location, $title, $description, $keywords, $fallbackTitle, $fallbackDescription);
				}
				if (!empty($tag)) {
					$this->applyTagValue($tag, $title, $description, $keywords, $fallbackTitle, $fallbackDescription);
				}
			}
		}
		
		// Tag
		if (!empty($tag)) {
			[$title, $description, $keywords] = getMetaTag('searchTag');
			$this->applyTagValue($tag, $title, $description, $keywords, $fallbackTitle, $fallbackDescription);
			
			if (!empty($category) || !empty($location) || !empty($user)) {
				[$title, $description, $keywords] = getMetaTag('search');
				$this->applyTagValue($tag, $title, $description, $keywords, $fallbackTitle, $fallbackDescription);
				if (!empty($category)) {
					$this->applyCategoryValue($category, $title, $description, $keywords, $fallbackTitle, $fallbackDescription);
				}
				if (!empty($location)) {
					$this->applyLocationValue($location, $title, $description, $keywords, $fallbackTitle, $fallbackDescription);
				}
				if (!empty($user)) {
					$this->applyUserValue($user, $title, $description, $keywords, $fallbackTitle, $fallbackDescription);
				}
			}
		}
		
		// Country
		$fallbackTitle .= ', ' . config('country.name');
		
		$title = replaceGlobalPatterns($title);
		$description = replaceGlobalPatterns($description);
		$keywords = mb_strtolower(replaceGlobalPatterns($keywords));
		
		$metaTag['title'] = !empty($title) ? $title : $fallbackTitle;
		$metaTag['description'] = !empty($description) ? $description : ($fallbackDescription ?? $fallbackTitle);
		$metaTag['keywords'] = $keywords;
		
		return array_values($metaTag);
	}
	
	/* PRIVATE METHODS */
	
	private function applyCategoryValue($cat, &$title, &$description, &$keywords, &$fallbackTitle, &$fallbackDescription): void
	{
		if (empty($cat)) {
			return;
		}
		
		$title = str_replace('{category.name}', data_get($cat, 'name'), $title);
		$title = str_replace('{category.title}', data_get($cat, 'seo_title'), $title);
		$description = str_replace('{category.name}', data_get($cat, 'name'), $description);
		$description = str_replace('{category.description}', data_get($cat, 'seo_description'), $description);
		$keywords = str_replace('{category.name}', mb_strtolower(data_get($cat, 'name')), $keywords);
		$keywords = str_replace('{category.keywords}', mb_strtolower(data_get($cat, 'seo_keywords')), $keywords);
		
		$fallbackTitle .= ' ' . data_get($cat, 'name');
		if (!empty(data_get($cat, 'seo_description'))) {
			$fallbackDescription = data_get($cat, 'seo_description') . ', ' . config('country.name');
		}
	}
	
	private function applyLocationValue($location, &$title, &$description, &$keywords, &$fallbackTitle, &$fallbackDescription): void
	{
		if (empty($location)) {
			return;
		}
		
		$title = str_replace('{location.name}', data_get($location, 'name'), $title);
		$description = str_replace('{location.name}', data_get($location, 'name'), $description);
		$keywords = str_replace('{location.name}', mb_strtolower(data_get($location, 'name')), $keywords);
		
		$fallbackTitle .= ' ' . t('in') . ' ';
		$fallbackTitle .= data_get($location, 'name');
		$fallbackDescription = t('listings_in_location', ['location' => data_get($location, 'name')])
			. ', ' . config('country.name')
			. '. ' . t('looking_for_product_or_service')
			. ' - ' . data_get($location, 'name')
			. ', ' . config('country.name');
	}
	
	private function applyUserValue($user, &$title, &$description, &$keywords, &$fallbackTitle, &$fallbackDescription): void
	{
		if (empty($user)) {
			return;
		}
		
		$title = str_replace('{profile.name}', data_get($user, 'name'), $title);
		$description = str_replace('{profile.name}', data_get($user, 'name'), $description);
		$keywords = str_replace('{profile.name}', mb_strtolower(data_get($user, 'name')), $keywords);
		
		$fallbackTitle .= ' ' . t('of') . ' ';
		$fallbackTitle .= data_get($user, 'name');
	}
	
	private function applyTagValue($tag, &$title, &$description, &$keywords, &$fallbackTitle, &$fallbackDescription): void
	{
		if (empty($tag)) {
			return;
		}
		
		$title = str_replace('{tag}', $tag, $title);
		$description = str_replace('{tag}', $tag, $description);
		$keywords = str_replace('{tag}', mb_strtolower($tag), $keywords);
		
		$fallbackTitle .= ' ' . t('for') . ' ';
		$fallbackTitle .= $tag . ' (' . t('Tag') . ')';
	}
}
