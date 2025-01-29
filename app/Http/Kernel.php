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

namespace App\Http;

use Illuminate\Foundation\Configuration\Middleware;

class Kernel
{
	public function __invoke(Middleware $middleware): void
	{
		/*
		 * The application's global HTTP middleware stack
		 */
		$middleware->use([
			// \App\Http\Middleware\TrustHosts::class,
			\App\Http\Middleware\TrustProxies::class,
			\Illuminate\Http\Middleware\HandleCors::class,
			\App\Http\Middleware\PreventRequestsDuringMaintenance::class,
			\Illuminate\Http\Middleware\ValidatePostSize::class,
			\App\Http\Middleware\TrimStrings::class,
			\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
			\Larapen\Honeypot\app\Http\Middleware\ProtectAgainstSpam::class,
		]);
		
		/*
		 * The application's route middleware groups
		 */
		$middleware->group('web', [
			\App\Http\Middleware\EncryptCookies::class,
			\Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
			\App\Http\Middleware\StartSessionExtended::class,
			\Illuminate\View\Middleware\ShareErrorsFromSession::class,
			\App\Http\Middleware\ResumeSessionId::class,
			\App\Http\Middleware\ValidateCsrfToken::class,
			\Illuminate\Routing\Middleware\SubstituteBindings::class,
			// \Illuminate\Session\Middleware\AuthenticateSession::class,
			
			\App\Http\Middleware\RequirementsChecker::class,
			\App\Http\Middleware\GetLocalization::class,
			\App\Http\Middleware\SetBrowserLocale::class,
			\App\Http\Middleware\SetCountryLocale::class,
			\App\Http\Middleware\SetDefaultLocale::class,
			\App\Http\Middleware\InputRequest::class,
			\App\Http\Middleware\TipsMessages::class,
			\App\Http\Middleware\DemoRestriction::class,
			\App\Http\Middleware\ReferrerChecker::class,
			\App\Http\Middleware\IsVerifiedUser::class,
			\App\Http\Middleware\BannedUser::class,
			\App\Http\Middleware\HttpsProtocol::class,
			\App\Http\Middleware\ResourceHints::class,
			\App\Http\Middleware\LazyLoading::class,
			\App\Http\Middleware\HtmlMinify::class,
		]);
		
		$middleware->appendToGroup('admin', [
			\App\Http\Middleware\EncryptCookies::class,
			\Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
			\App\Http\Middleware\StartSessionExtended::class,
			\Illuminate\View\Middleware\ShareErrorsFromSession::class,
			\App\Http\Middleware\ResumeSessionId::class,
			\App\Http\Middleware\ValidateCsrfToken::class,
			\Illuminate\Routing\Middleware\SubstituteBindings::class,
			
			\App\Http\Middleware\RequirementsChecker::class,
			\App\Http\Middleware\GetLocalization::class,
			\App\Http\Middleware\Admin::class,
			\App\Http\Middleware\DemoRestriction::class,
			\App\Http\Middleware\ReferrerChecker::class,
			\App\Http\Middleware\InputRequest::class,
			\App\Http\Middleware\BannedUser::class,
			\App\Http\Middleware\HttpsProtocol::class,
			\App\Http\Middleware\ResourceHints::class,
			\App\Http\Middleware\ScribeUpdater::class,
		]);
		
		$middleware->group('api', [
			\App\Http\Middleware\Installed::class,
			\App\Http\Middleware\VerifyAPIAccess::class,
			\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
			/*
			 * See the RouteServiceProvider::configureRateLimiting() method
			 */
			\Illuminate\Routing\Middleware\ThrottleRequests::class . ':api', // throttle:api
			\Illuminate\Routing\Middleware\SubstituteBindings::class,
			
			\App\Http\Middleware\RequirementsChecker::class,
			\App\Http\Middleware\GetLocalization::class,
			\App\Http\Middleware\IsVerifiedUser::class,
			\App\Http\Middleware\DemoRestriction::class,
			\App\Http\Middleware\BannedUser::class,
			\App\Http\Middleware\LastUserActivity::class,
		]);
		
		/*
		 * The application's middleware aliases
		 */
		$middleware->alias([
			'auth'             => \App\Http\Middleware\Authenticate::class,
			'auth.basic'       => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
			'auth.session'     => \Illuminate\Session\Middleware\AuthenticateSession::class,
			'cache.headers'    => \Illuminate\Http\Middleware\SetCacheHeaders::class,
			'can'              => \Illuminate\Auth\Middleware\Authorize::class,
			'guest'            => \App\Http\Middleware\RedirectIfAuthenticated::class,
			'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
			'precognitive'     => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
			'signed'           => \Illuminate\Routing\Middleware\ValidateSignature::class,
			// 'subscribed'    => \Spark\Http\Middleware\VerifyBillableIsSubscribed::class,
			'throttle'         => \Illuminate\Routing\Middleware\ThrottleRequests::class,
			'verified'         => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
			
			'banned.user'   => \App\Http\Middleware\BannedUser::class,
			'install'       => \App\Http\Middleware\Install::class,
			'installed'     => \App\Http\Middleware\Installed::class,
			'clearance'     => \App\Http\Middleware\Clearance::class,
			'no.http.cache' => \App\Http\Middleware\NoHttpCache::class,
			'only.ajax'     => \App\Http\Middleware\OnlyAjax::class,
		]);
		
		/*
		 * The priority-sorted list of middleware
		 * This forces non-global middleware to always be in the given order
		 */
		$middleware->priority([
			\Illuminate\Cookie\Middleware\EncryptCookies::class,
			\App\Http\Middleware\StartSessionExtended::class,
			\Illuminate\View\Middleware\ShareErrorsFromSession::class,
			\App\Http\Middleware\Authenticate::class,
			\Illuminate\Session\Middleware\AuthenticateSession::class,
			\Illuminate\Routing\Middleware\SubstituteBindings::class,
			\Illuminate\Auth\Middleware\Authorize::class,
			\App\Http\Middleware\ReferrerChecker::class,
			\App\Http\Middleware\DemoRestriction::class,
			\App\Http\Middleware\InputRequest::class,
			\App\Http\Middleware\GetLocalization::class,
			\App\Http\Middleware\SetBrowserLocale::class,
			\App\Http\Middleware\SetCountryLocale::class,
			\App\Http\Middleware\SetDefaultLocale::class,
			\App\Http\Middleware\LazyLoading::class,
			\App\Http\Middleware\ResourceHints::class,
			\App\Http\Middleware\HtmlMinify::class,
		]);
	}
}
