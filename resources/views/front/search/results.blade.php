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
	
	$apiResult ??= [];
	$apiExtra ??= [];
	$count = (array)data_get($apiExtra, 'count');
	$posts = (array)data_get($apiResult, 'data');
	$totalPosts = (int)data_get($apiResult, 'meta.total', 0);
	$tags = (array)data_get($apiExtra, 'tags');
	
	$postTypes ??= [];
	$orderByOptions ??= [];
	$displayModes ??= [];
	
	$selectedDisplayMode = config('settings.listings_list.display_mode', 'make-grid');
@endphp

@section('search')
	@parent
	@include('front.search.inc.form')
@endsection

@section('content')
	<div class="main-container">
		
		@if (session()->has('flash_notification'))
			@include('front.common.spacer')
			<?php $paddingTopExists = true; ?>
			<div class="container">
				<div class="row">
					<div class="col-12">
						@include('flash::message')
					</div>
				</div>
			</div>
		@endif
		
		@include('front.search.inc.breadcrumbs')
		
		@if (config('settings.listings_list.show_cats_in_top'))
			@if (isset($cats) && !empty($cats))
				<div class="container mb-2 hide-xs">
					<div class="row p-0 m-0">
						<div class="col-12 p-0 m-0 border border-bottom-0 bg-light"></div>
					</div>
				</div>
			@endif
			@include('front.search.inc.categories')
		@endif
		
		@if (isset($topAdvertising) && !empty($topAdvertising))
			@include('front.layouts.inc.advertising.top', ['paddingTopExists' => true])
			@php
				$paddingTopExists = false;
			@endphp
		@else
			@php
				if (isset($paddingTopExists) && $paddingTopExists) {
					$paddingTopExists = false;
				}
			@endphp
		@endif
		
		<div class="container">
			<div class="row">
				
				{{-- Sidebar --}}
                @if (config('settings.listings_list.left_sidebar'))
                    @include('front.search.inc.sidebar')
					@php
						$contentColSm = 'col-md-9';
					@endphp
                @else
					@php
						$contentColSm = 'col-md-12';
					@endphp
                @endif

				{{-- Content --}}
				<div class="{{ $contentColSm }} page-content col-thin-left mb-4">
					<div class="category-list {{ $selectedDisplayMode }}{{ ($contentColSm == 'col-md-12') ? ' noSideBar' : '' }}">
						<div class="tab-box">

							{{-- Nav tabs --}}
							<ul id="postType" class="nav nav-tabs add-tabs tablist" role="tablist">
								@php
									$aClass = '';
									$spanClass = 'text-bg-secondary';
									if (config('settings.listing_form.show_listing_type')) {
										if (!request()->filled('type') || request()->query('type') == '') {
											$aClass = ' active';
											$spanClass = 'text-bg-danger';
										}
									} else {
										$aClass = ' active';
										$spanClass = 'text-bg-danger';
									}
								@endphp
								<li class="nav-item">
									<a href="{!! request()->fullUrlWithoutQuery(['page', 'type']) !!}" class="nav-link{{ $aClass }}">
										{{ t('All Listings') }} <span class="badge {!! $spanClass !!}">{{ data_get($count, '0') }}</span>
									</a>
								</li>
								@if (config('settings.listing_form.show_listing_type'))
									@if (!empty($postTypes))
										@foreach ($postTypes as $postType)
											@php
												$postTypeId = data_get($postType, 'id');
												$postTypeUrl = request()->fullUrlWithQuery(['type' => $postTypeId, 'page' => null]);
												$postTypeCount = data_get($count, $postTypeId) ?? 0;
											@endphp
											@if (request()->filled('type') && request()->query('type') == $postTypeId)
												<li class="nav-item">
													<a href="{!! $postTypeUrl !!}" class="nav-link active">
														{{ data_get($postType, 'label') }}
														<span class="badge text-bg-danger">
															{{ $postTypeCount }}
														</span>
													</a>
												</li>
											@else
												<li class="nav-item">
													<a href="{!! $postTypeUrl !!}" class="nav-link">
														{{ data_get($postType, 'label') }}
														<span class="badge text-bg-secondary">
															{{ $postTypeCount }}
														</span>
													</a>
												</li>
											@endif
										@endforeach
									@endif
								@endif
							</ul>
							
							<div class="tab-filter pb-2">
								{{-- OrderBy Desktop --}}
								<select id="orderBy" title="sort by" class="niceselecter select-sort-by small" data-style="btn-select" data-width="auto">
									@if (!empty($orderByOptions))
										@foreach($orderByOptions as $option)
											@if (data_get($option, 'condition'))
												@php
													$optionUrl = request()->fullUrlWithQuery((array)data_get($option, 'query'));
												@endphp
												<option @selected(data_get($option, 'isSelected')) value="{!! $optionUrl !!}">
													{{ data_get($option, 'label') }}
												</option>
											@endif
										@endforeach
									@endif
								</select>
							</div>

						</div>
						
						<div class="listing-filter">
							<div class="float-start col-md-9 col-sm-8 col-12">
								<h1 class="h6 pb-0 breadcrumb-list">
									{!! (isset($htmlTitle)) ? $htmlTitle : '' !!}
								</h1>
                                <div style="clear:both;"></div>
							</div>
							
							{{-- Display Modes --}}
							@if (!empty($posts) && $totalPosts > 0)
								<div class="float-end col-md-3 col-sm-4 col-12 text-end listing-view-action">
									@if (!empty($displayModes))
										@foreach($displayModes as $displayMode => $value)
											<span class="grid-view{{ ($selectedDisplayMode == $displayMode) ? ' active' : '' }}">
												@if ($selectedDisplayMode == $displayMode)
													<i class="{{ data_get($value, 'icon') }}"></i>
												@else
													@php
														$displayModeUrl = request()->fullUrlWithQuery((array)data_get($value, 'query'));
													@endphp
													<a href="{!! $displayModeUrl !!}" rel="nofollow">
														<i class="{{ data_get($value, 'icon') }}"></i>
													</a>
												@endif
											</span>
										@endforeach
									@endif
								</div>
							@endif
							
							<div style="clear:both"></div>
						</div>
						
						{{-- Mobile Filter Bar --}}
						<div class="mobile-filter-bar col-xl-12">
							<ul class="list-unstyled list-inline no-margin no-padding">
								@if (config('settings.listings_list.left_sidebar'))
									<li class="filter-toggle">
										<a class=""><i class="fa-solid fa-bars"></i> {{ t('Filters') }}</a>
									</li>
								@endif
								<li>
									{{-- OrderBy Mobile --}}
									<div class="dropdown">
										<a class="dropdown-toggle" data-bs-toggle="dropdown">{{ t('Sort by') }}</a>
										<ul class="dropdown-menu">
											@if (!empty($orderByOptions))
												@foreach($orderByOptions as $option)
													@if (data_get($option, 'condition'))
														@php
															$optionUrl = request()->fullUrlWithQuery((array)data_get($option, 'query'));
														@endphp
														<li>
															<a href="{!! $optionUrl !!}" rel="nofollow">
																{{ data_get($option, 'label') }}
															</a>
														</li>
													@endif
												@endforeach
											@endif
										</ul>
									</div>
								</li>
							</ul>
						</div>
						<div class="menu-overly-mask"></div>
						{{-- Mobile Filter bar End--}}
						
						<div class="tab-content" id="myTabContent">
							<div class="tab-pane fade show active" id="contentAll" role="tabpanel" aria-labelledby="tabAll">
								<div id="postsList" class="category-list-wrapper posts-wrapper row no-margin">
									@if ($selectedDisplayMode == 'make-list')
										@include('front.search.inc.posts.template.list')
									@elseif ($selectedDisplayMode == 'make-compact')
										@include('front.search.inc.posts.template.compact')
									@else
										@include('front.search.inc.posts.template.grid')
									@endif
								</div>
							</div>
						</div>
						
						@if (request()->filled('q') && request()->query('q') != '' && data_get($count, '0') > 0)
							<div class="tab-box save-search-bar text-center">
								<a id="saveSearch"
								   data-search-url="{!! request()->fullUrlWithoutQuery(['_token', 'location']) !!}"
								   data-results-count="{{ data_get($count, '0') }}"
								>
									<i class="fa-regular fa-bell"></i> {{ t('Save Search') }}
								</a>
							</div>
						@endif
					</div>
					
					<nav class="mt-3 mb-0 pagination-sm" aria-label="">
						@include('vendor.pagination.api.bootstrap-4')
					</nav>
					
				</div>
			</div>
		</div>
		
		{{-- Advertising --}}
		@include('front.layouts.inc.advertising.bottom')
		
		{{-- Promo Listing Button --}}
		<div class="container mb-3">
			<div class="card border-light text-dark bg-light mb-3">
				<div class="card-body text-center">
					<h2>{{ t('do_you_have_anything') }}</h2>
					<h5>{{ t('sell_products_and_services_online_for_free') }}</h5>
					@if (!doesGuestHaveAbilityToCreateListings())
						<a href="{!! urlGen()->loginModal() !!}" class="btn btn-border btn-post btn-listing">
							{{ t('start_now') }}
						</a>
					@else
						<a href="{{ urlGen()->addPost() }}" class="btn btn-border btn-post btn-listing">
							{{ t('start_now') }}
						</a>
					@endif
				</div>
			</div>
		</div>
		
		{{-- Category Description --}}
		@if (isset($cat) && !empty(data_get($cat, 'description')))
			@if (!(bool)data_get($cat, 'hide_description'))
				<div class="container mb-3">
					<div class="card border-light text-dark bg-light mb-3">
						<div class="card-body">
							{!! data_get($cat, 'description') !!}
						</div>
					</div>
				</div>
			@endif
		@endif
		
		{{-- Show Posts Tags --}}
		@if (config('settings.listings_list.show_listings_tags'))
			@if (!empty($tags))
				<div class="container">
					<div class="card mb-3">
						<div class="card-body">
							<h2 class="card-title"><i class="fa-solid fa-tags"></i> {{ t('Tags') }}:</h2>
							@foreach($tags as $iTag)
								<span class="d-inline-block border border-inverse bg-light rounded-1 py-1 px-2 my-1 me-1">
									<a href="{{ urlGen()->tag($iTag) }}">
										{{ $iTag }}
									</a>
								</span>
							@endforeach
						</div>
					</div>
				</div>
			@endif
		@endif
		
	</div>
