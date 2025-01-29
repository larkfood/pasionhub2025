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

namespace App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Create;

use App\Models\Post;
use App\Models\Scopes\VerifiedScope;
use App\Models\Scopes\ReviewedScope;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Larapen\LaravelMetaTags\Facades\MetaTag;

class FinishController extends BaseController
{
	/**
	 * End of the steps (Confirmation)
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
	 */
	public function __invoke(Request $request): View|RedirectResponse
	{
		if (!session()->has('message')) {
			return redirect()->to('/');
		}
		
		// Clear the step wizard
		if (session()->has('postId')) {
			// Get the Post
			$post = Post::query()
				->withoutGlobalScopes([VerifiedScope::class, ReviewedScope::class])
				->where('id', session('postId'))
				->first();
			
			abort_if(empty($post), 404, t('post_not_found'));
			
			session()->forget('postId');
		}
		
		$emailVerificationEnabled = (config('settings.mail.email_verification') == '1');
		$phoneVerificationEnabled = (config('settings.sms.phone_verification') == '1');
		$doesVerificationIsDisabled = (!$emailVerificationEnabled && !$phoneVerificationEnabled);
		
		// Redirect to the Post,
		// - If User is logged
		// - Or if Email and Phone verification option is not activated
		if (auth()->check() || $doesVerificationIsDisabled) {
			if (!empty($post)) {
				flash(session('message'))->success();
				
				return redirect()->to(urlGen()->postUri($post));
			}
		}
		
		// Meta Tags
		MetaTag::set('title', session('message'));
		MetaTag::set('description', session('message'));
		
		return view('front.post.createOrEdit.multiSteps.create.finish');
	}
}
