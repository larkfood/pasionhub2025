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

use App\Http\Resources\EntityCollection;
use App\Http\Resources\ReportTypeResource;
use App\Models\ReportType;

/**
 * @group Listings
 */
class ReportTypeController extends BaseController
{
	/**
	 * List report types
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index(): \Illuminate\Http\JsonResponse
	{
		$reportTypes = ReportType::query()->get();
		
		$resourceCollection = new EntityCollection(class_basename($this), $reportTypes);
		
		$message = ($reportTypes->count() <= 0) ? t('no_report_types_found') : null;
		
		return apiResponse()->withCollection($resourceCollection, $message);
	}
	
	/**
	 * Get report type
	 *
	 * @urlParam id int required The report type's ID. Example: 1
	 *
	 * @param $id
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show($id): \Illuminate\Http\JsonResponse
	{
		$reportType = ReportType::query()->where('id', $id);
		
		$reportType = $reportType->first();
		
		abort_if(empty($reportType), 404, t('report_type_not_found'));
		
		$resource = new ReportTypeResource($reportType);
		
		return apiResponse()->withResource($resource);
	}
}
