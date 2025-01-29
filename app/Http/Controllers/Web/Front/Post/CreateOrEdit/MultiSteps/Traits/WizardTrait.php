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

namespace App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Traits;

use App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Create\FinishController as CreateFinishController;
use App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Create\PaymentController as CreatePaymentController;
use App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Create\PhotoController as CreatePhotoController;
use App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Create\PostController as CreatePostController;
use App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Edit\PaymentController as EditPaymentController;
use App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Edit\PhotoController as EditPhotoController;
use App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Edit\PostController as EditPostController;
use App\Http\Controllers\Web\Front\Traits\TravelWizardTrait;

trait WizardTrait
{
	use TravelWizardTrait;
	
	/**
	 * Get Wizard Menu
	 *
	 * @param null $post
	 * @return void
	 */
	public function shareNavItems($post = null): void
	{
		$this->navItems = $this->getNavItems($post);
		view()->share('wizardMenu', $this->navItems);
	}
	
	/**
	 * Get the installation navigation links
	 *
	 * @param null $post
	 * @return array
	 */
	protected function getNavItems($post = null): array
	{
		$uriPath = request()->segment($this->stepsSegment);
		
		$navItems = [];
		if (isPostCreationRequest()) {
			$navItems = $this->getCreationNavItems($navItems, $uriPath, $post);
		} else {
			$navItems = $this->getEditionNavItems($navItems, $uriPath, $post);
		}
		
		// Save the original menu before formatting it
		$this->rawNavItems = $navItems;
		
		return $this->formatAllNavItems($navItems, $uriPath);
	}
	
	/**
	 * @param array $navItems
	 * @param string|null $uriPath
	 * @param null $post
	 * @return array
	 */
	private function getCreationNavItems(array $navItems, ?string $uriPath = null, $post = null): array
	{
		// Listing's Details
		$navItems[CreatePostController::class] = [
			'step'        => 1,
			'label'       => t('listing_details'),
			'url'         => urlGen()->addPost(),
			'class'       => '',
			'included'    => true,
			'lockMessage' => null,
			'unlocked'    => true, // Unlocked by default
		];
		
		// Pictures
		$navItems[CreatePhotoController::class] = [
			'step'        => 2,
			'label'       => t('Photos'),
			'url'         => urlGen()->addPostPhotos(),
			'class'       => 'picturesBloc',
			'included'    => true,
			'lockMessage' => t('wizard_post_input_required'),
			'unlocked'    => !empty(session('postInput')),
		];
		
		// Payment
		$isIncluded = (
			isset($this->countPackages, $this->countPaymentMethods)
			&& $this->countPackages > 0
			&& $this->countPaymentMethods > 0
			&& doesNoPackageOrPremiumOneSelected()
		);
		$navItems[CreatePaymentController::class] = [
			'step'        => 3,
			'label'       => t('Payment'),
			'url'         => urlGen()->addPostPayment(),
			'class'       => '',
			'included'    => $isIncluded,
			'lockMessage' => t('wizard_pictures_input_required'),
			'unlocked'    => (
				!empty(session('postInput'))
				&& !empty(session('picturesInput'))
			),
		];
		
		if ($uriPath == 'verify') {
			// Activation
			$navItems['verifyInfo'] = [
				'step'        => 4,
				'label'       => t('Activation'),
				'url'         => null,
				'class'       => '',
				'included'    => true,
				'lockMessage' => null,
				'unlocked'    => false,
			];
		} else {
			// Finish
			$navItems[CreateFinishController::class] = [
				'step'        => 4,
				'label'       => t('Finish'),
				'url'         => urlGen()->addPostFinished(),
				'class'       => '',
				'included'    => true,
				'lockMessage' => null,
				'unlocked'    => false,
			];
		}
		
		return $navItems;
	}
	
	/**
	 * @param array $navItems
	 * @param string|null $uriPath
	 * @param null $post
	 * @return array
	 */
	private function getEditionNavItems(array $navItems, ?string $uriPath = null, $post = null): array
	{
		// Listing's Details
		$navItems[EditPostController::class] = [
			'step'        => 1,
			'label'       => t('listing_details'),
			'url'         => urlGen()->editPost($post),
			'class'       => '',
			'included'    => true,
			'lockMessage' => null,
			'unlocked'    => !empty($post),
		];
		
		// Pictures
		$navItems[EditPhotoController::class] = [
			'step'        => 2,
			'label'       => t('Photos'),
			'url'         => urlGen()->editPostPhotos($post),
			'class'       => 'picturesBloc',
			'included'    => true,
			'lockMessage' => null,
			'unlocked'    => !empty($post),
		];
		
		// Payment
		$isIncluded = (
			isset($this->countPackages, $this->countPaymentMethods)
			&& $this->countPackages > 0
			&& $this->countPaymentMethods > 0
		);
		$navItems[EditPaymentController::class] = [
			'step'        => 3,
			'label'       => t('Payment'),
			'url'         => urlGen()->editPostPayment($post),
			'class'       => '',
			'included'    => $isIncluded,
			'lockMessage' => null,
			'unlocked'    => !empty($post),
		];
		
		// Finish
		$navItems['finishInfo'] = [
			'step'        => 4,
			'label'       => t('Finish'),
			'url'         => null,
			'class'       => '',
			'included'    => true,
			'lockMessage' => null,
			'unlocked'    => false,
		];
		
		return $navItems;
	}
}
