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

namespace App\Services\Thumbnail;

use App\Models\Post;
use App\Models\Picture;

class PostThumbnail
{
	/**
	 * @param \App\Models\Post $post
	 * @return void
	 */
	public function generateFor(Post $post): void
	{
		if (empty($post->pictures)) return;
		
		foreach ($post->pictures as $picture) {
			$this->generateThumbnail($picture);
		}
	}
	
	/**
	 * @param $posts
	 * @return void
	 */
	public function generateForCollection($posts): void
	{
		if (empty($posts)) return;
		
		foreach ($posts as $post) {
			$post->loadMissing('picture');
			if (empty($post->picture)) continue;
			if (!$post->picture instanceof Picture) continue;
			
			$this->generateThumbnail($post->picture);
		}
	}
	
	/**
	 * @param \App\Models\Picture $picture
	 * @return void
	 */
	protected function generateThumbnail(Picture $picture): void
	{
		$columnsWithParams = $this->getModelThumbnailColumns($picture);
		if (empty($columnsWithParams)) return;
		
		foreach ($columnsWithParams as $columnParams) {
			$filePath = $columnParams['filePath'] ?? null;
			$webpFormat = $columnParams['webpFormat'] ?? false;
			
			thumbImage($filePath)->resize($columnParams, $webpFormat);
		}
	}
	
	/**
	 * Get the model's thumbnail columns and their parameters
	 *
	 * @param \App\Models\Picture $picture
	 * @return array
	 */
	protected function getModelThumbnailColumns(Picture $picture): array
	{
		$appendedColumns = $picture->getAppends();
		
		$attribute = 'file_path';
		
		$appendedColumns = collect($appendedColumns)
			->filter(function ($item) use ($attribute) {
				$attrBase = str($attribute)->replaceEnd('_path', '')->toString();
				
				return str_contains($item, $attrBase . '_url');
			})->mapWithKeys(function ($item, $key) use ($picture, $attribute) {
				$filePath = $picture->{$attribute} ?? null;
				
				$defaultResizeOptionName = 'picture-lg';
				$optionAlias = [
					'small'  => 'picture-sm',
					'medium' => 'picture-md',
					'large'  => 'picture-lg',
				];
				
				$resizeOptionAlias = str($item)->afterLast('_')->toString();
				$resizeOptionAlias = ($resizeOptionAlias != 'url') ? $resizeOptionAlias : null;
				$resizeOptionsName = $optionAlias[$resizeOptionAlias] ?? $defaultResizeOptionName;
				
				$webp = str($item)->before('_')->toString();
				$webpFormat = ($webp == 'webp');
				
				$params = thumbParam($filePath)->setOption($resizeOptionsName)->resizeParameters();
				$params['webpFormat'] = $webpFormat;
				
				return [$key => $params];
			})->toArray();
		
		return collect($appendedColumns)->toArray();
	}
}
