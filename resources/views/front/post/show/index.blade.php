{{--
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
--}}
@extends('front.layouts.master')

@php
	$post ??= [];
	$catBreadcrumb ??= [];
	$topAdvertising ??= [];
	$bottomAdvertising ??= [];
@endphp

@section('content')
	@if (session()->has('flash_notification'))
		@include('front.common.spacer')
		@php
			$paddingTopExists = true;
		@endphp
		<div class="container">
			<div class="row">
				<div class="col-12">
					@include('flash::message')
				</div>
			</div>
		</div>
		@php
			session()->forget('flash_notification.message');
		@endphp
	@endif
	
	{{-- Archived listings message --}}
	@if (!empty(data_get($post, 'archived_at')))
		@include('front.common.spacer')
		@php
			$paddingTopExists = true;
		@endphp
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="alert alert-warning" role="alert">
						{!! t('This listing has been archived') !!}
					</div>
				</div>
			</div>
		</div>
	@endif
	
	<div class="main-container">
		
		@if (!empty($topAdvertising))
			@include('front.layouts.inc.advertising.top', ['paddingTopExists' => $paddingTopExists ?? false])
			@php
				$paddingTopExists = false;
			@endphp
		@endif
		
		<div class="container {{ !empty($topAdvertising) ? 'mt-3' : 'mt-2' }}">
			<div class="row">
				<div class="col-md-12">
					
					<nav aria-label="breadcrumb" role="navigation" class="float-start">
						<ol class="breadcrumb">
							<li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="fa-solid fa-house"></i></a></li>
							<li class="breadcrumb-item"><a href="{{ url('/') }}">{{ config('country.name') }}</a></li>
							@if (is_array($catBreadcrumb) && count($catBreadcrumb) > 0)
								@foreach($catBreadcrumb as $key => $value)
									<li class="breadcrumb-item">
										<a href="{{ $value->get('url') }}">
											{!! $value->get('name') !!}
										</a>
									</li>
								@endforeach
							@endif
							<li class="breadcrumb-item active" aria-current="page">{{ str(data_get($post, 'title'))->limit(70) }}</li>
						</ol>
					</nav>
					
					<div class="float-end backtolist">
						<a href="{{ rawurldecode(url()->previous()) }}">
							<i class="fa-solid fa-angles-left"></i> {{ t('back_to_results') }}
						</a>
					</div>
				
				</div>
			</div>
		</div>
		
		<div class="container">
			<div class="row">
				<div class="col-lg-9 page-content col-thin-right">
					@php
						$innerBoxStyle = (!auth()->check() && plugin_exists('reviews')) ? 'overflow: visible;' : '';
					@endphp
					<div class="inner inner-box items-details-wrapper pb-0" style="{{ $innerBoxStyle }}">
						<h1 class="h4 fw-bold enable-long-words">
							<strong>
								<a href="{{ urlGen()->post($post) }}" title="{{ data_get($post, 'title') }}">
									{{ data_get($post, 'title') }}
                                </a>
                            </strong>
							@if (config('settings.listing_form.show_listing_type'))
								@if (!empty(data_get($post, 'postType')))
									<small class="label label-default adlistingtype">{{ data_get($post, 'postType.name') }}</small>
								@endif
							@endif
							@if (data_get($post, 'featured') == 1 && !empty(data_get($post, 'payment.package')))
								<i class="fa-solid fa-check-circle"
								   style="color: {{ data_get($post, 'payment.package.ribbon') }};"
								   data-bs-placement="bottom"
								   data-bs-toggle="tooltip"
								   title="{{ data_get($post, 'payment.package.short_name') }}"
								></i>
                            @endif
						</h1>
						<span class="info-row">
							@if (!config('settings.listing_page.hide_date'))
							<span class="date"{!! (config('lang.direction')=='rtl') ? ' dir="rtl"' : '' !!}>
								<i class="fa-regular fa-clock"></i> {!! data_get($post, 'created_at_formatted') !!}
							</span>&nbsp;
							@endif
							<span class="category"{!! (config('lang.direction')=='rtl') ? ' dir="rtl"' : '' !!}>
								<i class="bi bi-folder"></i> {{ data_get($post, 'category.parent.name', data_get($post, 'category.name')) }}
							</span>&nbsp;
							<span class="item-location"{!! (config('lang.direction')=='rtl') ? ' dir="rtl"' : '' !!}>
								<i class="bi bi-geo-alt"></i> {{ data_get($post, 'city.name') }}
							</span>&nbsp;
							<span class="category"{!! (config('lang.direction')=='rtl') ? ' dir="rtl"' : '' !!}>
								<i class="bi bi-eye"></i> {{ data_get($post, 'visits_formatted') }}
							</span>
							<span class="category float-md-end"{!! (config('lang.direction')=='rtl') ? ' dir="rtl"' : '' !!}>
								{{ t('reference') }}: {{ data_get($post, 'reference') }}
							</span>
						</span>
						
						@include('front.post.show.inc.pictures-slider')
						
						@if (config('plugins.reviews.installed'))
							@if (view()->exists('reviews::ratings-single'))
								@include('reviews::ratings-single')
							@endif
						@endif
						
						@include('front.post.show.inc.details')
					</div>
				</div>
				
				<div class="col-lg-3 page-sidebar-right">
					@include('front.post.show.inc.sidebar')
				</div>
			</div>

		</div>
		
		@if (config('settings.listing_page.similar_listings') == '1' || config('settings.listing_page.similar_listings') == '2')
			@php
				$widgetType = (config('settings.listing_page.similar_listings_in_carousel') ? 'carousel' : 'normal');
			@endphp
			@include('front.search.inc.posts.widget.' . $widgetType, [
				'widget' => ($widgetSimilarPosts ?? null), 'firstSection' => false
			])
		@endif
		
		@include('front.layouts.inc.advertising.bottom', ['firstSection' => false])
		
		@if (isVerifiedPost($post))
			@include('front.layouts.inc.tools.facebook-comments', ['firstSection' => false])
		@endif
		
	</div>