@endsection

@section('modal_location')
	@include('front.layouts.inc.modal.location')
@endsection

@section('after_scripts')
	<script>
		onDocumentReady((event) => {
			const postTypeEl = document.querySelectorAll('#postType a');
			if (postTypeEl.length > 0) {
				postTypeEl.forEach((element) => {
					element.addEventListener('click', (event) => {
						event.preventDefault();
						
						let goToUrl = event.target.getAttribute('href');
						redirect(goToUrl);
					});
				});
			}
			
			{{-- orderBy: HTML Select --}}
			const orderByEl = document.getElementById('orderBy');
			if (orderByEl) {
				orderByEl.addEventListener('change', (event) => {
					event.preventDefault();
					
					let goToUrl = event.target.value;
					if (goToUrl) {
						redirect(goToUrl);
					}
				});
			}
			
			{{-- orderBy: jQuery Nice Select --}}
			onDomElementsAdded('.select-sort-by li.option', (elements) => {
				if (elements.length <= 0) {
					return false;
				}
				
				elements.forEach((element) => {
					element.addEventListener('click', (event) => {
						event.preventDefault();
						
						const goToUrl = event.target.dataset.value;
						if (goToUrl) {
							redirect(goToUrl);
						}
					});
				});
			});
		});
	</script>
@endsection
