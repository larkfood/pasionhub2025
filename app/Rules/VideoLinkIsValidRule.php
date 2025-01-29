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

namespace App\Rules;

use App\Helpers\VideoEmbedder;
use App\Helpers\VideoIdExtractor;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class VideoLinkIsValidRule implements ValidationRule
{
	public ?string $attrLabel = '';
	
	public function __construct($attrLabel = '')
	{
		$this->attrLabel = $attrLabel;
	}
	
	/**
	 * Run the validation rule.
	 */
	public function validate(string $attribute, mixed $value, Closure $fail): void
	{
		if (!$this->passes($attribute, $value)) {
			$fail($this->message());
		}
	}
	
	/**
	 * Determine if the validation rule passes.
	 *
	 * @param string $attribute
	 * @param mixed $value
	 * @return bool
	 */
	public function passes(string $attribute, mixed $value): bool
	{
		$value = trim(getAsString($value));
		$extracted = null;
		
		// Get the video standard link
		try {
			$extracted = VideoIdExtractor::extractId($value);
		} catch (\Throwable $e) {
			abort(500, $e->getMessage());
		}
		
		return (!empty($extracted) && !empty($extracted['videoId']));
	}
	
	/**
	 * Get the validation error message.
	 *
	 * @return string
	 */
	private function message(): string
	{
		// Get the videos embedding platforms
		$platforms = VideoEmbedder::getPlatforms();
		
		// Build the error message
		if (!empty($this->attrLabel)) {
			return trans('validation.video_link_is_valid_rule', [
				'attribute' => mb_strtolower($this->attrLabel),
				'platforms' => $platforms,
			]);
		} else {
			if (!empty($this->attr) && !empty(trans('validation.attributes.' . $this->attr))) {
				return trans('validation.video_link_is_valid_rule', [
					'attribute' => trans('validation.attributes.' . $this->attr),
					'platforms' => $platforms,
				]);
			} else {
				return trans('validation.video_link_is_valid_rule', ['platforms' => $platforms]);
			}
		}
	}
}
