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

use App\Helpers\Files\FileSys;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SupportedFilesRule implements ValidationRule
{
	protected string $typeGroup = 'file';
	protected bool $client = false;
	protected array $installedTypes = [];
	protected array $invalidTypes = [];
	
	/**
	 * Constructor to accept additional characters.
	 *
	 * @param string $typeGroup
	 * @param bool $client
	 */
	public function __construct(string $typeGroup = 'file', bool $client = false)
	{
		$this->typeGroup = $typeGroup;
		$this->client = $client;
		$this->installedTypes = ($typeGroup == 'image')
			? (
			$client
				? getClientInstalledImageFormats()
				: getServerInstalledImageFormats()
			)
			: [];
	}
	
	/**
	 * Run the validation rule.
	 */
	public function validate(string $attribute, mixed $value, Closure $fail): void
	{
		if (!$this->passes($attribute, $value)) {
			$and = t('_and_');
			$installedTypes = collect($this->installedTypes)->join(', ', $and);
			$invalidTypes = collect($this->invalidTypes)->join(', ', $and);
			
			if ($this->typeGroup == 'image') {
				$message = trans('validation.invalid_image_formats_rule', [
					'installedTypes' => $installedTypes,
					'invalidTypes'   => $invalidTypes,
				]);
			} else {
				$message = trans('validation.invalid_file_formats_rule', ['invalidTypes' => $invalidTypes]);
			}
			
			$fail($message);
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
		return !$this->hasInvalidFormat($value);
	}
	
	/**
	 * Check if value contains invalid image format(s).
	 *
	 * @param string $value
	 * @return bool
	 */
	protected function hasInvalidFormat(string $value): bool
	{
		$typeList = array_map('trim', explode(',', $value));
		
		foreach ($typeList as $item) {
			if ($this->typeGroup == 'image') {
				if (!in_array($item, $this->installedTypes)) {
					$this->invalidTypes[] = $item;
				}
			} else {
				$mimeType = FileSys::getExtensionMimeType($item);
				if (empty($mimeType)) {
					$this->invalidTypes[] = $item;
				}
			}
		}
		
		return !empty($this->invalidTypes);
	}
}
