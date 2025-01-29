@php
	$admin ??= null;
	$city ??= null;
	$cat ??= null;
	
	$cats ??= [];
	
	// Keywords
	$keywords = request()->query('q');
	$keywords = is_string($keywords) ? $keywords : null;
	$keywords = rawurldecode($keywords);
	
	// Category
	$qCategory = request()->query('c');
	$qCategory = (is_numeric($qCategory) || is_string($qCategory)) ? $qCategory : null;
	$qCategory = data_get($cat, 'id', $qCategory);
	
	// Location
	$qLocationId = 0;
	$qAdminName = null;
	if (!empty($city)) {
		$qLocationId = data_get($city, 'id') ?? 0;
		$qLocation = data_get($city, 'name');
	} else {
		$qLocationId = request()->query('l');
		$qLocation = request()->query('location');
		$qAdminName = request()->query('r');
		
		$qLocationId = is_numeric($qLocationId) ? $qLocationId : null;
		$qLocation = is_string($qLocation) ? $qLocation : null;
		$qAdminName = is_string($qAdminName) ? $qAdminName : null;
		
		if (!empty($qAdminName)) {
			$qAdminName = data_get($admin, 'name', $qAdminName);
			$isAdminCode = (bool)preg_match('#^[a-z]{2}\.(.+)$#i', $qAdminName);
			$qLocation = !$isAdminCode ? t('area') . rawurldecode($qAdminName) : null;
		}
	}
	
	// FilterBy
	$qFilterBy = request()->query('filterBy');
	$qFilterBy = is_string($qFilterBy) ? $qFilterBy : null;
	
	$isAutocompleteEnabled = (config('settings.listings_list.enable_cities_autocompletion') == '1');
	$autocompleteClass = $isAutocompleteEnabled ? ' autocomplete-enabled' : '';
	
	$statesSearchTip = t('states_search_tip', ['prefix' => t('area'), 'suffix' => t('state_name')]);
	$displayStatesSearchTip = config('settings.listings_list.display_states_search_tip');
	$searchTooltip = $displayStatesSearchTip
		? ' data-bs-placement="top" data-bs-toggle="tooltipHover" title="' . $statesSearchTip . '"'
		: '';
@endphp
@include('front.sections.spacer')
<div class="container mb-2 serp-search-bar">
	<form id="searchForm"
	      name="search"
	      action="{{ urlGen()->searchWithoutQuery() }}"
	      method="GET"
	      data-csrf-token="{{ csrf_token() }}"
	>
		@if (!empty($qFilterBy))
			<input type="hidden" name="filterBy" value="{{ $qFilterBy }}">
		@endif
		<div class="row m-0">
			<div class="col-12 px-1 py-sm-1 bg-primary rounded">
				<div class="row gx-1 gy-1">
			
					<div class="col-xl-3 col-md-3 col-sm-12 col-12">
						<select name="c" id="catSearch" class="form-control selecter">
							<option value="" {{ ($qCategory=='') ? 'selected="selected"' : '' }}>
								{{ t('all_categories') }}
							</option>
							@if (!empty($cats))
								@foreach ($cats as $itemCat)
									<option value="{{ data_get($itemCat, 'id') }}" @selected($qCategory == data_get($itemCat, 'id'))>
										{{ data_get($itemCat, 'name') }}
									</option>
								@endforeach
							@endif
						</select>
					</div>
					
					<div class="col-xl-4 col-md-4 col-sm-12 col-12">
						<input name="q" class="form-control keyword" type="text" placeholder="{{ t('what') }}" value="{{ $keywords }}">
					</div>
					
					<input type="hidden" id="rSearch" name="r" value="{{ $qAdminName }}">
					<input type="hidden" id="lSearch" name="l" value="{{ $qLocationId }}">
					
					<div class="col-xl-3 col-md-3 col-sm-12 col-12 search-col locationicon">
						<input class="form-control locinput input-rel searchtag-input{{ $autocompleteClass }}"
						       type="text"
						       id="locSearch"
						       name="location"
						       placeholder="{{ t('where') }}"
						       value="{{ $qLocation }}"
						       data-old-value="{{ $qLocation }}"
						       spellcheck=false
						       autocomplete="off"
						       autocapitalize="off"
						       tabindex="1"{!! $searchTooltip !!}
						>
					</div>
					
					<div class="col-xl-2 col-md-2 col-sm-12 col-12">
						<button type="submit" class="btn btn-block btn-primary">
							<i class="fa-solid fa-magnifying-glass"></i> <strong>{{ t('find') }}</strong>
						</button>
					</div>
		
				</div>
			</div>
		</div>
	</form>
</div>

@section('after_scripts')
	@parent
@endsection
