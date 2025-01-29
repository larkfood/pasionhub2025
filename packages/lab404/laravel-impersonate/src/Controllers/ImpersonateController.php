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

namespace Larapen\Impersonate\Controllers;

use Illuminate\Http\Request;
use Lab404\Impersonate\Services\ImpersonateManager;

class ImpersonateController extends \Lab404\Impersonate\Controllers\ImpersonateController
{
	/** @var ImpersonateManager */
	protected $manager;
	
	/**
	 * ImpersonateController constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->middleware('auth');
		
		$this->manager = app()->make(ImpersonateManager::class);
	}
	
	/**
	 * @param \Illuminate\Http\Request $request
	 * @param $id
	 * @param $guardName
	 * @return \Illuminate\Http\RedirectResponse
	 * @throws \Lab404\Impersonate\Exceptions\InvalidUserProvider
	 * @throws \Lab404\Impersonate\Exceptions\MissingUserProvider
	 */
	public function take(Request $request, $id, $guardName = null)
	{
		$guardName = $guardName ?? $this->manager->getDefaultSessionGuard();
		
		// If the Domain Mapping plugin is installed,
		// Then, the impersonate feature need to be disabled
		if (config('plugins.domainmapping.installed')) {
			$message = t('Cannot impersonate when the Domain Mapping plugin is installed');
			notification($message, 'error');
			
			return redirect()->back();
		}
		
		// Cannot impersonate yourself
		if ($id == $request->user()->getKey() && ($this->manager->getCurrentAuthGuardName() == $guardName)) {
			$message = t('Cannot impersonate yourself');
			notification($message, 'error');
			
			return redirect()->back();
		}
		
		// Cannot impersonate again if you're already impersonate a user
		if ($this->manager->isImpersonating()) {
			abort(403);
		}
		
		if (!$request->user()->canImpersonate()) {
			$message = t('The current user can not impersonate');
			notification($message, 'error');
			
			return redirect()->back();
		}
		
		$userToImpersonate = $this->manager->findUserById($id, $guardName);
		
		if ($userToImpersonate->canBeImpersonated()) {
			if ($this->manager->take($request->user(), $userToImpersonate, $guardName)) {
				$takeRedirect = $this->manager->getTakeRedirectTo();
				if ($takeRedirect !== 'back') {
					return redirect()->to($takeRedirect);
				}
			}
		} else {
			$message = t('The destination user can not be impersonated');
			notification($message, 'error');
		}
		
		return redirect()->back();
	}
	
	/**
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function leave()
	{
		if (!$this->manager->isImpersonating()) {
			abort(403);
		}
		
		$this->manager->leave();
		
		$leaveRedirect = $this->manager->getLeaveRedirectTo();
		if ($leaveRedirect !== 'back') {
			return redirect()->to($leaveRedirect);
		}
		
		return redirect()->back();
	}
}
