@php
	
	$socialLinksAreEnabled = (
		config('settings.social_link.facebook_page_url')
		|| config('settings.social_link.twitter_url')
		|| config('settings.social_link.tiktok_url')
		|| config('settings.social_link.linkedin_url')
		|| config('settings.social_link.pinterest_url')
		|| config('settings.social_link.instagram_url')
		|| config('settings.social_link.youtube_url')
		|| config('settings.social_link.vimeo_url')
		|| config('settings.social_link.vk_url')
		|| config('settings.social_link.tumblr_url')
		|| config('settings.social_link.flickr_url')
	);
	$appsLinksAreEnabled = (
		config('settings.other.ios_app_url')
		|| config('settings.other.android_app_url')
	);
	$socialAndAppsLinksAreEnabled = ($socialLinksAreEnabled || $appsLinksAreEnabled);
@endphp
<footer class="main-footer">
	@php
		$rowColsLg = $socialAndAppsLinksAreEnabled ? 'row-cols-lg-3' : 'row-cols-lg-2';
		$rowColsMd = $rowColsLg;
		
		$ptFooterContent = '';
		$mbCopy = ' mb-3';
		if (config('settings.footer.hide_links')) {
			$ptFooterContent = ' pt-sm-5 pt-5';
			$mbCopy = ' mb-4';
		}
	@endphp
	<div class="footer-content{{ $ptFooterContent }}">
		<div class="container">
			<div class="row {{ $rowColsLg }} {{ $rowColsMd }} row-cols-sm-2 row-cols-2 g-3">
				
				@if (!config('settings.footer.hide_links'))
					<div class="col">
						<div class="footer-col">
							<h4 class="footer-title">{{ t('Contact and Sitemap') }}</h4>
							<ul class="list-unstyled footer-nav">
								<li><a href="{{ urlGen()->contact() }}"> {{ t('Contact') }} </a></li>
								@if (!empty(config('lang.code')) && !empty(config('country.icode')))
									<li><a href="{{ urlGen()->sitemap() }}"> {{ t('sitemap') }} </a></li>
								@endif
								<li><a href="{{ urlGen()->countries() }}"> {{ t('countries') }} </a></li>
							</ul>
						</div>
					</div>
					
					<div class="col">
						<div class="footer-col">
							<h4 class="footer-title">{{ t('My Account') }}</h4>
							<ul class="list-unstyled footer-nav">
								@if (!auth()->user())
									<li><a href="{{ urlGen()->login() }}"> {{ t('log_in') }} </a></li>
									<li><a href="{{ urlGen()->register() }}"> {{ t('register') }} </a></li>
								@else
									<li><a href="{{ url('account') }}"> {{ t('My Account') }} </a></li>
									<li><a href="{{ url('account/posts/list') }}"> {{ t('my_listings') }} </a></li>
									<li><a href="{{ url('account/posts/favourite') }}"> {{ t('favourite_listings') }} </a></li>
								@endif
							</ul>
						</div>
					</div>
					
					@if ($socialAndAppsLinksAreEnabled)
						<div class="col">
							<div class="footer-col row">
								@php
									$footerSocialClass = '';
									$footerSocialTitleClass = '';
								@endphp
								@if ($appsLinksAreEnabled)
									<div class="col-sm-12 col-12 p-lg-0">
										<div class="mobile-app-content">
											<h4 class="footer-title">{{ t('Mobile Apps') }}</h4>
											<div class="row">
												@if (config('settings.other.ios_app_url'))
													<div class="col-12 col-sm-6">
														<a class="app-icon" target="_blank" href="{{ config('settings.other.ios_app_url') }}">
															<span class="hide-visually">{{ t('iOS app') }}</span>
															<img src="{{ url('images/site/app-store-badge.svg') }}" alt="{{ t('Available on the App Store') }}">
														</a>
													</div>
												@endif
												@if (config('settings.other.android_app_url'))
													<div class="col-12 col-sm-6">
														<a class="app-icon" target="_blank" href="{{ config('settings.other.android_app_url') }}">
															<span class="hide-visually">{{ t('Android App') }}</span>
															<img src="{{ url('images/site/google-play-badge.svg') }}" alt="{{ t('Available on Google Play') }}">
														</a>
													</div>
												@endif
											</div>
										</div>
									</div>
									@php
										$footerSocialClass = 'hero-subscribe';
										$footerSocialTitleClass = 'm-0';
									@endphp
								@endif
								
								@if ($socialLinksAreEnabled)
									<div class="col-sm-12 col-12 p-lg-0">
										<div class="{!! $footerSocialClass !!}">
											<h4 class="footer-title {!! $footerSocialTitleClass !!}">{{ t('Follow us on') }}</h4>
											<ul class="list-unstyled list-inline mx-0 footer-nav footer-nav-inline social-media social-links">
												@if (config('settings.social_link.facebook_page_url'))
													<li>
														<a class="facebook"
														   href="{{ config('settings.social_link.facebook_page_url') }}"
														   title="Facebook"
														>
															<i class="fa-brands fa-square-facebook"></i>
														</a>
													</li>
												@endif
												@if (config('settings.social_link.twitter_url'))
													<li>
														<a class="x-twitter"
														   href="{{ config('settings.social_link.twitter_url') }}"
														   title="X (Twitter)"
														>
															<i class="fa-brands fa-square-x-twitter"></i>
														</a>
													</li>
												@endif
												@if (config('settings.social_link.instagram_url'))
													<li>
														<a class="instagram"
														   href="{{ config('settings.social_link.instagram_url') }}"
														   title="Instagram"
														>
															<i class="fa-brands fa-square-instagram"></i>
														</a>
													</li>
												@endif
												@if (config('settings.social_link.linkedin_url'))
													<li>
														<a class="linkedin"
														   href="{{ config('settings.social_link.linkedin_url') }}"
														   title="LinkedIn"
														>
															<i class="fa-brands fa-linkedin"></i>
														</a>
													</li>
												@endif
												@if (config('settings.social_link.pinterest_url'))
													<li>
														<a class="pinterest"
														   href="{{ config('settings.social_link.pinterest_url') }}"
														   title="Pinterest"
														>
															<i class="fa-brands fa-square-pinterest"></i>
														</a>
													</li>
												@endif
												@if (config('settings.social_link.tiktok_url'))
													<li>
														<a class="tiktok"
														   href="{{ config('settings.social_link.tiktok_url') }}"
														   title="Tiktok"
														>
															<i class="fa-brands fa-tiktok"></i>
														</a>
													</li>
												@endif
												@if (config('settings.social_link.youtube_url'))
													<li>
														<a class="youtube"
														   href="{{ config('settings.social_link.youtube_url') }}"
														   title="YouTube"
														>
															<i class="fa-brands fa-youtube"></i>
														</a>
													</li>
												@endif
												@if (config('settings.social_link.vimeo_url'))
													<li>
														<a class="vimeo"
														   href="{{ config('settings.social_link.vimeo_url') }}"
														   title="Vimeo"
														>
															<i class="fa-brands fa-vimeo"></i>
														</a>
													</li>
												@endif
												@if (config('settings.social_link.vk_url'))
													<li>
														<a class="vk"
														   href="{{ config('settings.social_link.vk_url') }}"
														   title="VK (VKontakte)"
														>
															<i class="fa-brands fa-vk"></i>
														</a>
													</li>
												@endif
												@if (config('settings.social_link.tumblr_url'))
													<li>
														<a class="tumblr"
														   href="{{ config('settings.social_link.tumblr_url') }}"
														   title="Tumblr"
														>
															<i class="fa-brands fa-square-tumblr"></i>
														</a>
													</li>
												@endif
												@if (config('settings.social_link.flickr_url'))
													<li>
														<a class="flickr"
														   href="{{ config('settings.social_link.flickr_url') }}"
														   title="Flickr"
														>
															<i class="fa-brands fa-flickr"></i>
														</a>
													</li>
												@endif
											</ul>
										</div>
									</div>
								@endif
							</div>
						</div>
					@endif
					
					<div style="clear: both"></div>
				@endif
			
			</div>
			<div class="row">
				
				@php
					$mtPay = '';
					$mtCopy = ' mt-md-4 mt-3 pt-2';
				@endphp
				<div class="col-12">
					@if (!config('settings.footer.hide_payment_plugins_logos') && isset($paymentMethods) && $paymentMethods->count() > 0)
						@if (config('settings.footer.hide_links'))
							@php
								$mtPay = ' mt-0';
							@endphp
						@endif
						<div class="text-center payment-method-logo{{ $mtPay }}">
							{{-- Payment Plugins --}}
							@foreach($paymentMethods as $paymentMethod)
								@if (file_exists(plugin_path($paymentMethod->name, 'public/images/payment.png')))
									<img src="{{ url('images/' . $paymentMethod->name . '/payment.png') }}" alt="{{ $paymentMethod->display_name }}" title="{{ $paymentMethod->display_name }}">
								@endif
							@endforeach
						</div>
					@else
						@php
							$mtCopy = ' mt-0';
						@endphp
						@if (!config('settings.footer.hide_links'))
							@php
								$mtCopy = ' mt-md-4 mt-3 pt-2';
							@endphp
							<hr class="border-0 bg-secondary">
						@endif
					@endif
					
					<div class="copy-info text-center mb-md-0{{ $mbCopy }}{{ $mtCopy }}">
						© {{ date('Y') }} {{ config('settings.app.name') }}. {{ t('all_rights_reserved') }}.
						@if (!config('settings.footer.hide_powered_by'))
							@if (config('settings.footer.powered_by_info'))
								{{ t('Powered by') }} {!! config('settings.footer.powered_by_info') !!}
							@else
								{{ t('Powered by') }} <a href="https://laraclassifier.com" title="LaraClassifier">LaraClassifier</a>.
							@endif
						@endif
					</div>
				</div>
			
			</div>
		</div>
	</div>
</footer>
