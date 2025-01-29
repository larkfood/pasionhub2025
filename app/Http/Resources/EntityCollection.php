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

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EntityCollection extends ResourceCollection
{
	public string $entityResource;
	
	/**
	 * EntityCollection constructor.
	 *
	 * @param $controllerName
	 * @param $resource
	 */
	public function __construct($controllerName, $resource)
	{
		parent::__construct($resource);
		
		$this->entityResource = str($controllerName)->replaceLast('Controller', 'Resource')->toString();
		if (!str_starts_with($this->entityResource, '\\')) {
			$this->entityResource = '\\' . __NAMESPACE__ . '\\' . $this->entityResource;
		}
	}
	
	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return array
	 */
	public function toArray(Request $request): array
	{
		$collection = $this->collection->transform(function ($entity) {
			return new $this->entityResource($entity);
		});
		
		return [
			'data' => $collection,
		];
	}
}
