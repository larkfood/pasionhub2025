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

namespace App\Providers\AppService\ConfigTrait;

trait SocialAuthConfig
{
	private function updateSocialAuthConfig(?array $settings = []): void
	{
		// $appUrl = config('app.url');
		$currentBaseUrl = request()->root();
		
		// Facebook
		$facebookClientId = env('FACEBOOK_CLIENT_ID', data_get($settings, 'facebook_client_id'));
		$facebookClientSecret = env('FACEBOOK_CLIENT_SECRET', data_get($settings, 'facebook_client_secret'));
		$facebookCallbackUrl = $currentBaseUrl . '/auth/facebook/callback';
		config()->set('services.facebook.client_id', $facebookClientId);
		config()->set('services.facebook.client_secret', $facebookClientSecret);
		config()->set('services.facebook.redirect', $facebookCallbackUrl);
		
		// LinkedIn
		$linkedinClientId = env('LINKEDIN_CLIENT_ID', data_get($settings, 'linkedin_client_id'));
		$linkedinClientSecret = env('LINKEDIN_CLIENT_SECRET', data_get($settings, 'linkedin_client_secret'));
		$linkedinCallbackUrl = $currentBaseUrl . '/auth/linkedin/callback';
		config()->set('services.linkedin-openid.client_id', $linkedinClientId);
		config()->set('services.linkedin-openid.client_secret', $linkedinClientSecret);
		config()->set('services.linkedin-openid.redirect', $linkedinCallbackUrl);
		
		// Twitter (OAuth 2.0)
		$twitterOauth2ClientId = env('TWITTER_OAUTH_2_CLIENT_ID', data_get($settings, 'twitter_oauth_2_client_id'));
		$twitterOauth2ClientSecret = env('TWITTER_OAUTH_2_CLIENT_SECRET', data_get($settings, 'twitter_oauth_2_client_secret'));
		$twitterOauth2CallbackUrl = $currentBaseUrl . '/auth/twitter_oauth_2/callback';
		config()->set('services.twitter-oauth-2.client_id', $twitterOauth2ClientId);
		config()->set('services.twitter-oauth-2.client_secret', $twitterOauth2ClientSecret);
		config()->set('services.twitter-oauth-2.redirect', $twitterOauth2CallbackUrl);
		
		// Twitter (OAuth 1.0)
		$twitterClientId = env('TWITTER_CLIENT_ID', data_get($settings, 'twitter_client_id'));
		$twitterClientSecret = env('TWITTER_CLIENT_SECRET', data_get($settings, 'twitter_client_secret'));
		$twitterCallbackUrl = $currentBaseUrl . '/auth/twitter/callback';
		config()->set('services.twitter.client_id', $twitterClientId);
		config()->set('services.twitter.client_secret', $twitterClientSecret);
		config()->set('services.twitter.redirect', $twitterCallbackUrl);
		
		// Google
		$googleClientId = env('GOOGLE_CLIENT_ID', data_get($settings, 'google_client_id'));
		$googleClientSecret = env('GOOGLE_CLIENT_SECRET', data_get($settings, 'google_client_secret'));
		$googleCallbackUrl = $currentBaseUrl . '/auth/google/callback';
		config()->set('services.google.client_id', $googleClientId);
		config()->set('services.google.client_secret', $googleClientSecret);
		config()->set('services.google.redirect', $googleCallbackUrl);
	}
}
