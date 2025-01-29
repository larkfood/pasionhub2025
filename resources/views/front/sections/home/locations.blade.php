@php
	$sectionOptions = $locationsOptions ?? [];
	$sectionData ??= [];
	$cities = (array)data_get($sectionData, 'cities');
	
	// Get Admin Map's values
	$locCanBeShown = (data_get($sectionOptions, 'show_cities') == '1');
	$locColumns = (int)(data_get($sectionOptions, 'items_cols') ?? 3);
	$locCountListingsPerCity = (config('settings.listings_list.count_cities_listings'));
	$mapCanBeShown = (
		file_exists(config('larapen.core.maps.path') . config('country.icode') . '.svg')
		&& data_get($sectionOptions, 'enable_map') == '1'
	);
	
	$showListingBtn = (data_get($sectionOptions, 'show_listing_btn') == '1');
	
	$hideOnMobile = (data_get($sectionOptions, 'hide_on_mobile') == '1') ? ' hidden-sm' : '';
@endphp
@if ($locCanBeShown || $mapCanBeShown)
	@include('front.sections.spacer', ['hideOnMobile' => $hideOnMobile])
	
	<div class="container{{ $hideOnMobile }}">
		<div class="col-xl-12 page-content p-0">
			<div class="inner-box">
				
				<div class="row">
					@if (!$mapCanBeShown)
						<div class="row">
							<div class="col-xl-12 col-sm-12">
								<h2 class="title-3 pt-1 pb-3 px-3" style="white-space: nowrap;">
									<i class="bi bi-geo-alt"></i>&nbsp;{{ t('Choose a city') }}
								</h2>
							</div>
						</div>
					@endif
					
					@php
						$leftClassCol = '';
						$rightClassCol = '';
						$rowCol = 'row-cols-lg-4 row-cols-md-3 row-cols-sm-2 row-cols-1'; // Cities Columns
						
						if ($locCanBeShown && $mapCanBeShown) {
							// Display the Cities & the Map
							$leftClassCol = 'col-lg-8 col-md-12';
							$rightClassCol = 'col-lg-3 col-md-12 mt-3 mt-xl-0 mt-lg-0';
							$rowCol = 'row-cols-lg-3 row-cols-md-2 row-cols-sm-1 row-cols-1';
							
							if ($locColumns == 2) {
								$leftClassCol = 'col-md-6 col-sm-12';
								$rightClassCol = 'col-md-5 col-sm-12';
								$rowCol = 'row-cols-lg-2 row-cols-md-2 row-cols-sm-1 row-cols-1';
							}
							if ($locColumns == 1) {
								$leftClassCol = 'col-md-3 col-sm-12';
								$rightClassCol = 'col-md-8 col-sm-12';
								$rowCol = 'row-cols-lg-1 row-cols-md-1 row-cols-sm-1 row-cols-1';
							}
						} else {
							if ($locCanBeShown && !$mapCanBeShown) {
								// Display the Cities & Hide the Map
								$leftClassCol = 'col-xl-12';
							}
							if (!$locCanBeShown && $mapCanBeShown) {
								// Display the Map & Hide the Cities
								$rightClassCol = 'col-xl-12';
							}
						}
					@endphp
					@if ($locCanBeShown)
						<div class="{{ $leftClassCol }} page-content m-0 p-0">
							@if (!empty($cities))
								<div class="relative location-content">
									
									@if ($mapCanBeShown)
										<h2 class="title-3 pt-1 pb-3 px-3" style="white-space: nowrap;">
											<i class="bi bi-geo-alt"></i>&nbsp;{{ t('Choose a city or region') }}
										</h2>
									@endif
									<div class="col-xl-12 tab-inner">
										<div id="cityList" class="row {{ $rowCol }}">
											@foreach ($cities as $key => $city)
												@php
													$listBorder = (count($cities) == $key+1) ? 'cat-list-border' : '';
												@endphp
												<div class="col cat-list mb-0 mb-xl-2 mb-lg-2 mb-md-2 {{ $listBorder }}">
													@if (data_get($city, 'id') == 0)
														<a href="#browseLocations"
														   data-bs-toggle="modal"
														   data-admin-code="0"
														   data-city-id="0"
														>
															{!! data_get($city, 'name') !!}
														</a>
													@else
														<a href="{{ urlGen()->city($city) }}">
															{{ data_get($city, 'name') }}
														</a>
														@if ($locCountListingsPerCity)
															&nbsp;({{ data_get($city, 'posts_count') ?? 0 }})
														@endif
													@endif
												</div>
											@endforeach
										</div>
									</div>
									
									@if ($showListingBtn)
										@php
											[$createListingLinkUrl, $createListingLinkAttr] = getCreateListingLinkInfo();
										@endphp
										<a class="btn btn-lg btn-listing ps-4 pe-4"
										   href="{{ $createListingLinkUrl }}"{!! $createListingLinkAttr !!}
										   style="text-transform: none;"
										>
											<i class="fa-regular fa-pen-to-square"></i> {{ t('Create Listing') }}
										</a>
									@endif
			
								</div>
							@endif
						</div>
					@endif
					
					@include('front.sections.home.locations.svgmap')
				</div>
				
			</div>
		</div>
	</div>
@endif

@section('modal_location')
	@parent
	@if ($locCanBeShown || $mapCanBeShown)
		@include('front.layouts.inc.modal.location')
	@endif
@endsection

@section('after_scripts')
	@parent
	<script>
		const citiesColumns = {{ $locColumns }};
		onDocumentReady((event) => {
			{{-- Reorder the city list --}}
			const cityListReorder = new BsRowColumnsReorder('#cityList', {defaultColumns: citiesColumns});
		});
	</script>
@endsection
