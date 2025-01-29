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

namespace App\Http\Controllers\Web\Front\Traits;

trait TravelWizardTrait
{
	/**
	 * Get the last unlocked step URL only if the given step is locked
	 *
	 * @param int $step
	 * @return string|null
	 */
	protected function getLastUnlockedStepUrlOnlyIfGivenStepIsLocked(int $step): ?string
	{
		$lastUnlockedStep = $this->getLastUnlockedStep();
		if ($lastUnlockedStep < $step) {
			$message = $this->getStepLockMessage($step);
			if (!empty($message)) {
				flash($message)->error();
			}
			
			return $this->getStepUrl($lastUnlockedStep);
		}
		
		return null;
	}
	
	/**
	 * Get the latest unlocked step,
	 * allowing steps auto-traveling
	 *
	 * @return int
	 */
	protected function getLastUnlockedStep(): int
	{
		$step = 0;
		
		if (!empty($this->navItems)) {
			foreach ($this->navItems as $item) {
				$isUnlockedItem = (bool)($item['unlocked'] ?? false);
				$itemStep = (int)($item['step'] ?? 0);
				
				if ($isUnlockedItem) {
					$step = $itemStep;
				} else {
					return $step;
				}
			}
		}
		
		return $step;
	}
	
	/**
	 * @param string|null $path
	 * @return int
	 */
	protected function getStepByUriPath(?string $path): int
	{
		if (empty($path)) return 0;
		
		$navItems = collect($this->rawNavItems)
			->filter(function ($item) use ($path) {
				$url = $item['url'] ?? '';
				$urlLastPath = getUrlSegment($url, $this->stepsSegment);
				
				return ($urlLastPath == $path);
			});
		
		$stepItem = $navItems->first();
		
		return (int)($stepItem['step'] ?? 0);
	}
	
	/**
	 * @param string $key
	 * @return int
	 */
	protected function getStepByKey(string $key): int
	{
		$stepItem = $this->navItems[$key] ?? [];
		
		return (int)($stepItem['step'] ?? 0);
	}
	
	/**
	 * @param int $step
	 * @return string
	 */
	protected function getStepUrl(int $step): string
	{
		$firstStepItem = reset($this->rawNavItems);
		$stepItem = collect($this->rawNavItems)->firstWhere('step', $step);
		
		$stepUrl = $stepItem['url'] ?? $firstStepItem['url'] ?? null;
		if (empty($stepUrl)) return '/';
		
		$allowedQueries = request()->only($this->allowedQueries);
		$stepUrl = urlQuery($stepUrl)->setParameters($allowedQueries)->toString();
		
		return getAsString($stepUrl);
	}
	
	/**
	 * @param int $step
	 * @return string
	 */
	protected function getStepLockMessage(int $step): string
	{
		$stepNavLink = collect($this->navItems)->firstWhere('step', $step);
		
		$defaultMessage = t('wizard_locked_step_message');
		$stepLockMessage = $stepNavLink['lockMessage'] ?? null;
		$stepLockMessage = !empty($stepLockMessage) ? $stepLockMessage : $defaultMessage;
		
		return getAsString($stepLockMessage);
	}
	
	/**
	 * @param int $key
	 * @return string
	 */
	protected function getPrevStepUrl(int $key): string
	{
		$prevStep = $key - 1;
		$prevStep = ($prevStep >= 1) ? $prevStep : 1;
		
		return $this->getStepUrl($prevStep);
	}
	
	/**
	 * @param int $key
	 * @return string
	 */
	protected function getNextStepUrl(int $key): string
	{
		$highestStepItem = collect($this->rawNavItems)->sortByDesc('step')->first();
		$highestStep = (int)($highestStepItem['step'] ?? 0);
		
		$nextStep = $key + 1;
		$nextStep = ($nextStep <= $highestStep) ? $nextStep : $highestStep;
		
		return $this->getStepUrl($nextStep);
	}
	
	/**
	 * Format all nav items
	 *
	 * @param array $navItems
	 * @param string|null $uriPath
	 * @return array
	 */
	protected function formatAllNavItems(array $navItems, ?string $uriPath = null): array
	{
		return collect($navItems)
			->map(fn ($item) => $this->formatNavItem($item, $uriPath))
			->toArray();
	}
	
	/**
	 * Format the nav item
	 *
	 * @param array $item
	 * @param string|null $uriPath
	 * @return array
	 */
	protected function formatNavItem(array $item, ?string $uriPath): array
	{
		$itemStep = $item['step'] ?? 1;
		
		// Get the current URL step
		$currentStep = $this->getStepByUriPath($uriPath);
		$prevStep = $currentStep - 1;
		
		// Save the item's URL
		$url = $item['url'] ?? null;
		
		// Update the item's URL
		$isUnlocked = $item['unlocked'] ?? false;
		$item['url'] = $isUnlocked ? $item['url'] : null;
		
		// Get item's parent CSS classes
		if (array_key_exists('parentClass', $item)) {
			$itemParentClass = $item['parentClass'] ?? '';
			$parentClass = ($itemStep >= $prevStep) ? 'enabled' : '';
			$item['parentClass'] = (!empty($itemParentClass) ? ' ' : '') . $parentClass;
		}
		
		// Get item's CSS classes
		$itemClass = $item['class'] ?? '';
		$class = 'disabled';
		if (!empty($url)) {
			if ($itemStep == 1) {
				$class = ($itemStep == $currentStep || $currentStep == 0) ? 'active' : (!$isUnlocked ? 'disabled' : '');
			} else {
				$class = ($itemStep == $currentStep) ? 'active' : (!$isUnlocked ? 'disabled' : '');
			}
		}
		$item['class'] = (!empty($itemClass) ? ' ' : '') . $class;
		
		// Add the item's latest path to the item properties
		$item['path'] = !empty($url) ? getUrlSegment($url, $this->stepsSegment) : null;
		
		return $item;
	}
}
