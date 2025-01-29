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

@section('search')
	@parent
@endsection

@section('content')
	@include('front.common.spacer')
	<div class="main-container inner-page">
		<div class="container">
			<div class="section-content">
				<div class="row">

					@if (session()->has('message'))
						<div class="alert alert-danger">
							{{ session('message') }}
						</div>
					@endif

					@if (session()->has('flash_notification'))
						<div class="col-12">
							<div class="row">
								<div class="col-12">
									@include('flash::message')
								</div>
							</div>
						</div>
					@endif
					
					@include('front.sections.spacer')
					<h1 class="text-center title-1"><strong>{{ t('sitemap') }}</strong></h1>
					<hr class="center-block small mt-0">
					
					<div class="col-12">
						<div class="content-box mb-0">
							<div class="row row-featured-category">
								<div class="col-12 box-title">
									<h2 class="px-3">
										<span class="title-3 fw-bold">{{ t('list_of_categories_and_sub_categories') }}</span>
									</h2>
								</div>
								
								<div class="col-12">
									<div class="list-categories-children styled">
										<div id="categoryList" class="row row-cols-lg-3 row-cols-md-2 row-cols-sm-1 row-cols-1">
											@foreach ($cats as $key => $iCat)
												@php
													$randomId = '-' . substr(uniqid(rand(), true), 5, 5);
													$itemClass = (count($cats) == $key+1) ? ' last-column' : '';
													
													$catIconClass = $iCat->icon_class ?? 'icon-ok';
													$catIcon = !empty($catIconClass) ? '<i class="' . $catIconClass . '"></i> ' : '';
												@endphp
												<div class="col cat-list{{ $itemClass }}">
													<h3 class="cat-title rounded">
														<a href="{{ urlGen()->category($iCat) }}">
															{!! $catIcon !!}{{ $iCat->name }} <span class="count"></span>
														</a>
														@if (isset($iCat->children) && $iCat->children->count() > 0)
															<span class="btn-cat-collapsed collapsed"
															      data-bs-toggle="collapse"
															      data-bs-target=".cat-id-{{ $iCat->id . $randomId }}"
															      aria-expanded="false"
															>
																<span class="icon-down-open-big"></span>
															</span>
														@endif
													</h3>
													<ul class="cat-collapse collapse show cat-id-{{ $iCat->id . $randomId }} long-list-home">
														@if (isset($iCat->children) && $iCat->children->count() > 0)
															@foreach ($iCat->children as $iSubCat)
																<li>
																	<a href="{{ urlGen()->category($iSubCat) }}">
																		{{ $iSubCat->name }}
																	</a>
																</li>
															@endforeach
														@endif
													</ul>
												</div>
											@endforeach
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					
					@if (isset($cities))
						@include('front.sections.spacer')
						<div class="col-12">
							<div class="content-box mb-0">
								<div class="row row-featured-category">
									<div class="col-12 box-title">
										<div class="inner">
											<h2 class="px-3">
												<span class="title-3 fw-bold">
													<i class="bi bi-geo-alt"></i> {{ t('list_of_cities_in') }} {{ config('country.name') }}
												</span>
											</h2>
										</div>
									</div>
									
									<div class="col-12">
										<div class="list-categories-children">
											<div id="cityList" class="row row-cols-lg-4 row-cols-md-3 row-cols-sm-2 row-cols-1">
												@foreach ($cities as $key => $city)
													@php
														$itemClass = ($cities->count() == $key + 1) ? ' cat-list-border' : '';
													@endphp
													<div class="col cat-list px-4{{ $itemClass }}">
														<a href="{{ urlGen()->city($city) }}" title="{{ t('Free Listings') }} {{ $city->name }}">
															<strong>{{ $city->name }}</strong>
														</a>
													</div>
												@endforeach
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					@endif

				</div>
				
				@include('front.layouts.inc.social.horizontal')
				
			</div>
		</div>
	</div>
@endsection

@section('before_scripts')
	@parent
	<script>
		var maxSubCats = 5;
		
		onDocumentReady((event) => {
			{{-- Reorder the category & city list --}}
			const categoryListReorder = new BsRowColumnsReorder('#categoryList', {defaultColumns: 3});
			const cityListReorder = new BsRowColumnsReorder('#cityList', {defaultColumns: 4});
		});
	</script>
@endsection
