@php
	$posts ??= [];
	$totalPosts ??= 0;
	
	$city ??= null;
	$cat ??= null;
@endphp
@if (!empty($posts) && $totalPosts > 0)
	@foreach($posts as $key => $post)
		<div class="item-list">
			@if (data_get($post, 'featured') == 1)
				@if (!empty(data_get($post, 'payment.package')))
					@if (data_get($post, 'payment.package.ribbon') != '')
						<div class="ribbon-horizontal {{ data_get($post, 'payment.package.ribbon') }}">
							<span>{{ data_get($post, 'payment.package.short_name') }}</span>
						</div>
					@endif
				@endif
			@endif
			
			@php
				$picturePath = data_get($post, 'picture.file_path');
				$pictureAttr = ['class' => 'lazyload thumbnail no-margin'];
				
				$postUrl = urlGen()->post($post);
				$parentCatUrl = null;
				if (!empty(data_get($post, 'category.parent'))) {
					$parentCatUrl = urlGen()->category(data_get($post, 'category.parent'), null, $city);
				}
				$catUrl = urlGen()->category(data_get($post, 'category'), null, $city);
				$locationUrl = urlGen()->city(data_get($post, 'city'), null, $cat);
			@endphp
			
			<div class="row d-flex align-content-between h-100">
				<div class="col-sm-9 col-12 p-0 m-0">
					<div class="row">
						<div class="col-sm-3 col-12 no-padding photobox">
							<div class="add-image">
								<span class="photo-count">
									<i class="fa-solid fa-camera"></i> {{ data_get($post, 'count_pictures') }}
								</span>
								<a href="{{ $postUrl }}">
									@php
										$src = data_get($post, 'picture.url.medium');
										$webpSrc = data_get($post, 'picture.url.webp.medium');
										$alt = str(data_get($post, 'title'))->slug();
										echo generateImageHtml($src, $alt, $webpSrc, $pictureAttr);
									@endphp
								</a>
							</div>
						</div>
						
						<div class="col-sm-9 col-12 add-desc-box">
							<div class="items-details">
								<h5 class="add-title">
									<a href="{{ $postUrl }}">
										{{ str(data_get($post, 'title'))->limit(70) }}
									</a>
								</h5>
								
								@php
									$showPostInfo = (
										(!config('settings.listings_list.hide_post_type') && config('settings.listing_form.show_listing_type'))
										|| !config('settings.listings_list.hide_date')
										|| !config('settings.listings_list.hide_category')
										|| !config('settings.listings_list.hide_location')
									);
								@endphp
								@if ($showPostInfo)
									<span class="info-row">
										@if (!config('settings.listings_list.hide_post_type') && config('settings.listing_form.show_listing_type'))
											@if (!empty(data_get($post, 'postType')))
												<span class="add-type business-posts"
													  data-bs-toggle="tooltip"
													  data-bs-placement="bottom"
													  title="{{ data_get($post, 'postType.name') }}"
												>
													{{ strtoupper(mb_substr(data_get($post, 'postType.name'), 0, 1)) }}
												</span>&nbsp;
											@endif
										@endif
										@if (!config('settings.listings_list.hide_date'))
											<span class="date"{!! (config('lang.direction')=='rtl') ? ' dir="rtl"' : '' !!}>
												<i class="fa-regular fa-clock"></i> {!! data_get($post, 'created_at_formatted') !!}
											</span>
										@endif
										@if (!config('settings.listings_list.hide_category'))
											<span class="category"{!! (config('lang.direction')=='rtl') ? ' dir="rtl"' : '' !!}>
												<i class="bi bi-folder"></i>&nbsp;
												@if (!empty(data_get($post, 'category.parent')))
													<a href="{!! $parentCatUrl !!}" class="info-link">
														{{ data_get($post, 'category.parent.name') }}
													</a>&nbsp;&raquo;&nbsp;
												@endif
												<a href="{!! $catUrl !!}" class="info-link">
													{{ data_get($post, 'category.name') }}
												</a>
											</span>
										@endif
										@if (!config('settings.listings_list.hide_location'))
											<span class="item-location"{!! (config('lang.direction')=='rtl') ? ' dir="rtl"' : '' !!}>
												<i class="bi bi-geo-alt"></i>&nbsp;
												<a href="{!! $locationUrl !!}" class="info-link">
													{{ data_get($post, 'city.name') }}
												</a> {{ data_get($post, 'distance_info') }}
											</span>
										@endif
									</span>
								@endif
								
								@if (config('plugins.reviews.installed'))
									@if (view()->exists('reviews::ratings-list'))
										@include('reviews::ratings-list')
									@endif
								@endif
							</div>
						</div>
					</div>
				</div>
				
				<div class="col-sm-3 col-12 text-end price-box" style="white-space: nowrap;">
					<div class="row w-100">
						<div class="col-12 m-0 p-0 d-flex justify-content-end">
							<h2 class="item-price h5 fw-bold">
								{!! data_get($post, 'price_formatted') !!}
							</h2>
						</div>
						<div class="col-12 m-0 p-0 d-flex justify-content-end">
							@if (!empty(data_get($post, 'payment.package')))
								@if (data_get($post, 'payment.package.has_badge') == 1)
									<a class="btn btn-danger btn-sm make-favorite">
										<i class="fa-solid fa-certificate"></i> <span>{{ data_get($post, 'payment.package.short_name') }}</span>
									</a>&nbsp;
								@endif
							@endif
							@if (!empty(data_get($post, 'savedByLoggedUser')))
								<a class="btn btn-success btn-sm make-favorite" id="{{ data_get($post, 'id') }}">
									<i class="bi bi-bookmark-fill"></i> <span>{{ t('Saved') }}</span>
								</a>
							@else
								<a class="btn btn-default btn-sm make-favorite" id="{{ data_get($post, 'id') }}">
									<i class="bi bi-bookmark"></i> <span>{{ t('Save') }}</span>
								</a>
							@endif
						</div>
					</div>
				</div>
			</div>
		</div>
	@endforeach
@else
	<div class="p-4 w-100">
		{{ t('no_result_refine_your_search') }}
	</div>
@endif

@section('after_scripts')
	@parent
	<script>
		{{-- Favorites Translation --}}
		var lang = {
			labelSavePostSave: "{!! t('Save listing') !!}",
			labelSavePostRemove: "{!! t('Remove favorite') !!}",
			loginToSavePost: "{!! t('Please log in to save the Listings') !!}",
			loginToSaveSearch: "{!! t('Please log in to save your search') !!}"
		};
	</script>
@endsection
