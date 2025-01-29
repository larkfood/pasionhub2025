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

namespace App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps;

use App\Http\Controllers\Api\Payment\HasPaymentReferrers;
use App\Http\Controllers\Web\Front\FrontController;
use App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Traits\WizardTrait;
use Illuminate\Support\Collection;

class BaseController extends FrontController
{
	use WizardTrait;
	use HasPaymentReferrers;
	
	protected array $rawNavItems = [];
	protected array $navItems = [];
	protected int $stepsSegment = 3;
	protected array $allowedQueries = [];
	protected Collection $companies;
	
	/**
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->commonQueries();
	}
	
	/**
	 * Common Queries
	 *
	 * @return void
	 */
	private function commonQueries(): void
	{
		// Set the payment global settings
		$this->getPaymentReferrersData();
	}
}