@endsection
@php
	if (!session()->has('emailVerificationSent') && !session()->has('phoneVerificationSent')) {
		if (session()->has('message')) {
			session()->forget('message');
		}
	}
@endphp

@section('modal_message')
	@if (config('settings.listing_page.show_security_tips') == '1')
		@include('front.post.show.inc.security-tips')
	@endif
	@if (auth()->check() || config('settings.listing_page.guest_can_contact_authors') == '1')
		@include('front.account.messenger.modal.create')
	@endif
@endsection

@section('after_styles')
@endsection

@section('before_scripts')
	<script>
		var showSecurityTips = '{{ config('settings.listing_page.show_security_tips', '0') }}';
	</script>
@endsection

@section('after_scripts')
	<script>
		{{-- Favorites Translation --}}
        var lang = {
            labelSavePostSave: "{!! t('Save listing') !!}",
            labelSavePostRemove: "{!! t('Remove favorite') !!}",
            loginToSavePost: "{!! t('Please log in to save the Listings') !!}",
            loginToSaveSearch: "{!! t('Please log in to save your search') !!}"
        };
		
		onDocumentReady((event) => {
			{{-- Tooltip --}}
			const tooltipEls = document.querySelectorAll('[rel="tooltip"]');
			if (tooltipEls) {
				let tooltipTriggerList = [].slice.call(tooltipEls);
				let tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
					return new bootstrap.Tooltip(tooltipTriggerEl)
				});
			}
			
			{{-- Keep the current tab active with Twitter Bootstrap after a page reload --}}
			const tabEls = document.querySelectorAll('button[data-bs-toggle="tab"]');
			if (tabEls.length > 0) {
				tabEls.forEach((tabButton) => {
					tabButton.addEventListener('shown.bs.tab', function (e) {
						/* Save the latest tab; use cookies if you like 'em better: */
						/* localStorage.setItem('lastTab', tabButton.getAttribute('href')); */
						localStorage.setItem('lastTab', tabButton.getAttribute('data-bs-target'));
					});
				});
			}
			
			{{-- Go to the latest tab, if it exists: --}}
            let lastTab = localStorage.getItem('lastTab');
            if (lastTab) {
				{{-- let triggerEl = document.querySelector('a[href="' + lastTab + '"]'); --}}
				let triggerEl = document.querySelector('button[data-bs-target="' + lastTab + '"]');
				if (typeof triggerEl !== 'undefined' && triggerEl !== null) {
					let tabObj = new bootstrap.Tab(triggerEl);
					if (tabObj !== null) {
						tabObj.show();
					}
				}
            }
		});
	</script>
@endsection
